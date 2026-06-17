<?php

namespace App\Filament\Resources\ContentPieces\Schemas;

use App\Models\Belief;
use App\Models\ContentPiece;
use App\Support\Rum;
use Filament\Infolists\Components\ImageEntry;
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
                        ImageEntry::make('preview_image_url')
                            ->label('Vista previa del post')
                            ->height(200)
                            ->placeholder('Sin imagen')
                            ->columnSpanFull(),
                    ]),

                Section::make('Evaluación RUM')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('rum')
                            ->label('RUM')
                            ->badge()
                            ->size('lg')
                            ->color(fn ($state): string => Rum::color($state !== null ? (float) $state : null))
                            ->formatStateUsing(fn ($state): string => $state !== null ? number_format((float) $state, 1) : '—')
                            ->placeholder('Sin evaluar')
                            ->columnSpanFull(),
                        ...array_map(
                            fn (string $key) => TextEntry::make("rum_factors.{$key}")
                                ->label(Rum::FACTORS[$key]['label'])
                                ->formatStateUsing(fn ($state): ?string => $state !== null ? (Rum::optionsFor($key)[(string) $state] ?? $state) : null)
                                ->placeholder('—'),
                            array_keys(Rum::FACTORS),
                        ),
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
