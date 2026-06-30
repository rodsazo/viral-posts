<?php

namespace App\Filament\Resources\WinningIdeas\Schemas;

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
                        TextEntry::make('status')->label('Estado')->badge(),
                        TextEntry::make('viral_mechanism')
                            ->label('Mecanismo de viralidad')
                            ->badge()
                            ->placeholder('—'),
                        TextEntry::make('validation')
                            ->label('Validación')
                            ->badge()
                            ->state(fn (WinningIdea $record) => $record->validationStatus()),
                        TextEntry::make('viralReferent.name')
                            ->label('Referente (si importada)')
                            ->placeholder('—'),
                        TextEntry::make('herasTemplate.display_name')
                            ->label('Plantilla Heras')
                            ->placeholder('Sin plantilla'),
                        TextEntry::make('concept')
                            ->label('Concepto / estructura')
                            ->columnSpanFull(),
                        TextEntry::make('example_urls')
                            ->label('Ejemplos reales (viralidad)')
                            ->state(fn (WinningIdea $record): array => $record->example_urls ?? [])
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->url(fn (string $state): string => $state, shouldOpenInNewTab: true)
                            ->color('info')
                            ->placeholder('Sin ejemplos: idea pendiente de validación.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
