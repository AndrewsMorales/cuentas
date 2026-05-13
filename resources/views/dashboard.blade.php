@extends('layouts.app')
@section('title', 'Resumen · Cuentas')

@php
    $money = fn ($v) => '$' . number_format((float)$v, 0, ',', '.');
@endphp

@section('content')
    <div class="d-flex flex-wrap align-items-end justify-content-between mb-4 gap-3">
        <div>
            <h1 class="page-title">Resumen del mes</h1>
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

    @php
        $q1 = $summary['by_fortnight'][0];
        $q2 = $summary['by_fortnight'][1];
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-positive h-100">
                <div class="stat-label"><i class="bi bi-arrow-down-circle"></i> Ingresos</div>
                <div class="stat-value balance-positive">{{ $money($summary['total_income']) }}</div>
                @if ($summary['carry_over'] > 0)
                    <small class="text-muted d-block mt-2">+ {{ $money($summary['carry_over']) }} ahorro previo</small>
                @endif
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-negative h-100">
                <div class="stat-label"><i class="bi bi-arrow-up-circle"></i> Gastos</div>
                <div class="stat-value balance-negative">{{ $money($summary['total_expense']) }}</div>
                <small class="balance-negative d-block" style="opacity:.75">
                    <i class="bi bi-check2"></i> {{ $money($summary['total_expense_assigned']) }} pagado
                </small>

                <div class="d-flex justify-content-between mt-2 pt-2" style="border-top: 1px solid var(--border);">
                    <div>
                        <small class="text-muted">1ª</small>
                        <div class="balance-negative">{{ $money($q1['expense']) }}</div>
                        <small class="balance-negative" style="opacity:.75">
                            <i class="bi bi-check2"></i> {{ $money($q1['expense_assigned']) }}
                        </small>
                    </div>
                    <div class="text-end">
                        <small class="text-muted">2ª</small>
                        <div class="balance-negative">{{ $money($q2['expense']) }}</div>
                        <small class="balance-negative" style="opacity:.75">
                            <i class="bi bi-check2"></i> {{ $money($q2['expense_assigned']) }}
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-brand h-100">
                <div class="stat-label"><i class="bi bi-wallet2"></i> Disponible</div>
                <div class="stat-value {{ $summary['available'] >= 0 ? 'balance-positive' : 'balance-negative' }}">
                    {{ $money($summary['available']) }}
                </div>
                <div class="d-flex justify-content-between small mt-2 pt-2" style="border-top: 1px solid var(--border);">
                    <span>
                        <span class="text-muted">1ª</span>
                        <strong class="{{ $q1['available'] >= 0 ? 'balance-positive' : 'balance-negative' }} d-block">{{ $money($q1['available']) }}</strong>
                    </span>
                    <span class="text-end">
                        <span class="text-muted">2ª</span>
                        <strong class="{{ $q2['available'] >= 0 ? 'balance-positive' : 'balance-negative' }} d-block">{{ $money($q2['available']) }}</strong>
                    </span>
                </div>
                <small class="text-muted d-block mt-1">ingresos + ahorro − gastos</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="stat-card stat-warning h-100">
                <div class="stat-label"><i class="bi bi-piggy-bank"></i> Ahorros</div>
                <div class="stat-value">{{ $money($summary['cumulative_savings']) }}</div>
                <small class="text-muted d-block mt-2">acumulado total</small>
                @if ($summary['savings_this_month'] > 0)
                    <small class="text-muted d-block">+ {{ $money($summary['savings_this_month']) }} este mes</small>
                @endif
            </div>
        </div>
    </div>

    <div class="card card-stat mb-3">
        <div class="card-header"><i class="bi bi-people"></i> ¿Cuánto le queda a cada persona?</div>
        <div class="card-body">
            <div class="row g-3">
                @forelse ($summary['by_person'] as $row)
                    <div class="col-md-6">
                        <div class="person-card h-100" style="--person-color: {{ $row['person']->color }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="person-dot" style="background: {{ $row['person']->color }}"></span>
                                    <strong class="fs-5">{{ $row['person']->name }}</strong>
                                </div>
                                <span class="badge"
                                      style="background: {{ $row['percent_used'] >= 100 ? 'var(--negative)' : ($row['percent_used'] >= 80 ? 'var(--accent)' : 'var(--positive)') }}; color: #fff;">
                                    {{ $row['percent_used'] }}% gastado
                                </span>
                            </div>

                            <div class="stat-label mt-3">Le queda</div>
                            <div class="stat-value {{ $row['remaining'] >= 0 ? 'balance-positive' : 'balance-negative' }}" style="font-size:1.9rem">
                                {{ $money($row['remaining']) }}
                            </div>

                            <div class="progress mt-2" style="height: 8px; background: var(--surface-3);">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ min(100, $row['percent_used']) }}%; background: linear-gradient(90deg, {{ $row['person']->color }}, var(--brand-glow));"></div>
                            </div>

                            <div class="row small text-muted mt-2 g-2">
                                <div class="col-6"><i class="bi bi-arrow-down-circle"></i> Ingresó <strong class="balance-positive d-block">{{ $money($row['income']) }}</strong></div>
                                <div class="col-6"><i class="bi bi-arrow-up-circle"></i> Gastó <strong class="balance-negative d-block">{{ $money($row['expense']) }}</strong></div>
                                @if ($row['carry_over'] > 0)
                                    <div class="col-6"><i class="bi bi-piggy-bank"></i> Ahorro previo <strong class="d-block">{{ $money($row['carry_over']) }}</strong></div>
                                @endif
                                @if ($row['savings'] > 0)
                                    <div class="col-6"><i class="bi bi-bank"></i> Ahorró este mes <strong class="d-block">{{ $money($row['savings']) }}</strong></div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-muted">No hay personas registradas.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-12">
            <div class="card card-stat h-100">
                <div class="card-header"><i class="bi bi-pie-chart"></i> Por categoría</div>
                <ul class="list-group list-group-flush">
                    @forelse ($summary['by_category'] as $row)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <span class="category-chip" style="background: {{ $row['category']->color }}22; color: {{ $row['category']->color }}">
                                    <i class="bi {{ $row['category']->icon }}"></i> {{ $row['category']->name }}
                                </span>
                                <small class="text-muted ms-1">{{ $row['count'] }} movs.</small>
                            </span>
                            <strong>{{ $money($row['amount']) }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Sin gastos.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    <div class="card card-stat mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span><i class="bi bi-calendar3-range"></i> Por quincena</span>
            <a href="{{ route('budgets.show', [$budget->year, $budget->month]) }}" class="small">Ver detalle del mes →</a>
        </div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($summary['by_fortnight'] as $row)
                    <div class="col-md-6">
                        <div class="stat-card h-100">
                            <div class="stat-label">{{ $row['fortnight'] === 1 ? 'Primera quincena' : 'Segunda quincena' }}</div>
                            <div class="d-flex justify-content-between mt-2">
                                <span class="balance-positive">+ {{ $money($row['income']) }}</span>
                                <span class="balance-negative">− {{ $money($row['expense']) }}</span>
                            </div>
                            <hr class="my-2">
                            <div class="text-end">
                                <small class="text-muted">Balance</small>
                                <div class="stat-value {{ ($row['income']-$row['expense']) >= 0 ? 'balance-positive' : 'balance-negative' }}" style="font-size:1.4rem">
                                    {{ $money($row['income'] - $row['expense']) }}
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection
