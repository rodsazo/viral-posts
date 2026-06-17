<?php

namespace App\Filament\Resources\HerasTemplates\Schemas;

use App\Models\HerasTemplate;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HerasTemplateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('number')->label('Número'),
                        TextEntry::make('name')->label('Nombre'),
                        TextEntry::make('viralReferent.name')->label('Referente viral')->placeholder('—'),
                        TextEntry::make('viralReferent.niche.name')->label('Nicho')->placeholder('—'),
                        TextEntry::make('suggested_format')->label('Formato sugerido')->placeholder('—'),
                        TextEntry::make('viral_mechanism')->label('Mecanismo')->badge()->placeholder('—'),
                        TextEntry::make('structure')->label('Estructura')->placeholder('—')->columnSpanFull(),
                    ]),

                Section::make('Referencia viral')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('reference_url')
                            ->label('Post de referencia')
                            ->url(fn (HerasTemplate $record): ?string => $record->reference_url, shouldOpenInNewTab: true)
                            ->icon('heroicon-m-link')
                            ->color('info')
                            ->placeholder('Sin URL'),
                        ImageEntry::make('preview_image_url')
                            ->label('Vista previa')
                            ->height(220)
                            ->placeholder('Sin imagen')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
