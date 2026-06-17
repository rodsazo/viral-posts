<?php

namespace App\Filament\Resources\AccountInvitations\Schemas;

use App\Enums\TeamRole;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AccountInvitationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('email')
                    ->label('Email del invitado')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->rule(fn () => function (string $attribute, $value, $fail): void {
                        $tenant = Filament::getTenant();

                        if ($tenant->users()->where('email', $value)->exists()) {
                            $fail('Esa persona ya es miembro de esta marca.');

                            return;
                        }

                        // El índice único (account_id, email) reventaría con un 500 si no validamos aquí.
                        if ($tenant->invitations()->where('email', $value)->exists()) {
                            $fail('Ya existe una invitación para ese email. Revócala antes de volver a invitar.');
                        }
                    })
                    ->helperText('Recibirá un correo con el enlace para unirse.'),
                Select::make('role')
                    ->label('Rol')
                    ->options(TeamRole::class)
                    ->default(TeamRole::Editor->value)
                    ->required()
                    ->native(false),
            ]);
    }
}
