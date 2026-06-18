<?php

namespace App\Filament\Concerns;

use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Model;

/**
 * Catálogos GLOBALES (Heras / Referentes / Nichos): cualquier miembro puede verlos
 * (se referencian al crear ideas/piezas), pero **crear, editar y borrar** queda
 * reservado al super admin de plataforma. Ver [docs/USUARIOS.md].
 */
trait RestrictsMutationToSuperAdmins
{
    protected static function currentUserIsSuperAdmin(): bool
    {
        return Filament::auth()->user()?->isSuperAdmin() === true;
    }

    public static function canCreate(): bool
    {
        return static::currentUserIsSuperAdmin();
    }

    public static function canEdit(Model $record): bool
    {
        return static::currentUserIsSuperAdmin();
    }

    public static function canDelete(Model $record): bool
    {
        return static::currentUserIsSuperAdmin();
    }

    public static function canDeleteAny(): bool
    {
        return static::currentUserIsSuperAdmin();
    }
}
