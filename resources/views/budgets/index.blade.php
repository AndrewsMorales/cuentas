@extends('layouts.app')
@section('title', 'Meses')

@php $money = fn ($v) => '$' . number_format((float)$v, 0, ',', '.'); @endphp

@section('content')
    <h1 class="page-title mb-3">Presupuestos por mes</h1>

    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th class="text-end">Ingresos</th>
                        <th class="text-end">Gastos</th>
                        <th class="text-end">Balance</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($budgets as $b)
                    @php $balance = ((float)$b->total_income) - ((float)$b->total_expense); @endphp
                    <tr>
                        <td class="text-capitalize">{{ $b->label() }}</td>
                        <td class="text-end balance-positive">{{ $money($b->total_income) }}</td>
                        <td class="text-end balance-negative">{{ $money($b->total_expense) }}</td>
                        <td class="text-end {{ $balance >= 0 ? 'balance-positive' : 'balance-negative' }}">{{ $money($balance) }}</td>
                        <td class="actions-cell">
                            <div class="actions">
                                <a href="{{ route('budgets.show', [$b->year, $b->month]) }}" class="btn btn-info btn-sm"
                                   data-bs-toggle="tooltip" title="Ver detalle del mes">
                                    Ver
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center text-muted py-4">Aún no se ha creado ningún mes.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
