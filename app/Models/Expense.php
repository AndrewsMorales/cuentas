<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Gasto ejecutado, asignable a una persona (de cuya plata salió).
 *
 * Si proviene de una plantilla FixedExpense, se marca con
 * is_fixed_template=true al cargarse el mes y queda en estado "pendiente"
 * (sin persona asignada) hasta que el usuario indique quién pagó.
 *
 * @property int     $id
 * @property int     $monthly_budget_id
 * @property int     $category_id
 * @property ?int    $person_id
 * @property ?int    $fixed_expense_id
 * @property string  $description
 * @property float   $amount
 * @property string  $spent_at
 * @property int     $fortnight
 * @property bool    $is_fixed_template
 */
class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_budget_id',
        'category_id',
        'person_id',
        'fixed_expense_id',
        'description',
        'amount',
        'spent_at',
        'fortnight',
        'is_fixed_template',
    ];

    protected $casts = [
        'amount'            => 'decimal:2',
        'spent_at'          => 'date',
        'fortnight'         => 'int',
        'is_fixed_template' => 'bool',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(MonthlyBudget::class, 'monthly_budget_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function fixedExpense(): BelongsTo
    {
        return $this->belongsTo(FixedExpense::class);
    }
}
