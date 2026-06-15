<?php

namespace App\Filament\Resources\ContentPieces\Schemas;

use App\Models\Belief;
use App\Models\ContentPiece;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ContentPieceInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Pieza')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('title')->label('Título')->columnSpanFull(),
                        TextEntry::make('status')->label('Estado')->badge(),
                        TextEntry::make('objective')->label('Objetivo')->badge()->placeholder('Sin objetivo'),
                        TextEntry::make('format')->label('Formato')->badge()->placeholder('—'),
                        TextEntry::make('rating')->label('Calificación')->badge()->placeholder('Sin calificar'),
                        TextEntry::make('published_at')
                            ->label('Fecha de publicación')
                            ->date()
                            ->placeholder('Sin publicar'),
                        TextEntry::make('url')
                            ->label('URL publicada')
                            ->url(fn (ContentPiece $record) => $record->url, shouldOpenInNewTab: true)
                            ->placeholder('Sin publicar')
                            ->columnSpanFull(),
                    ]),

                Section::make('Guión')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('hook')->label('Gancho')->placeholder('—'),
                        TextEntry::make('story')->label('Historia')->placeholder('—'),
                        TextEntry::make('moral')->label('Moraleja')->placeholder('—'),
                        TextEntry::make('cta')->label('CTA')->placeholder('—'),
                    ]),

                // VISIBILIDAD MULTI-SALTO: ContentPiece → WinningIdea → Questions → Beliefs.
                Section::make('Idea ganadora')
                    ->schema([
                        TextEntry::make('winningIdea.title')
                            ->hiddenLabel()
                            ->placeholder('Esta pieza no nace de una idea ganadora (pieza suelta).'),
                    ]),

                Section::make('Preguntas que responde (vía la idea)')
                    ->schema([
                        TextEntry::make('derived_questions')
                            ->hiddenLabel()
                            ->state(fn (ContentPiece $record): array => $record->derivedQuestions()
                                ->pluck('body')
                                ->all())
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Sin preguntas: la pieza no tiene idea, o la idea no tiene preguntas.'),
                    ]),

                Section::make('Mitos y verdades (vía la idea)')
                    ->schema([
                        TextEntry::make('derived_beliefs')
                            ->hiddenLabel()
                            ->state(fn (ContentPiece $record): array => $record->derivedBeliefs()
                                ->map(fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement)
                                ->all())
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Sin mitos/verdades derivados.'),
                    ]),
            ]);
    }
}
