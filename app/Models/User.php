<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\TeamRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Rol de plataforma. Concede acceso total vía Gate::before.
     * Se otorga/revoca SOLO por línea de comando (no `fillable`, sin UI).
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin === true;
    }

    /**
     * Marcas (cuentas/tenants) a las que pertenece el usuario.
     */
    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)->withPivot('role')->withTimestamps();
    }

    /**
     * Rol del usuario dentro de una marca (o null si no pertenece).
     */
    public function roleIn(Account $account): ?TeamRole
    {
        $role = $this->accounts()->whereKey($account->getKey())->value('role');

        return $role !== null ? TeamRole::from($role) : null;
    }

    public function isAdminOf(Account $account): bool
    {
        return $this->roleIn($account) === TeamRole::Admin;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // Usuario desactivado: sin acceso al panel.
        return $this->is_active === true;
    }

    /**
     * @return Collection<int, Account>
     */
    public function getTenants(Panel $panel): Collection
    {
        // El super admin puede operar sobre cualquier marca (no necesita ser miembro).
        if ($this->isSuperAdmin()) {
            return Account::query()->orderBy('name')->get();
        }

        // Miembros: solo marcas activas (las suspendidas no aparecen en el selector).
        return $this->accounts()->where('accounts.is_active', true)->get();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->accounts()
            ->whereKey($tenant)
            ->where('accounts.is_active', true)
            ->exists();
    }
}
