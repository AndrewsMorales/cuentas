@extends('layouts.app')
@section('title', 'Editar gasto')

@section('content')
    <div class="d-flex justify-content-between align-items-end mb-3">
        <div>
            <h1 class="page-title">Editar gasto</h1>
            <div class="page-subtitle">{{ ucfirst($expense->budget->label()) }}</div>
        </div>
        <a href="{{ route('expenses.index', ['year' => $expense->budget->year, 'month' => $expense->budget->month]) }}"
           class="btn btn-light btn-sm"
           data-bs-toggle="tooltip" title="Volver al listado">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <form method="POST" action="{{ route('expenses.update', $expense) }}" class="card card-stat p-4">
        @csrf @method('PUT')
        <input type="hidden" name="monthly_budget_id" value="{{ $expense->monthly_budget_id }}">

        <div class="mb-3">
            <label class="form-label">Descripción</label>
            <input type="text" name="description" class="form-control"
                   value="{{ old('description', $expense->description) }}" required>
        </div>

        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Monto</label>
                <input type="number" step="0.01" min="0" name="amount" class="form-control"
                       value="{{ old('amount', $expense->amount) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Fecha</label>
                <input type="date" name="spent_at" class="form-control"
                       value="{{ old('spent_at', $expense->spent_at->toDateString()) }}" required>
            </div>
        </div>

        <div class="row g-2 mt-2">
            <div class="col-md-6">
                <label class="form-label">Categoría</label>
                <select name="category_id" class="form-select" required>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('category_id', $expense->category_id) == $c->id)>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">¿De qué persona salió?</label>
                <select name="person_id" class="form-select">
                    <option value="">— Sin asignar —</option>
                    @foreach ($people as $p)
                        <option value="{{ $p->id }}" @selected(old('person_id', $expense->person_id) == $p->id)>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label d-block">Quincena</label>
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="fortnight" id="ef1" value="1" @checked(old('fortnight', $expense->fortnight) == 1)>
                <label class="btn btn-outline-primary" for="ef1">1ª quincena</label>
                <input type="radio" class="btn-check" name="fortnight" id="ef2" value="2" @checked(old('fortnight', $expense->fortnight) == 2)>
                <label class="btn btn-outline-primary" for="ef2">2ª quincena</label>
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary" data-bs-toggle="tooltip" title="Guardar cambios del gasto">
                <i class="bi bi-check2-circle"></i> Guardar cambios
            </button>
            <a href="{{ route('expenses.index', ['year' => $expense->budget->year, 'month' => $expense->budget->month]) }}"
               class="btn btn-light"
               data-bs-toggle="tooltip" title="Descartar cambios">
                <i class="bi bi-x-lg"></i> Cancelar
            </a>
        </div>
    </form>
@endsection
