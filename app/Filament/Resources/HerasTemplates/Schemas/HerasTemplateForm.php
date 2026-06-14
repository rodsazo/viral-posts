<?php

namespace App\Filament\Resources\HerasTemplates\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class HerasTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('number')
                    ->label('Número')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(30)
                    ->unique(ignoreRecord: true),
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                TextInput::make('suggested_format')
                    ->label('Formato sugerido')
                    ->maxLength(255),
                TextInput::make('viral_mechanism')
                    ->label('Mecanismo de viralidad')
                    ->maxLength(255),
                Textarea::make('structure')
                    ->label('Estructura')
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }
}
