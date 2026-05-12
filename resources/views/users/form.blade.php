@extends('layouts.app')
@section('title', ($user->exists ? 'Editar' : 'Nuevo') . ' usuario')

@section('content')
    <h1 class="page-title mb-3">{{ $user->exists ? 'Editar' : 'Nuevo' }} usuario</h1>

    <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}" class="card card-stat p-4">
        @csrf
        @if ($user->exists) @method('PUT') @endif

        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label">Nombre</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Correo</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
        </div>

        <div class="mt-3">
            <label class="form-label d-block">Rol</label>
            <div class="btn-group" role="group">
                <input type="radio" class="btn-check" name="role" id="role-mgr" value="{{ \App\Models\User::ROLE_MANAGER }}"
                       @checked(old('role', $user->role) === \App\Models\User::ROLE_MANAGER)>
                <label class="btn btn-outline-primary" for="role-mgr">
                    <i class="bi bi-shield-check"></i> Gestión
                </label>
                <input type="radio" class="btn-check" name="role" id="role-view" value="{{ \App\Models\User::ROLE_VIEWER }}"
                       @checked(old('role', $user->role) === \App\Models\User::ROLE_VIEWER)>
                <label class="btn btn-outline-primary" for="role-view">
                    <i class="bi bi-eye"></i> Visualización
                </label>
            </div>
            <small class="text-muted d-block mt-2">
                <strong>Gestión</strong>: puede crear, editar y eliminar todo.
                <strong>Visualización</strong>: solo puede ver datos, no modificarlos.
            </small>
        </div>

        <div class="row g-2 mt-3">
            <div class="col-md-6">
                <label class="form-label">Contraseña {{ $user->exists ? '(dejar vacío para no cambiar)' : '' }}</label>
                <input type="password" name="password" class="form-control" {{ $user->exists ? '' : 'required' }} minlength="6">
            </div>
            <div class="col-md-6">
                <label class="form-label">Confirmar contraseña</label>
                <input type="password" name="password_confirmation" class="form-control" {{ $user->exists ? '' : 'required' }} minlength="6">
            </div>
        </div>

        <div class="d-flex gap-2 mt-4">
            <button class="btn btn-primary" data-bs-toggle="tooltip" title="Guardar el usuario">
                <i class="bi bi-check2-circle"></i> Guardar
            </button>
            <a href="{{ route('users.index') }}" class="btn btn-light"
               data-bs-toggle="tooltip" title="Descartar cambios">
                <i class="bi bi-x-lg"></i> Cancelar
            </a>
        </div>
    </form>
@endsection
