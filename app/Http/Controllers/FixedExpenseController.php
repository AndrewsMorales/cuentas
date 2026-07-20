<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\FixedExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FixedExpenseController extends Controller
{
    public function index(): View
    {
        return view('fixed_expenses.index', [
            'items' => FixedExpense::with('category')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('fixed_expenses.form', [
            'item'       => new FixedExpense(['active' => true, 'fortnight' => 1, 'interval_months' => 1]),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        FixedExpense::create($this->validated($request));

        return redirect()->route('fixed-expenses.index')->with('status', 'Gasto fijo creado.');
    }

    public function edit(FixedExpense $fixedExpense): View
    {
        return view('fixed_expenses.form', [
            'item'       => $fixedExpense,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, FixedExpense $fixedExpense): RedirectResponse
    {
        $fixedExpense->update($this->validated($request));

        return redirect()->route('fixed-expenses.index')->with('status', 'Gasto fijo actualizado.');
    }

    public function destroy(FixedExpense $fixedExpense): RedirectResponse
    {
        $fixedExpense->delete();

        return redirect()->route('fixed-expenses.index')->with('status', 'Gasto fijo eliminado.');
    }

    /** @return array<string,mixed> */
    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id'     => ['required', 'exists:categories,id'],
            'name'            => ['required', 'string', 'max:150'],
            'average_amount'  => ['required', 'numeric', 'min:0'],
            'fortnight'       => ['required', 'integer', 'in:1,2'],
            'interval_months' => ['required', 'integer', 'min:1', 'max:12'],
            'anchor'          => ['nullable', 'date_format:Y-m'],
            'active'          => ['nullable', 'boolean'],
        ]);

        $data['active'] = (bool) $request->boolean('active');

        // El mes de referencia (ancla) solo aplica a frecuencias mayores a
        // mensual; para gastos mensuales se descarta.
        if ((int) $data['interval_months'] > 1 && ! empty($data['anchor'])) {
            [$ay, $am] = explode('-', $data['anchor']);
            $data['anchor_year']  = (int) $ay;
            $data['anchor_month'] = (int) $am;
        } else {
            $data['interval_months'] = max(1, (int) $data['interval_months']);
            $data['anchor_year']  = null;
            $data['anchor_month'] = null;
        }

        unset($data['anchor']);

        return $data;
    }
}
