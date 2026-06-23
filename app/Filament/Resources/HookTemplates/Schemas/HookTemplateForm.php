<?php

namespace App\Filament\Resources\HookTemplates\Schemas;

use App\Support\FontAwesomeIcons;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HookTemplateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Plantilla')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nombre del gancho')
                            ->required()
                            ->maxLength(255),
                        Select::make('viral_referent_id')
                            ->label('Referente')
                            ->relationship('viralReferent', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('icon')
                            ->label('Ícono (FontAwesome)')
                            ->options(FontAwesomeIcons::options())
                            ->allowHtml()
                            ->searchable()
                            ->native(false)
                            ->placeholder('Sin ícono'),
                        Textarea::make('objective')
                            ->label('Objetivo')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notas')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Ejemplos por nicho')
                    ->columns(2)
                    ->schema([
                        Textarea::make('example_generic')
                            ->label('Ejemplo genérico')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('example_health')->label('Nicho Salud')->rows(2),
                        Textarea::make('example_sex')->label('Nicho Sexo')->rows(2),
                        Textarea::make('example_money')->label('Nicho Dinero')->rows(2),
                        Textarea::make('example_personal_dev')->label('Nicho Desarrollo Personal')->rows(2),
                    ]),

                Section::make('Referencias')
                    ->schema([
                        TextInput::make('reference_url')
                            ->label('URL de referencia')
                            ->url()
                            ->maxLength(2048)
                            ->placeholder('https://...'),
                        Repeater::make('real_examples')
                            ->label('Ejemplos reales (URLs)')
                            ->simple(
                                TextInput::make('url')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://...'),
                            )
                            ->addActionLabel('Añadir URL')
                            ->reorderable(false)
                            ->defaultItems(0),
                    ]),
            ]);
    }
}
