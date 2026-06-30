<?php

namespace App\Filament\Resources\WinningIdeas\Schemas;

use App\Enums\IdeaStatus;
use App\Enums\ViralMechanism;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class WinningIdeaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // La idea ganadora describe un FORMATO (independiente del seguidor):
                // el seguidor, las preguntas y los mitos se eligen al generar la PIEZA.
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(255),
                Select::make('status')
                    ->label('Estado')
                    ->options(IdeaStatus::class)
                    ->default(IdeaStatus::Borrador->value)
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->helperText('Borrador → Hipótesis → Fija (la mantenemos) o Descartada.'),
                Select::make('viral_mechanism')
                    ->label('Mecanismo de viralidad')
                    ->options(ViralMechanism::class)
                    ->native(false)
                    ->placeholder('Sin definir'),
                Textarea::make('concept')
                    ->label('Concepto / estructura')
                    ->helperText('Describe el FORMATO: estructura, condiciones y consideraciones para el video (no el video en sí).')
                    ->required()
                    ->rows(5),
                Repeater::make('example_urls')
                    ->label('Ejemplos reales (URLs)')
                    ->helperText('Posts virales de otros creadores con este formato (Instagram, TikTok…). Con al menos uno, la idea queda "Validada".')
                    ->simple(
                        TextInput::make('url')
                            ->url()
                            ->required()
                            ->placeholder('https://instagram.com/p/...'),
                    )
                    ->addActionLabel('Añadir ejemplo')
                    ->reorderable(false)
                    ->defaultItems(0),
                Select::make('heras_template_id')
                    ->label('Plantilla Heras')
                    ->relationship('herasTemplate', 'name') // global, sin escopar a la marca
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin plantilla'),
            ]);
    }
}
