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
 * @property int    $fortnight     1 = primera quincena, 2 = segunda
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
        'active',
    ];

    protected $casts = [
        'average_amount' => 'decimal:2',
        'fortnight'      => 'int',
        'active'         => 'bool',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }
}
