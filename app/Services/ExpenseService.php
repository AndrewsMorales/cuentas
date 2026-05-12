<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\MonthlyBudget;
use Carbon\Carbon;

/**
 * Operaciones de gasto: alta rápida desde el FAB, edición, asignación
 * de persona a un gasto fijo pendiente, eliminación.
 */
class ExpenseService
{
    /**
     * Da de alta un gasto nuevo (no proveniente de plantilla).
     *
     * @param array{
     *   monthly_budget_id:int,
     *   category_id:int,
     *   person_id:?int,
     *   description:string,
     *   amount:float|string,
     *   spent_at:string,
     *   fortnight:int
     * } $data
     */
    public function create(array $data): Expense
    {
        return Expense::create([
            'monthly_budget_id' => $data['monthly_budget_id'],
            'category_id'       => $data['category_id'],
            'person_id'         => $data['person_id'] ?? null,
            'description'       => $data['description'],
            'amount'            => $data['amount'],
            'spent_at'          => $data['spent_at'],
            'fortnight'         => $data['fortnight'],
            'is_fixed_template' => false,
        ]);
    }

    /** Actualiza un gasto existente. */
    public function update(Expense $expense, array $data): Expense
    {
        $expense->fill($data)->save();

        return $expense->fresh();
    }

    /**
     * Asigna una persona (y opcionalmente ajusta el monto) a un gasto fijo
     * que se cargó vacío al iniciar el mes.
     */
    public function assignPersonToFixed(Expense $expense, int $personId, ?float $amount = null): Expense
    {
        $expense->person_id = $personId;

        if ($amount !== null) {
            $expense->amount = $amount;
        }

        $expense->save();

        return $expense->fresh();
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    /**
     * Determina la quincena (1 o 2) a partir de una fecha.
     * Días 1-15 → quincena 1; días 16-31 → quincena 2.
     */
    public function fortnightFromDate(string|Carbon $date): int
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $d->day <= 15 ? 1 : 2;
    }
}
