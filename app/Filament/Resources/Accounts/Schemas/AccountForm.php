<?php

namespace App\Filament\Resources\Accounts\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('slug')
                    ->label('Slug (URL)')
                    ->maxLength(255)
                    ->helperText('Se genera del nombre si lo dejas vacío. Debe ser único.')
                    ->unique(ignoreRecord: true),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('brand_promise')
                    ->label('Promesa de la marca')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('main_offers')
                    ->label('Oferta(s) principal(es)')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('ideal_customer_profile')
                    ->label('Perfil del cliente ideal')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Marca activa')
                    ->default(true)
                    ->helperText('Si la suspendes, sus miembros no podrán acceder a ella (el super admin sí).'),
            ]);
    }
}
