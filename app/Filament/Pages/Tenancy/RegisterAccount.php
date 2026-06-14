<?php

namespace App\Filament\Pages\Tenancy;

use App\Models\Account;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Tenancy\RegisterTenant;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

class RegisterAccount extends RegisterTenant
{
    public static function getLabel(): string
    {
        return 'Crear marca';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre de la marca')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3),
            ]);
    }

    protected function handleRegistration(array $data): Model
    {
        $account = Account::create($data);

        // Asociar la marca recién creada al usuario actual para que aparezca
        // en su selector de tenants y pueda acceder a ella.
        $account->users()->attach(Filament::auth()->id());

        return $account;
    }
}
