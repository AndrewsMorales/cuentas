<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Gestión de usuarios del sistema. Sólo accesible para usuarios con rol
 * "manager" (gestión completa).
 */
class UserController extends Controller
{
    public function index(): View
    {
        return view('users.index', [
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User(['role' => User::ROLE_MANAGER]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', 'unique:users,email'],
            'role'     => ['required', Rule::in([User::ROLE_MANAGER, User::ROLE_VIEWER])],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        return view('users.form', ['user' => $user]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:100'],
            'email'    => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', Rule::in([User::ROLE_MANAGER, User::ROLE_VIEWER])],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // No permitir que el último manager se rebaje a viewer.
        if ($user->isManager() && $data['role'] === User::ROLE_VIEWER) {
            $otherManagers = User::where('role', User::ROLE_MANAGER)->where('id', '!=', $user->id)->count();
            if ($otherManagers === 0) {
                return back()->withInput()->with('error',
                    'No puedes quitarle "Gestión" al único usuario administrador.');
            }
        }

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'Usuario actualizado.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'No puedes eliminar tu propio usuario.');
        }

        if ($user->isManager()) {
            $otherManagers = User::where('role', User::ROLE_MANAGER)->where('id', '!=', $user->id)->count();
            if ($otherManagers === 0) {
                return back()->with('error',
                    'No puedes eliminar al último usuario con permisos de gestión.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Usuario eliminado.');
    }
}
