<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;

/**
 * Recurso de plataforma: visible y accesible SOLO para el super admin.
 * Oculta la navegación y bloquea las rutas para el resto.
 */
trait SuperAdminOnly
{
    protected static function isSuperAdmin(): bool
    {
        return Filament::auth()->user()?->isSuperAdmin() === true;
    }

    public static function canViewAny(): bool
    {
        return static::isSuperAdmin();
    }

    public static function canAccess(): bool
    {
        return static::isSuperAdmin();
    }
}
