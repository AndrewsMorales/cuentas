@php
    /** @var \App\Services\BudgetService $budgetSvc */
    $budgetSvc = app(\App\Services\BudgetService::class);
    $currentBudget = $budgetSvc->currentBudget();
    $quickPeople     = \App\Models\Person::orderBy('name')->get();
    $quickCategories = \App\Models\Category::orderBy('name')->get();
@endphp

<div class="modal fade" id="quickExpenseModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('expenses.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="monthly_budget_id" value="{{ $currentBudget->id }}">

            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg"></i> Nuevo gasto · {{ ucfirst($currentBudget->label()) }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="description" class="form-control form-control-lg" placeholder="Ej: Mercado del sábado" required>
                </div>
                <div class="row g-2">
                    <div class="col-7">
                        <label class="form-label">Monto</label>
                        <input type="number" step="0.01" min="0" name="amount" class="form-control form-control-lg" required>
                    </div>
                    <div class="col-5">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="spent_at" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                </div>
                <div class="row g-2 mt-2">
                    <div class="col-6">
                        <label class="form-label">Categoría</label>
                        <select name="category_id" class="form-select" required>
                            @foreach ($quickCategories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label">¿De qué persona salió?</label>
                        <select name="person_id" class="form-select">
                            <option value="">— Sin asignar —</option>
                            @foreach ($quickPeople as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label d-block">Quincena</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="fortnight" id="qf1" value="1">
                        <label class="btn btn-outline-primary" for="qf1">1ª quincena</label>
                        <input type="radio" class="btn-check" name="fortnight" id="qf2" value="2">
                        <label class="btn btn-outline-primary" for="qf2">2ª quincena</label>
                    </div>
                    <small class="text-muted">Si la dejas vacía, se asigna por la fecha.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal"
                        data-bs-toggle="tooltip" title="Cerrar sin guardar">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary"
                        data-bs-toggle="tooltip" title="Registrar este gasto">
                    <i class="bi bi-check2-circle"></i> Guardar gasto
                </button>
            </div>
        </form>
    </div>
</div>
