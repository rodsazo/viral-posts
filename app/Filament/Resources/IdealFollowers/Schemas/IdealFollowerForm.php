<?php

namespace App\Filament\Resources\IdealFollowers\Schemas;

use App\Enums\AwarenessLevel;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class IdealFollowerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre del perfil')
                    ->required()
                    ->maxLength(255),
                Select::make('awareness_level')
                    ->label('Nivel de conciencia (Heras)')
                    ->options(AwarenessLevel::class)
                    ->native(false)
                    ->placeholder('Sin definir')
                    ->helperText('Del 0 (no sabe que tiene un problema) al 4 (sabe con qué producto resolverlo).'),
                Textarea::make('description')
                    ->label('Descripción')
                    ->rows(4)
                    ->columnSpanFull(),
            ]);
    }
}
