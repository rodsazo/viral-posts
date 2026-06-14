<?php

namespace App\Filament\Resources\ContentPieces\Schemas;

use App\Enums\ContentFormat;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ContentPieceForm
{
    public static function configure(Schema $schema): Schema
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $schema
            ->components([
                Section::make('Pieza')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título de trabajo')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('winning_idea_id')
                            ->label('Idea ganadora')
                            ->relationship('winningIdea', 'title', $scopeToTenant)
                            ->searchable()
                            ->preload()
                            ->placeholder('Sin idea (pieza suelta)')
                            ->helperText('Opcional: la pieza puede existir sin idea ganadora.'),
                        Select::make('format')
                            ->label('Formato')
                            ->options(ContentFormat::class)
                            ->native(false),
                        Select::make('status')
                            ->label('Estado')
                            ->options(ContentStatus::class)
                            ->default(ContentStatus::Planificacion->value)
                            ->required()
                            ->native(false),
                        Select::make('rating')
                            ->label('Calificación')
                            ->options(ContentRating::class)
                            ->native(false)
                            ->placeholder('Sin calificar'),
                    ]),

                Section::make('Guión')
                    ->description('Gancho → Historia → Moraleja → CTA.')
                    ->schema([
                        Textarea::make('hook')
                            ->label('Gancho')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('story')
                            ->label('Historia')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('moral')
                            ->label('Moraleja')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('cta')
                            ->label('CTA')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),

                Section::make('Publicación')
                    ->schema([
                        TextInput::make('url')
                            ->label('URL publicada')
                            ->url()
                            ->placeholder('https://...'),
                    ]),
            ]);
    }
}
