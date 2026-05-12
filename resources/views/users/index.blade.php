@extends('layouts.app')
@section('title', 'Usuarios')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="page-title">Usuarios</h1>
            <small class="text-muted">Gestiona quién accede a la app y con qué permisos.</small>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary"
           data-bs-toggle="tooltip" title="Crear un nuevo usuario">
            <i class="bi bi-person-plus"></i> Nuevo usuario
        </a>
    </div>

    <div class="card card-stat">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th class="text-center">Rol</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($users as $u)
                    <tr>
                        <td>
                            <strong>{{ $u->name }}</strong>
                            @if ($u->id === auth()->id())
                                <span class="badge bg-secondary ms-1">Tú</span>
                            @endif
                        </td>
                        <td><span class="text-muted">{{ $u->email }}</span></td>
                        <td class="text-center">
                            @if ($u->isManager())
                                <span class="badge" style="background: var(--brand); color:#fff">
                                    <i class="bi bi-shield-check"></i> Gestión
                                </span>
                            @else
                                <span class="badge" style="background: var(--info); color:#fff">
                                    <i class="bi bi-eye"></i> Visualización
                                </span>
                            @endif
                        </td>
                        <td class="actions-cell">
                            <div class="actions">
                                <a href="{{ route('users.edit', $u) }}"
                                   class="btn btn-info btn-icon btn-sm"
                                   data-bs-toggle="tooltip" title="Editar usuario">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                @if ($u->id !== auth()->id())
                                    <form method="POST" action="{{ route('users.destroy', $u) }}"
                                          class="js-confirm-delete"
                                          data-message="¿Eliminar al usuario {{ $u->name }}?">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-icon btn-sm"
                                                data-bs-toggle="tooltip" title="Eliminar usuario">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
