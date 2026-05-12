<?php

namespace App\Providers;

use App\Models\MonthlyBudget;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Permiso de gestión: usuarios con rol "manager" pueden modificar datos.
        Gate::define('manage', fn (User $user) => $user->isManager());

        // Pestaña de usuarios: SOLO el super admin (Andrés).
        Gate::define('manage-users', fn (User $user) => $user->isSuperAdmin());

        // Permiso de modificar un mes: bloqueado si el mes ya pasó.
        Gate::define('edit-budget', function (User $user, MonthlyBudget $budget) {
            if (! $user->isManager()) return false;
            return ! $budget->isLocked();
        });
    }
}
