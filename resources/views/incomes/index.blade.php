@extends('layouts.app')
@section('title', 'Ingresos · ' . ucfirst($budget->label()))

@php $money = fn ($v) => '$' . number_format((float)$v, 0, ',', '.'); @endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3 gap-3">
        <div>
            <h1 class="page-title">Ingresos</h1>
            <div class="page-subtitle">{{ $budget->label() }}</div>
        </div>
        <form method="GET" class="d-flex gap-2 align-items-center">
            <select name="month" class="form-select form-select-sm" style="min-width:140px">
                @foreach (range(1,12) as $m)
                    <option value="{{ $m }}" @selected($m === $budget->month)>{{ \Carbon\Carbon::create(null, $m)->locale('es')->isoFormat('MMMM') }}</option>
                @endforeach
            </select>
            <select name="year" class="form-select form-select-sm" style="width:110px">
                @for ($y = min(2100, (int) now()->year); $y >= 2025; $y--)
                    <option value="{{ $y }}" @selected($y === $budget->year)>{{ $y }}</option>
                @endfor
            </select>
            <button class="btn btn-info btn-sm"
                    data-bs-toggle="tooltip" title="Cambiar al mes seleccionado">
                Ver
            </button>
        </form>
    </div>

    @if ($budget->isLocked())
        <div class="alert" style="border-left:4px solid var(--ink-soft); background: var(--surface); border-color: var(--border); color: var(--ink);">
            <i class="bi bi-lock-fill"></i> Este mes ya está cerrado. Los datos quedan en solo-lectura.
        </div>
    @endif

    <div class="row g-3">
        @canany(['manage'])
            @unless ($budget->isLocked())
        <div class="col-lg-5">
            <div class="card card-stat">
                <div class="card-header"><i class="bi bi-plus-circle"></i> Nuevo ingreso</div>
                <form method="POST" action="{{ route('incomes.store') }}" class="card-body">
                    @csrf
                    <input type="hidden" name="monthly_budget_id" value="{{ $budget->id }}">
                    <div class="mb-2">
                        <label class="form-label">Persona</label>
                        <select name="person_id" class="form-select" required>
                            @foreach ($people as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-7">
                            <label class="form-label">Monto</label>
                            <input type="number" step="0.01" min="0" name="amount" class="form-control" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label">Quincena</label>
                            <select name="fortnight" class="form-select">
                                <option value="1">1ª</option>
                                <option value="2">2ª</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Fecha</label>
                        <input type="date" name="received_at" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="mt-2">
                        <label class="form-label">Nota (opcional)</label>
                        <input type="text" name="note" class="form-control" placeholder="Ej: Salario quincena">
                    </div>
                    <button class="btn btn-primary mt-3 w-100"
                            data-bs-toggle="tooltip" title="Guardar el ingreso de esta persona">
                        <i class="bi bi-check2-circle"></i> Registrar
                    </button>
                </form>
            </div>
        </div>
            @endunless
        @endcanany

        <div class="@if(! $budget->isLocked() && auth()->user()?->isManager()) col-lg-7 @else col-12 @endif">
            <div class="card card-stat">
                <div class="card-header"><i class="bi bi-list-ul"></i> Ingresos del mes</div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead>
                            <tr>
                                <th>Persona</th>
                                <th class="text-center">Quincena</th>
                                <th>Fecha</th>
                                <th class="text-end">Monto</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse ($incomes as $i)
                            <tr>
                                <td><span class="person-dot" style="background: {{ $i->person->color }}"></span> {{ $i->person->name }}</td>
                                <td class="text-center">{{ $i->fortnight === 1 ? '1ª' : '2ª' }}</td>
                                <td>{{ $i->received_at->format('d/m/Y') }}</td>
                                <td class="text-end balance-positive">{{ $money($i->amount) }}</td>
                                <td class="actions-cell">
                                    @if (! $budget->isLocked())
                                        @can('manage')
                                            <div class="actions">
                                                <form method="POST" action="{{ route('incomes.destroy', $i) }}"
                                                      class="js-confirm-delete" data-message="¿Eliminar este ingreso?">
                                                    @csrf @method('DELETE')
                                                    <button class="btn btn-danger btn-icon btn-sm"
                                                            data-bs-toggle="tooltip" title="Eliminar ingreso">
                                                        <i class="bi bi-trash-fill"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        @endcan
                                    @else
                                        <span class="text-muted small"><i class="bi bi-lock-fill"></i></span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">Aún no hay ingresos.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
