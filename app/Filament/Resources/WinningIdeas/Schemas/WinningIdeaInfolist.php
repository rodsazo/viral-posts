<?php

namespace App\Filament\Resources\WinningIdeas\Schemas;

use App\Models\Belief;
use App\Models\WinningIdea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class WinningIdeaInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Idea ganadora')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('title')->label('Título'),
                        TextEntry::make('viral_mechanism')
                            ->label('Mecanismo de viralidad')
                            ->badge()
                            ->placeholder('—'),
                        TextEntry::make('herasTemplate.display_name')
                            ->label('Plantilla Heras')
                            ->placeholder('Sin plantilla'),
                        TextEntry::make('validation')
                            ->label('Validación')
                            ->badge()
                            ->state(fn (WinningIdea $record) => $record->validationStatus()),
                        TextEntry::make('example_urls')
                            ->label('Ejemplos reales (viralidad)')
                            ->state(fn (WinningIdea $record): array => $record->example_urls ?? [])
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->url(fn (string $state): string => $state, shouldOpenInNewTab: true)
                            ->color('info')
                            ->placeholder('Sin ejemplos: idea pendiente de validación.')
                            ->columnSpanFull(),
                        TextEntry::make('concept')
                            ->label('Concepto')
                            ->columnSpanFull(),
                    ]),

                Section::make('Preguntas que resuelve')
                    ->schema([
                        TextEntry::make('questions')
                            ->hiddenLabel()
                            ->state(fn (WinningIdea $record): array => $record->questions->pluck('body')->all())
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Esta idea aún no tiene preguntas relacionadas.'),
                    ]),

                // VISIBILIDAD MULTI-SALTO: WinningIdea → Questions → Beliefs.
                Section::make('Mitos y verdades (derivados de las preguntas)')
                    ->description('Se calculan automáticamente a partir de las preguntas relacionadas, sin duplicados.')
                    ->schema([
                        TextEntry::make('derived_beliefs')
                            ->hiddenLabel()
                            ->state(fn (WinningIdea $record): array => $record->derivedBeliefs()
                                ->map(fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement)
                                ->all())
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Sin mitos/verdades: la idea no tiene preguntas, o sus preguntas no tienen mitos/verdades asociados.'),
                    ]),
            ]);
    }
}
