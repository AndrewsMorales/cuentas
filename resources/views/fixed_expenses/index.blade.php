@extends('layouts.app')
@section('title', 'Gastos fijos')

@php $money = fn ($v) => '$' . number_format((float)$v, 0, ',', '.'); @endphp

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="page-title">Gastos fijos</h1>
            <small class="text-muted">Se cargan automáticamente al iniciar un nuevo mes.</small>
        </div>
        @can('manage')
            <a href="{{ route('fixed-expenses.create') }}" class="btn btn-primary"
               data-bs-toggle="tooltip" title="Crear un nuevo gasto fijo plantilla">
                <i class="bi bi-plus-lg"></i> Nuevo
            </a>
        @endcan
    </div>

    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Categoría</th>
                        <th class="text-end">Promedio</th>
                        <th class="text-center">Quincena</th>
                        <th class="text-center">Activo</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($items as $i)
                    <tr>
                        <td>{{ $i->name }}</td>
                        <td>
                            <span class="category-chip" style="background: {{ $i->category->color }}22; color: {{ $i->category->color }}">
                                <i class="bi {{ $i->category->icon }}"></i> {{ $i->category->name }}
                            </span>
                        </td>
                        <td class="text-end">{{ $money($i->average_amount) }}</td>
                        <td class="text-center">{{ $i->fortnight === 1 ? '1ª' : '2ª' }}</td>
                        <td class="text-center">
                            @if ($i->active)
                                <span class="badge bg-success">Sí</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            @can('manage')
                                <div class="actions">
                                    <a href="{{ route('fixed-expenses.edit', $i) }}"
                                       class="btn btn-info btn-icon btn-sm"
                                       data-bs-toggle="tooltip" title="Editar gasto fijo">
                                        <i class="bi bi-pencil-fill"></i>
                                    </a>
                                    <form method="POST" action="{{ route('fixed-expenses.destroy', $i) }}"
                                          class="js-confirm-delete"
                                          data-message="¿Eliminar la plantilla &quot;{{ $i->name }}&quot;? Los gastos ya cargados en meses anteriores no se borran.">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-icon btn-sm"
                                                data-bs-toggle="tooltip" title="Eliminar gasto fijo">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                </div>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No hay gastos fijos cargados.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
