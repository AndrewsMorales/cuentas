<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FixedExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Login / logout (público / autenticado)
Route::middleware('guest')->group(function () {
    Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Toda la app requiere estar autenticado.
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Lecturas: cualquier usuario autenticado puede ver.
    Route::get('/incomes',         [IncomeController::class, 'index'])->name('incomes.index');
    Route::get('/expenses',        [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/{expense}/edit', [ExpenseController::class, 'edit'])->name('expenses.edit');
    Route::get('/budgets',         [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('/budgets/{year}/{month}', [BudgetController::class, 'show'])
        ->whereNumber(['year', 'month'])->name('budgets.show');

    // Fallback: navegación accidental a /expenses/{id} o /incomes/{id} → index
    Route::get('/expenses/{expense}', fn () => redirect()->route('expenses.index'))->whereNumber('expense');
    Route::get('/incomes/{income}',  fn () => redirect()->route('incomes.index'))->whereNumber('income');

    // Mutaciones: SÓLO managers.
    Route::middleware('can:manage')->group(function () {
        Route::resource('categories',     CategoryController::class)->except('show');
        Route::resource('fixed-expenses', FixedExpenseController::class)->except('show');

        // Pestaña Usuarios: solo el super admin (Andrés).
        Route::resource('users', UserController::class)->except('show')
            ->middleware('can:manage-users');

        Route::post('/incomes',           [IncomeController::class, 'store'])->name('incomes.store');
        Route::put('/incomes/{income}',   [IncomeController::class, 'update'])->name('incomes.update');
        Route::delete('/incomes/{income}',[IncomeController::class, 'destroy'])->name('incomes.destroy');

        Route::post('/expenses',                  [ExpenseController::class, 'store'])->name('expenses.store');
        Route::put('/expenses/{expense}',         [ExpenseController::class, 'update'])->name('expenses.update');
        Route::patch('/expenses/{expense}/assign',[ExpenseController::class, 'assign'])->name('expenses.assign');
        Route::delete('/expenses/{expense}',      [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        Route::post('/budgets/{year}/{month}/reload-fixed', [BudgetController::class, 'reloadFixed'])
            ->whereNumber(['year', 'month'])->name('budgets.reload-fixed');
    });
});
