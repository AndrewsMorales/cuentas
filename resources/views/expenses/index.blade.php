@extends('layouts.app')
@section('title', 'Gastos · ' . ucfirst($budget->label()))

@php $money = fn ($v) => '$' . number_format((float)$v, 0, ',', '.'); @endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3 gap-3">
        <div>
            <h1 class="page-title">Gastos</h1>
            <div class="page-subtitle">{{ $budget->label() }}</div>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
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
            @can('manage')
                @unless ($budget->isLocked())
                    <button type="button" class="btn btn-primary btn-sm d-none d-lg-inline-flex"
                            data-bs-toggle="modal" data-bs-target="#quickExpenseModal"
                            title="Registrar un gasto rápido">
                        <i class="bi bi-plus-lg"></i> Agregar
                    </button>
                @endunless
            @endcan
        </div>
    </div>

    @if ($budget->isLocked())
        <div class="alert" style="border-left:4px solid var(--ink-soft); background: var(--surface); border-color: var(--border); color: var(--ink);">
            <i class="bi bi-lock-fill"></i> Este mes ya está cerrado. Los datos quedan en solo-lectura.
        </div>
    @endif

    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Descripción</th>
                        <th>Categoría</th>
                        <th class="text-center">Quincena</th>
                        <th>Persona</th>
                        <th class="text-end">Monto</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($expenses as $e)
                    <tr>
                        <td>{{ $e->spent_at->format('d/m') }}</td>
                        <td>
                            {{ $e->description }}
                            @if ($e->is_fixed_template) <span class="badge bg-warning ms-1">Fijo</span> @endif
                        </td>
                        <td>
                            <span class="category-chip" style="background: {{ $e->category->color }}22; color: {{ $e->category->color }}">
                                <i class="bi {{ $e->category->icon }}"></i> {{ $e->category->name }}
                            </span>
                        </td>
                        <td class="text-center">{{ $e->fortnight === 1 ? '1ª' : '2ª' }}</td>
                        <td>
                            @if ($e->person)
                                <span class="person-dot" style="background: {{ $e->person->color }}"></span> {{ $e->person->name }}
                            @elseif (! $budget->isLocked() && auth()->user()?->isManager())
                                <form method="POST" action="{{ route('expenses.assign', $e) }}" class="d-inline-flex gap-1 align-items-center">
                                    @csrf @method('PATCH')
                                    <select name="person_id" class="form-select form-select-sm" required style="min-width:120px">
                                        <option value="">— Asignar —</option>
                                        @foreach ($people as $p) <option value="{{ $p->id }}">{{ $p->name }}</option> @endforeach
                                    </select>
                                    <button class="btn btn-primary btn-icon btn-sm"
                                            data-bs-toggle="tooltip" title="Asignar persona a este gasto">
                                        <i class="bi bi-check2"></i>
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end balance-negative">{{ $money($e->amount) }}</td>
                        <td class="actions-cell">
                            @if (! $budget->isLocked())
                                @can('manage')
                                    <div class="actions">
                                        <a href="{{ route('expenses.edit', $e) }}"
                                           class="btn btn-info btn-icon btn-sm"
                                           data-bs-toggle="tooltip" title="Editar gasto">
                                            <i class="bi bi-pencil-fill"></i>
                                        </a>
                                        <form method="POST" action="{{ route('expenses.destroy', $e) }}" class="js-confirm-delete"
                                              data-message="¿Eliminar este gasto?">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-danger btn-icon btn-sm"
                                                    data-bs-toggle="tooltip" title="Eliminar gasto">
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
                    <tr><td colspan="7" class="text-center text-muted py-4">Aún no hay gastos.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
