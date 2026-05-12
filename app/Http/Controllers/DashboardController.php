<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Person;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Vista principal: balance del mes, totales por persona y por categoría,
 * acceso rápido al alta de gasto.
 */
class DashboardController extends Controller
{
    public function __construct(private readonly BudgetService $budgets) {}

    public function index(Request $request): View
    {
        $year  = (int) $request->integer('year', (int) now()->year);
        $month = (int) $request->integer('month', (int) now()->month);

        $budget  = $this->budgets->resolveBudget($year, $month);
        $summary = $this->budgets->summary($budget);

        return view('dashboard', [
            'budget'     => $budget,
            'summary'    => $summary,
            'people'     => Person::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
