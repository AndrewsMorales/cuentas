@extends('layouts.app')
@section('title', 'Gastos · ' . ucfirst($budget->label()))

@php $money = fn ($v) => '$' . number_format((float)$v, 0, ',', '.'); @endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3 gap-3">
        <div>
            <h1 class="page-title">Gastos</h1>
            <div class="page-subtitle">{{ $budget->label() }}</div>
        </div>
        @php
            $assignedQ1 = (float) $totalsExpenses->where('fortnight', 1)->whereNotNull('person_id')->sum('amount');
            $assignedQ2 = (float) $totalsExpenses->where('fortnight', 2)->whereNotNull('person_id')->sum('amount');
        @endphp
        <div class="d-flex gap-3 align-items-center flex-wrap">
            <div class="d-flex gap-2" data-bs-toggle="tooltip" data-bs-placement="bottom"
                 title="Total ya asignado a alguna persona — excluye gastos fijos pendientes">
                <div class="px-3 py-2 text-center" style="background: var(--surface-3); border-radius: 10px; min-width:110px;">
                    <small class="text-muted d-block" style="font-size:.7rem; letter-spacing:.06em; text-transform:uppercase;">1ª quinc.</small>
                    <strong class="balance-negative d-block">{{ $money($assignedQ1) }}</strong>
                </div>
                <div class="px-3 py-2 text-center" style="background: var(--surface-3); border-radius: 10px; min-width:110px;">
                    <small class="text-muted d-block" style="font-size:.7rem; letter-spacing:.06em; text-transform:uppercase;">2ª quinc.</small>
                    <strong class="balance-negative d-block">{{ $money($assignedQ2) }}</strong>
                </div>
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

    {{-- Panel de filtros --}}
    <div class="card card-stat mb-3">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="year"  value="{{ $budget->year }}">
                <input type="hidden" name="month" value="{{ $budget->month }}">

                <div class="col-6 col-md-3 col-lg">
                    <label class="form-label mb-1"><i class="bi bi-person"></i> Persona</label>
                    <select name="person_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        <option value="unassigned" @selected(($filters['person_id'] ?? '') === 'unassigned')>— Sin asignar —</option>
                        @foreach ($people as $p)
                            <option value="{{ $p->id }}" @selected((string) ($filters['person_id'] ?? '') === (string) $p->id)>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg">
                    <label class="form-label mb-1"><i class="bi bi-tag"></i> Categoría</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected((string) ($filters['category_id'] ?? '') === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg">
                    <label class="form-label mb-1"><i class="bi bi-calendar3-range"></i> Quincena</label>
                    <select name="fortnight" class="form-select form-select-sm">
                        <option value="">Ambas</option>
                        <option value="1" @selected((string) ($filters['fortnight'] ?? '') === '1')>1ª quincena</option>
                        <option value="2" @selected((string) ($filters['fortnight'] ?? '') === '2')>2ª quincena</option>
                    </select>
                </div>

                <div class="col-6 col-md-3 col-lg">
                    <label class="form-label mb-1"><i class="bi bi-calendar-event"></i> Fecha exacta</label>
                    <input type="date" name="date" class="form-control form-control-sm" value="{{ $filters['date'] ?? '' }}">
                </div>

                <div class="col-12 col-lg-auto d-flex gap-2">
                    <button class="btn btn-info btn-sm" data-bs-toggle="tooltip" title="Aplicar filtros">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    @if ($hasActiveFilters)
                        <a href="{{ route('expenses.index', ['year' => $budget->year, 'month' => $budget->month]) }}"
                           class="btn btn-light btn-sm"
                           data-bs-toggle="tooltip" title="Quitar todos los filtros">
                            <i class="bi bi-x-lg"></i> Limpiar
                        </a>
                    @endif
                </div>
            </form>

            @if ($hasActiveFilters)
                @php
                    $filteredTotal = (float) $expenses->sum('amount');
                @endphp
                <div class="mt-2 pt-2 small text-muted" style="border-top: 1px solid var(--border);">
                    <i class="bi bi-info-circle"></i>
                    Mostrando <strong>{{ $expenses->count() }}</strong> gasto(s) ·
                    Total filtrado: <strong class="balance-negative">{{ $money($filteredTotal) }}</strong>
                </div>
            @endif
        </div>
    </div>

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
