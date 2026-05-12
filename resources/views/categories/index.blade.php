@extends('layouts.app')
@section('title', 'Categorías')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="page-title">Categorías</h1>
        @can('manage')
            <a href="{{ route('categories.create') }}" class="btn btn-primary"
               data-bs-toggle="tooltip" title="Crear una nueva categoría">
                <i class="bi bi-plus-lg"></i> Nueva
            </a>
        @endcan
    </div>

    <div class="row g-3">
        @forelse ($categories as $c)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card card-stat">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="category-chip" style="background: {{ $c->color }}22; color: {{ $c->color }}">
                                <i class="bi {{ $c->icon }}"></i> {{ $c->name }}
                            </div>
                            <div class="text-muted small mt-2">{{ $c->fixed_expenses_count }} gastos fijos</div>
                        </div>
                        @can('manage')
                            <div class="actions">
                                <a href="{{ route('categories.edit', $c) }}"
                                   class="btn btn-info btn-icon btn-sm"
                                   data-bs-toggle="tooltip" title="Editar categoría">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @php $protected = $c->isProtected(); @endphp
                                <form method="POST" action="{{ route('categories.destroy', $c) }}"
                                      @unless($protected) class="js-confirm-delete" data-message="¿Eliminar la categoría {{ $c->name }}?" @endunless>
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-icon btn-sm"
                                            data-bs-toggle="tooltip"
                                            title="{{ $protected ? 'Categoría protegida (no se puede eliminar)' : 'Eliminar categoría' }}"
                                            @disabled($protected)>
                                        <i class="bi bi-{{ $protected ? 'lock-fill' : 'trash-fill' }}"></i>
                                    </button>
                                </form>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="alert alert-info">Aún no hay categorías.</div></div>
        @endforelse
    </div>
@endsection
