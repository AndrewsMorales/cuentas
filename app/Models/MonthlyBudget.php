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

    protected $fillable = ['year', 'month', 'closed_at'];

    protected $casts = [
        'year'      => 'int',
        'month'     => 'int',
        'closed_at' => 'datetime',
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
        return Carbon::create($this->year, $this->month, 1)->endOfMonth();
    }

    /**
     * Un presupuesto queda bloqueado para edición cuando su mes ya pasó.
     * El mes actual y los futuros siguen siendo editables.
     */
    public function isLocked(): bool
    {
        $now = Carbon::now();
        if ($this->year < $now->year) return true;
        if ($this->year === $now->year && $this->month < $now->month) return true;
        return false;
    }
}
