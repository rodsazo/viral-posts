<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                Toggle::make('is_active')
                    ->label('Usuario activo')
                    ->default(true)
                    // No puedes desactivarte a ti mismo (evita bloquearte).
                    ->disabled(fn (?User $record): bool => $record !== null && Filament::auth()->id() === $record->getKey())
                    ->helperText('Si lo desactivas, el usuario no podrá acceder al panel ni al Estudio.'),
                // Solo lectura: el rol super admin se otorga/revoca por línea de comando.
                Toggle::make('is_super_admin')
                    ->label('Super admin')
                    ->disabled()
                    ->dehydrated(false)
                    ->helperText('Se gestiona solo por consola: php artisan super-admin grant|revoke <email>.'),
            ]);
    }
}
