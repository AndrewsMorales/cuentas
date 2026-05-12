<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\MonthlyBudget;
use App\Models\Person;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Gestión de presupuestos mensuales: listado, detalle, navegación entre
 * meses y recarga de gastos fijos para el mes activo.
 */
class BudgetController extends Controller
{
    public function __construct(private readonly BudgetService $budgets) {}

    public function index(): View
    {
        $list = MonthlyBudget::orderByDesc('year')
            ->orderByDesc('month')
            ->withSum('incomes as total_income', 'amount')
            ->withSum('expenses as total_expense', 'amount')
            ->get();

        return view('budgets.index', ['budgets' => $list]);
    }

    public function show(int $year, int $month): View
    {
        $budget  = $this->budgets->resolveBudget($year, $month);
        $summary = $this->budgets->summary($budget);

        $budget->load([
            'incomes.person',
            'expenses.person',
            'expenses.category',
            'expenses.fixedExpense',
        ]);

        return view('budgets.show', [
            'budget'     => $budget,
            'summary'    => $summary,
            'people'     => Person::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** Recarga los gastos fijos del mes (idempotente sólo si no hay gastos fijos previos). */
    public function reloadFixed(Request $request, int $year, int $month): RedirectResponse
    {
        $budget = $this->budgets->resolveBudget($year, $month);

        if ($budget->expenses()->where('is_fixed_template', true)->exists()) {
            return back()->with('error', 'Este mes ya tiene gastos fijos cargados.');
        }

        $count = $this->budgets->seedFixedExpenses($budget);

        return back()->with('status', "Se cargaron {$count} gastos fijos.");
    }
}
