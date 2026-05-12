<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Expense;
use App\Models\Person;
use App\Services\BudgetService;
use App\Services\ExpenseService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $service,
        private readonly BudgetService $budgets,
    ) {}

    public function index(Request $request): View
    {
        $year   = (int) $request->integer('year', (int) now()->year);
        $month  = (int) $request->integer('month', (int) now()->month);
        $budget = $this->budgets->resolveBudget($year, $month);

        return view('expenses.index', [
            'budget'     => $budget,
            'expenses'   => $budget->expenses()
                ->with(['category', 'person', 'fixedExpense'])
                ->orderBy('spent_at', 'desc')
                ->orderBy('id', 'desc')
                ->get(),
            'people'     => Person::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // El mes destino se deriva siempre de la FECHA del gasto, no del
        // payload. Así un "spent_at = 2026-06-03" cae en el budget de junio
        // aunque se haya disparado desde el FAB de mayo.
        $spent = Carbon::parse($data['spent_at']);
        $budget = $this->budgets->resolveBudget($spent->year, $spent->month);
        if ($budget->isLocked()) {
            return back()->with('error', 'No se puede agregar a un mes ya cerrado.');
        }

        $data['monthly_budget_id'] = $budget->id;
        $data['fortnight'] ??= $this->service->fortnightFromDate($data['spent_at']);
        $this->service->create($data);

        return back()->with('status', 'Gasto registrado.');
    }

    public function edit(Expense $expense): View|RedirectResponse
    {
        if ($expense->budget->isLocked()) {
            return redirect()->route('expenses.index', [
                'year'  => $expense->budget->year,
                'month' => $expense->budget->month,
            ])->with('error', 'Este mes ya está cerrado y no se puede editar.');
        }

        return view('expenses.edit', [
            'expense'    => $expense->load(['category', 'person', 'budget']),
            'people'     => Person::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        if ($expense->budget->isLocked()) {
            return back()->with('error', 'Este mes ya está cerrado y no se puede editar.');
        }

        $data = $this->validated($request);

        // Si el usuario cambió la fecha a otro mes, mover el gasto al budget
        // correcto (creándolo si hace falta) y validar que ese mes no esté
        // cerrado.
        $spent = Carbon::parse($data['spent_at']);
        $targetBudget = $this->budgets->resolveBudget($spent->year, $spent->month);
        if ($targetBudget->isLocked()) {
            return back()->with('error', 'No se puede mover el gasto a un mes ya cerrado.');
        }
        $data['monthly_budget_id'] = $targetBudget->id;
        $data['fortnight'] ??= $this->service->fortnightFromDate($data['spent_at']);

        $this->service->update($expense, $data);

        return redirect()->route('expenses.index', [
            'year'  => $targetBudget->year,
            'month' => $targetBudget->month,
        ])->with('status', 'Gasto actualizado.');
    }

    public function assign(Request $request, Expense $expense): RedirectResponse
    {
        if ($expense->budget->isLocked()) {
            return back()->with('error', 'Este mes ya está cerrado.');
        }

        $data = $request->validate([
            'person_id' => ['required', 'exists:people,id'],
            'amount'    => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->service->assignPersonToFixed(
            $expense, (int) $data['person_id'],
            isset($data['amount']) ? (float) $data['amount'] : null,
        );

        return back()->with('status', 'Gasto asignado.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        if ($expense->budget->isLocked()) {
            return back()->with('error', 'Este mes ya está cerrado y no se puede modificar.');
        }

        $this->service->delete($expense);

        return back()->with('status', 'Gasto eliminado.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        // monthly_budget_id es nullable porque ahora se deriva server-side
        // desde spent_at. El campo hidden en los forms se mantiene por
        // retro-compatibilidad pero su valor se ignora.
        return $request->validate([
            'monthly_budget_id' => ['nullable', 'exists:monthly_budgets,id'],
            'category_id'       => ['required', 'exists:categories,id'],
            'person_id'         => ['nullable', 'exists:people,id'],
            'description'       => ['required', 'string', 'max:200'],
            'amount'            => ['required', 'numeric', 'min:0'],
            'spent_at'          => ['required', 'date'],
            'fortnight'         => ['nullable', 'integer', 'in:1,2'],
        ]);
    }
}
