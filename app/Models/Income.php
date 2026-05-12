<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ingreso de una persona en una quincena específica de un mes.
 *
 * @property int     $id
 * @property int     $monthly_budget_id
 * @property int     $person_id
 * @property float   $amount
 * @property int     $fortnight
 * @property string  $received_at
 * @property ?string $note
 */
class Income extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_budget_id',
        'person_id',
        'amount',
        'fortnight',
        'received_at',
        'note',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'fortnight'   => 'int',
        'received_at' => 'date',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(MonthlyBudget::class, 'monthly_budget_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }
}
