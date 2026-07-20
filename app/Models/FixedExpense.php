<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Plantilla de gasto fijo recurrente.
 *
 * Cada mes nuevo se materializa en un Expense con is_fixed_template=true,
 * para que el usuario sólo le asigne la persona a la que se le descuenta.
 *
 * @property int    $id
 * @property int    $category_id
 * @property string $name
 * @property float  $average_amount
 * @property int    $fortnight       1 = primera quincena, 2 = segunda
 * @property int    $interval_months Cada cuántos meses se carga (1 = mensual)
 * @property ?int   $anchor_year     Año del mes de referencia (ancla)
 * @property ?int   $anchor_month    Mes de referencia (ancla), 1-12
 * @property bool   $active
 */
class FixedExpense extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'average_amount',
        'fortnight',
        'interval_months',
        'anchor_year',
        'anchor_month',
        'active',
    ];

    protected $casts = [
        'average_amount'  => 'decimal:2',
        'fortnight'       => 'int',
        'interval_months' => 'int',
        'anchor_year'     => 'int',
        'anchor_month'    => 'int',
        'active'          => 'bool',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * ¿Este gasto fijo recae en el mes (year, month)?
     *
     * - Frecuencia mensual (interval_months <= 1): siempre.
     * - Frecuencia cada N meses: solo cuando la distancia en meses respecto
     *   al mes de referencia (ancla) es múltiplo de N. Sin ancla definida se
     *   asume mensual para no ocultar el gasto por configuración incompleta.
     */
    public function occursIn(int $year, int $month): bool
    {
        $interval = max(1, (int) $this->interval_months);
        if ($interval === 1) {
            return true;
        }

        if (! $this->anchor_year || ! $this->anchor_month) {
            return true;
        }

        $diff = (($year * 12) + ($month - 1)) - (($this->anchor_year * 12) + ($this->anchor_month - 1));

        return ((($diff % $interval) + $interval) % $interval) === 0;
    }

    /** Etiqueta legible de la frecuencia, ej. "cada 2 meses". */
    public function frequencyLabel(): string
    {
        $interval = max(1, (int) $this->interval_months);

        return match (true) {
            $interval === 1  => 'Mensual',
            $interval === 12 => 'Anual',
            default          => "Cada {$interval} meses",
        };
    }
}
