<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Categoría de gasto (ej: Hogar, Alimentación, Salidas, Moto).
 *
 * @property int    $id
 * @property string $name
 * @property string $icon
 * @property string $color
 */
class Category extends Model
{
    use HasFactory;

    /** Categorías protegidas — no se pueden eliminar desde la UI. */
    public const PROTECTED = ['Ahorro', 'Alimentación', 'Hogar', 'Servicios'];

    /** Categoría usada para reservar dinero como ahorro (carry-over al siguiente período). */
    public const SAVINGS = 'Ahorro';

    protected $fillable = ['name', 'icon', 'color'];

    public function fixedExpenses(): HasMany
    {
        return $this->hasMany(FixedExpense::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function isProtected(): bool
    {
        return in_array($this->name, self::PROTECTED, true);
    }

    public function isSavings(): bool
    {
        return $this->name === self::SAVINGS;
    }
}
