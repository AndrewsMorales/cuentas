@extends('layouts.app')
@section('title', 'Detalle · ' . ucfirst($budget->label()))

@php $money = fn ($v) => '$' . number_format((float)$v, 0, ',', '.'); @endphp

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3 gap-3">
        <div>
            <h1 class="page-title text-capitalize">{{ $budget->label() }}</h1>
            <small class="text-muted">Detalle completo del mes</small>
        </div>
        @if ($summary['pending_fixed'] === 0 && $budget->expenses()->where('is_fixed_template', true)->doesntExist())
            <form method="POST" action="{{ route('budgets.reload-fixed', [$budget->year, $budget->month]) }}">
                @csrf
                <button class="btn btn-primary"
                        data-bs-toggle="tooltip" title="Cargar los gastos fijos plantilla en este mes">
                    <i class="bi bi-arrow-clockwise"></i> Cargar gastos fijos
                </button>
            </form>
        @endif
    </div>

    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-positive h-100">
                <div class="stat-label"><i class="bi bi-arrow-down-circle"></i> Ingresos</div>
                <div class="stat-value balance-positive" style="font-size:1.4rem">{{ $money($summary['total_income']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-negative h-100">
                <div class="stat-label"><i class="bi bi-arrow-up-circle"></i> Gastos</div>
                <div class="stat-value balance-negative" style="font-size:1.4rem">{{ $money($summary['total_expense']) }}</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-brand h-100">
                <div class="stat-label"><i class="bi bi-wallet2"></i> Balance</div>
                <div class="stat-value {{ $summary['balance'] >= 0 ? 'balance-positive' : 'balance-negative' }}" style="font-size:1.4rem">
                    {{ $money($summary['balance']) }}
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="stat-card stat-warning h-100">
                <div class="stat-label"><i class="bi bi-pin-angle"></i> Fijos pendientes</div>
                <div class="stat-value" style="font-size:1.4rem">{{ $summary['pending_fixed'] }}</div>
            </div>
        </div>
    </div>

    <div class="card card-stat mb-3">
        <div class="card-header"><i class="bi bi-people"></i> Disponible por persona</div>
        <div class="card-body">
            <div class="row g-3">
                @foreach ($summary['by_person'] as $row)
                    <div class="col-md-6">
                        <div class="person-card h-100" style="--person-color: {{ $row['person']->color }}">
                            <div class="d-flex justify-content-between align-items-start">
                                <strong class="fs-5"><span class="person-dot" style="background: {{ $row['person']->color }}"></span>{{ $row['person']->name }}</strong>
                                <span class="badge"
                                      style="background: {{ $row['percent_used'] >= 100 ? 'var(--negative)' : ($row['percent_used'] >= 80 ? 'var(--accent)' : 'var(--positive)') }}; color: #fff;">
                                    {{ $row['percent_used'] }}% gastado
                                </span>
                            </div>
                            <div class="stat-label mt-3">Le queda</div>
                            <div class="stat-value {{ $row['remaining'] >= 0 ? 'balance-positive' : 'balance-negative' }}" style="font-size:1.7rem">
                                {{ $money($row['remaining']) }}
                            </div>
                            <div class="progress mt-2" style="height: 8px; background: var(--surface-3);">
                                <div class="progress-bar" style="width: {{ min(100, $row['percent_used']) }}%; background: linear-gradient(90deg, {{ $row['person']->color }}, var(--brand-glow));"></div>
                            </div>
                            <div class="d-flex justify-content-between small text-muted mt-2">
                                <span>Ingresó <strong class="balance-positive">{{ $money($row['income']) }}</strong></span>
                                <span>Gastó <strong class="balance-negative">{{ $money($row['expense']) }}</strong></span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card card-stat">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-arrow-down-circle"></i> Ingresos</span>
                    <a href="{{ route('incomes.index', ['year' => $budget->year, 'month' => $budget->month]) }}" class="small"
                       data-bs-toggle="tooltip" title="Gestionar ingresos del mes">Gestionar →</a>
                </div>
                <ul class="list-group list-group-flush">
                    @forelse ($budget->incomes as $i)
                        <li class="list-group-item d-flex justify-content-between">
                            <span><span class="person-dot" style="background: {{ $i->person->color }}"></span> {{ $i->person->name }}
                                <small class="text-muted">· {{ $i->fortnight === 1 ? '1ª' : '2ª' }} quincena</small></span>
                            <strong class="balance-positive">{{ $money($i->amount) }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Sin ingresos.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card card-stat">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-arrow-up-circle"></i> Gastos</span>
                    <a href="{{ route('expenses.index', ['year' => $budget->year, 'month' => $budget->month]) }}" class="small"
                       data-bs-toggle="tooltip" title="Gestionar gastos del mes">Gestionar →</a>
                </div>
                <ul class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                    @forelse ($budget->expenses as $e)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <span class="category-chip" style="background: {{ $e->category->color }}22; color: {{ $e->category->color }}">
                                    <i class="bi {{ $e->category->icon }}"></i> {{ $e->category->name }}
                                </span>
                                {{ $e->description }}
                                @if ($e->person)
                                    <small class="text-muted">· {{ $e->person->name }}</small>
                                @else
                                    <span class="badge bg-warning">Sin asignar</span>
                                @endif
                            </span>
                            <strong class="balance-negative">{{ $money($e->amount) }}</strong>
                        </li>
                    @empty
                        <li class="list-group-item text-muted">Sin gastos.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
