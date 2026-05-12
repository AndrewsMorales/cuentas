<?php

namespace App\Services;

use App\Models\Income;

/**
 * Operaciones de ingreso por persona y quincena.
 */
class IncomeService
{
    /**
     * @param array{
     *   monthly_budget_id:int,
     *   person_id:int,
     *   amount:float|string,
     *   fortnight:int,
     *   received_at:string,
     *   note?:?string
     * } $data
     */
    public function create(array $data): Income
    {
        return Income::create($data);
    }

    public function update(Income $income, array $data): Income
    {
        $income->fill($data)->save();

        return $income->fresh();
    }

    public function delete(Income $income): void
    {
        $income->delete();
    }
}
