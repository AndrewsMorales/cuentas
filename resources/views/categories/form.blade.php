@extends('layouts.app')
@section('title', ($category->exists ? 'Editar' : 'Nueva') . ' categoría')

@section('content')
    <h1 class="page-title mb-3">{{ $category->exists ? 'Editar' : 'Nueva' }} categoría</h1>
    <form method="POST" action="{{ $category->exists ? route('categories.update', $category) : route('categories.store') }}" class="card card-stat p-4">
        @csrf
        @if ($category->exists) @method('PUT') @endif
        <div class="mb-3">
            <label class="form-label">Nombre</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" required>
        </div>
        <div class="row g-2">
            <div class="col-md-8">
                <label class="form-label">Icono (clase Bootstrap Icons)</label>
                <input type="text" name="icon" class="form-control" value="{{ old('icon', $category->icon ?? 'bi-tag') }}" placeholder="bi-tag">
                <small class="text-muted">Ej: bi-house-door, bi-basket, bi-cup-straw, bi-bicycle</small>
            </div>
            <div class="col-md-4">
                <label class="form-label">Color</label>
                <input type="color" name="color" class="form-control form-control-color w-100" value="{{ old('color', $category->color ?? '#6c757d') }}">
            </div>
        </div>
        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary" data-bs-toggle="tooltip" title="Guardar la categoría">
                <i class="bi bi-check2-circle"></i> Guardar
            </button>
            <a href="{{ route('categories.index') }}" class="btn btn-light"
               data-bs-toggle="tooltip" title="Descartar cambios">
                <i class="bi bi-x-lg"></i> Cancelar
            </a>
        </div>
    </form>
@endsection
