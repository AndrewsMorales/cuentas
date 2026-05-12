<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Expense;
use App\Models\FixedExpense;
use App\Models\MonthlyBudget;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Servicio responsable del ciclo de vida del presupuesto mensual.
 *
 * - Resuelve/crea el MonthlyBudget de un (año, mes).
 * - Materializa los gastos fijos (FixedExpense) como Expense en estado
 *   "pendiente de asignación" la primera vez que se accede al mes.
 * - Calcula el balance y los totales por persona y por categoría.
 */
class BudgetService
{
    /**
     * Devuelve el presupuesto del mes solicitado, creándolo si no existe.
     * Al crearlo, copia los gastos fijos activos como Expense del mes.
     */
    public function resolveBudget(int $year, int $month): MonthlyBudget
    {
        return DB::transaction(function () use ($year, $month) {
            /** @var MonthlyBudget $budget */
            $budget = MonthlyBudget::firstOrCreate(
                ['year' => $year, 'month' => $month],
            );

            if ($budget->wasRecentlyCreated) {
                $this->seedFixedExpenses($budget);
            }

            return $budget;
        });
    }

    /** Devuelve el presupuesto del mes actual (creándolo si hace falta). */
    public function currentBudget(): MonthlyBudget
    {
        $now = Carbon::now();

        return $this->resolveBudget($now->year, $now->month);
    }

    /**
     * Sobrante acumulado: para todos los meses anteriores a $budget,
     * suma (ingresos − gastos no-Ahorro). Opcionalmente filtra por persona.
     */
    private function cumulativeLeftover(MonthlyBudget $budget, ?int $savingsCategoryId, ?int $personId = null): float
    {
        $previous = MonthlyBudget::where(function ($q) use ($budget) {
            $q->where('year', '<', $budget->year)
              ->orWhere(function ($q) use ($budget) {
                  $q->where('year', $budget->year)->where('month', '<', $budget->month);
              });
        })->pluck('id');

        if ($previous->isEmpty()) return 0.0;

        $incomeQ = \App\Models\Income::whereIn('monthly_budget_id', $previous);
        if ($personId) $incomeQ->where('person_id', $personId);
        $income = (float) $incomeQ->sum('amount');

        $expQ = Expense::whereIn('monthly_budget_id', $previous);
        if ($savingsCategoryId) $expQ->where('category_id', '!=', $savingsCategoryId);
        if ($personId) $expQ->where('person_id', $personId);
        $expense = (float) $expQ->sum('amount');

        return $income - $expense;
    }

    /**
     * Carga los gastos fijos activos como gastos pendientes del mes.
     * Cada gasto pendiente queda sin person_id para que el usuario asigne.
     */
    public function seedFixedExpenses(MonthlyBudget $budget): int
    {
        $fixed = FixedExpense::where('active', true)->get();

        foreach ($fixed as $tpl) {
            Expense::create([
                'monthly_budget_id' => $budget->id,
                'category_id'       => $tpl->category_id,
                'fixed_expense_id'  => $tpl->id,
                'person_id'         => null,
                'description'       => $tpl->name,
                'amount'            => $tpl->average_amount,
                'spent_at'          => $budget->periodStart()->toDateString(),
                'fortnight'         => $tpl->fortnight,
                'is_fixed_template' => true,
            ]);
        }

        return $fixed->count();
    }

    /**
     * Resumen agregado del mes: ingresos, gastos, balance, totales por
     * persona y por categoría, además del detalle por quincena.
     *
     * @return array<string,mixed>
     */
    public function summary(MonthlyBudget $budget): array
    {
        $budget->load(['incomes.person', 'expenses.person', 'expenses.category']);

        $savingsCategoryId = Category::where('name', Category::SAVINGS)->value('id');
        $isSavings = fn ($expense) => (int) $expense->category_id === (int) $savingsCategoryId;

        // Separar gastos reales de los movimientos a ahorro.
        $realExpenses    = $budget->expenses->reject($isSavings);
        $savingsEntries  = $budget->expenses->filter($isSavings);

        $totalIncome  = (float) $budget->incomes->sum('amount');
        $totalExpense = (float) $realExpenses->sum('amount');
        $savingsThisMonth = (float) $savingsEntries->sum('amount');

        // Carry-over: lo no gastado en meses anteriores se acumula automáticamente.
        // = Σ(ingresos − gastos NO Ahorro) de cada mes previo.
        $carryOver = $this->cumulativeLeftover($budget, $savingsCategoryId);
        $cumulativeSavings = $carryOver + $savingsThisMonth;

        $byPerson = Person::orderBy('name')->get()->map(
            function (Person $person) use ($budget, $savingsCategoryId, $isSavings, $carryOver) {
                $income  = (float) $budget->incomes->where('person_id', $person->id)->sum('amount');
                $expense = (float) $budget->expenses
                    ->where('person_id', $person->id)
                    ->reject($isSavings)
                    ->sum('amount');
                $personSavings = (float) $budget->expenses
                    ->where('person_id', $person->id)
                    ->filter($isSavings)
                    ->sum('amount');

                // Carry-over por persona: sobrante implícito de meses anteriores
                // = Σ(ingresos_persona − gastos_no_ahorro_persona) de cada mes previo.
                $personCarryOver = $this->cumulativeLeftover($budget, $savingsCategoryId, $person->id);

                $available = $income + $personCarryOver - $expense;
                $remaining = $available - $personSavings;
                $denom = $income + $personCarryOver;
                $percentUsed = $denom > 0 ? min(100, ($expense / $denom) * 100) : ($expense > 0 ? 100 : 0);

                return [
                    'person'        => $person,
                    'income'        => $income,
                    'carry_over'    => $personCarryOver,
                    'expense'       => $expense,
                    'savings'       => $personSavings,
                    'available'     => $available,
                    'remaining'     => $remaining,
                    'balance'       => $remaining,
                    'percent_used'  => round($percentUsed, 1),
                ];
            }
        )->values();

        $byCategory = $realExpenses
            ->groupBy('category_id')
            ->map(fn ($exp) => [
                'category' => $exp->first()->category,
                'amount'   => (float) $exp->sum('amount'),
                'count'    => $exp->count(),
            ])
            ->sortByDesc('amount')
            ->values();

        $byFortnight = collect([1, 2])->map(fn ($f) => [
            'fortnight' => $f,
            'income'    => (float) $budget->incomes->where('fortnight', $f)->sum('amount'),
            'expense'   => (float) $realExpenses->where('fortnight', $f)->sum('amount'),
            'savings'   => (float) $savingsEntries->where('fortnight', $f)->sum('amount'),
        ]);

        return [
            'total_income'       => $totalIncome,
            'total_expense'      => $totalExpense,
            'savings_this_month' => $savingsThisMonth,
            'carry_over'         => $carryOver,
            'cumulative_savings' => $cumulativeSavings,
            'available'          => $totalIncome + $carryOver - $totalExpense,
            'balance'            => $totalIncome + $carryOver - $totalExpense - $savingsThisMonth,
            'pending_fixed'      => $budget->expenses
                ->where('is_fixed_template', true)
                ->whereNull('person_id')
                ->count(),
            'by_person'          => $byPerson,
            'by_category'        => $byCategory,
            'by_fortnight'       => $byFortnight,
        ];
    }
}
