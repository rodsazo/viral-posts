<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/**
 * Restringe el borrado (individual y masivo) a los administradores de la marca activa.
 * Crear/editar/ver siguen disponibles para cualquier miembro (incluidos Editores).
 */
trait RestrictsDeletionToAdmins
{
    protected static function currentUserIsAdmin(): bool
    {
        $user = Filament::auth()->user();
        $tenant = Filament::getTenant();

        return $user !== null && $tenant !== null && $user->isAdminOf($tenant);
    }

    public static function canDelete(Model $record): bool
    {
        return static::currentUserIsAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::currentUserIsAdmin();
    }
}
