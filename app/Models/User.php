<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Usuario del sistema.
 *
 * @property string $role  'manager' (gestión completa) | 'viewer' (solo lectura)
 */
#[Fillable(['name', 'email', 'password', 'role', 'is_super_admin'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_MANAGER = 'manager';
    public const ROLE_VIEWER  = 'viewer';

    public const ROLES = [
        self::ROLE_MANAGER => 'Gestión',
        self::ROLE_VIEWER  => 'Visualización',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_super_admin'    => 'bool',
        ];
    }

    public function isManager(): bool
    {
        return $this->role === self::ROLE_MANAGER;
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    public function roleLabel(): string
    {
        // La columna trae default en base de datos, pero una instancia recién
        // creada todavía no lo tiene cargado: sin esto el layout revienta.
        return self::ROLES[$this->role] ?? (string) ($this->role ?? '—');
    }
}
