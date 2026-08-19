<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Persona que aporta ingresos al hogar.
 *
 * @property int    $id
 * @property string $name
 * @property string $color
 */
class Person extends Model
{
    use HasFactory;

    protected $table = 'people';

    protected $fillable = ['name', 'color'];

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
