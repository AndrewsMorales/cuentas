<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\Person;
use App\Services\BudgetService;
use App\Services\IncomeService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IncomeController extends Controller
{
    public function __construct(
        private readonly IncomeService $service,
        private readonly BudgetService $budgets,
    ) {}

    public function index(Request $request): View
    {
        $year   = (int) $request->integer('year', (int) now()->year);
        $month  = (int) $request->integer('month', (int) now()->month);
        $budget = $this->budgets->resolveBudget($year, $month);

        return view('incomes.index', [
            'budget'  => $budget,
            'incomes' => $budget->incomes()->with('person')->orderBy('received_at')->get(),
            'people'  => Person::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // El budget destino se deriva siempre de received_at, no del payload.
        $received = Carbon::parse($data['received_at']);
        $budget = $this->budgets->resolveBudget($received->year, $received->month);
        if ($budget->isLocked()) {
            return back()->with('error', 'No se puede agregar a un mes ya cerrado.');
        }
        $data['monthly_budget_id'] = $budget->id;
        $this->service->create($data);

        return back()->with('status', 'Ingreso registrado.');
    }

    public function update(Request $request, Income $income): RedirectResponse
    {
        if ($income->budget->isLocked()) {
            return back()->with('error', 'Este mes ya está cerrado y no se puede editar.');
        }

        $data = $this->validated($request);
        $received = Carbon::parse($data['received_at']);
        $target = $this->budgets->resolveBudget($received->year, $received->month);
        if ($target->isLocked()) {
            return back()->with('error', 'No se puede mover el ingreso a un mes ya cerrado.');
        }
        $data['monthly_budget_id'] = $target->id;

        $this->service->update($income, $data);

        return back()->with('status', 'Ingreso actualizado.');
    }

    public function destroy(Income $income): RedirectResponse
    {
        if ($income->budget->isLocked()) {
            return back()->with('error', 'Este mes ya está cerrado y no se puede modificar.');
        }
        $this->service->delete($income);

        return back()->with('status', 'Ingreso eliminado.');
    }

    /**
     * monthly_budget_id es nullable porque ahora se deriva server-side
     * desde received_at. El hidden de los forms se ignora.
     *
     * @return array<string,mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'monthly_budget_id' => ['nullable', 'exists:monthly_budgets,id'],
            'person_id'         => ['required', 'exists:people,id'],
            'amount'            => ['required', 'numeric', 'min:0'],
            'fortnight'         => ['required', 'integer', 'in:1,2'],
            'received_at'       => ['required', 'date'],
            'note'              => ['nullable', 'string', 'max:255'],
        ]);
    }
}
