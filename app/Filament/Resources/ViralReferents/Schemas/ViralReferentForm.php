<?php

namespace App\Filament\Resources\ViralReferents\Schemas;

use App\Models\Niche;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ViralReferentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(255),
                Select::make('niche_id')
                    ->label('Nicho')
                    ->relationship('niche', 'name')
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin nicho')
                    ->createOptionForm([
                        TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),
                        ColorPicker::make('color')
                            ->label('Color'),
                        Textarea::make('description')
                            ->label('Descripción')
                            ->rows(2),
                    ])
                    ->createOptionUsing(fn (array $data): int => Niche::create($data)->getKey()),
                TextInput::make('instagram_url')
                    ->label('URL de Instagram')
                    ->url()
                    ->maxLength(2048)
                    ->prefixIcon('heroicon-m-link')
                    ->placeholder('https://instagram.com/...'),
                Textarea::make('notes')
                    ->label('Notas')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
