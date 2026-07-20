<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Presupuesto de un mes específico (clave única [year, month]).
 *
 * Agrupa ingresos y gastos del mes y permite calcular balance.
 *
 * @property int             $id
 * @property int             $year
 * @property int             $month
 * @property ?\Carbon\Carbon $closed_at
 */
class MonthlyBudget extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'month', 'closed_at', 'fixed_seeded_at'];

    protected $casts = [
        'year'            => 'int',
        'month'           => 'int',
        'closed_at'       => 'datetime',
        'fixed_seeded_at' => 'datetime',
    ];

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function label(): string
    {
        return Carbon::create($this->year, $this->month, 1)
            ->locale('es')
            ->isoFormat('MMMM YYYY');
    }

    public function periodStart(): Carbon
    {
        return Carbon::create($this->year, $this->month, 1)->startOfDay();
    }

    public function periodEnd(): Carbon
    {
        // El periodo contable se extiende hasta la fecha de corte (día 5 del
        // mes siguiente), no hasta el último día calendario del mes.
        return $this->cutoff();
    }

    /**
     * Fecha de corte: el presupuesto de un mes se cierra el día 5 del mes
     * siguiente. Hasta esa fecha (días 1 a 5 del mes siguiente) el mes
     * anterior sigue siendo editable como periodo de gracia.
     */
    public function cutoff(): Carbon
    {
        return Carbon::create($this->year, $this->month, 1)
            ->addMonthNoOverflow()  // primer día del mes siguiente
            ->day(5)                // día 5 del mes siguiente
            ->endOfDay();
    }

    /**
     * Un presupuesto queda bloqueado para edición una vez pasada su fecha
     * de corte (día 5 del mes siguiente). El mes actual, el mes recién
     * vencido dentro del periodo de gracia, y los futuros siguen editables.
     */
    public function isLocked(): bool
    {
        return Carbon::now()->greaterThan($this->cutoff());
    }

    /**
     * Los gastos fijos del mes se materializan recién cuando el mes anterior
     * cierra, es decir, pasada SU fecha de corte (el día 5 de este mes).
     * Durante el periodo de gracia (días 1 a 5) los fijos aún no se cargan.
     */
    public function fixedExpensesDue(): bool
    {
        $previousCutoff = Carbon::create($this->year, $this->month, 5)->endOfDay();

        return Carbon::now()->greaterThan($previousCutoff);
    }
}
