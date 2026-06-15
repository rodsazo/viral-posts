<?php

namespace App\Filament\Resources\ContentPieces\Schemas;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use App\Models\Belief;
use App\Models\WinningIdea;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ContentPieceForm
{
    public static function configure(Schema $schema): Schema
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(2)
                    ->schema([
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
                                    ->live()
                                    ->createOptionForm([
                                        TextInput::make('title')
                                            ->label('Título')
                                            ->required()
                                            ->maxLength(255),
                                        Textarea::make('concept')
                                            ->label('Concepto')
                                            ->required()
                                            ->rows(3),
                                    ])
                                    ->createOptionUsing(fn (array $data): int => WinningIdea::create([
                                        ...$data,
                                        'account_id' => Filament::getTenant()->getKey(),
                                    ])->getKey())
                                    ->placeholder('Sin idea (pieza suelta)')
                                    ->helperText('Al elegirla verás a la derecha qué responde y refuerza.'),
                                Select::make('objective')
                                    ->label('Objetivo')
                                    ->options(ContentObjective::class)
                                    ->native(false)
                                    ->placeholder('Sin objetivo'),
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
                                // Guión asistido: recordatorio de las creencias que la pieza debería tratar.
                                TextEntry::make('script_beliefs_reminder')
                                    ->label('💡 Mitos/verdades a tratar en el guión')
                                    ->state(fn (Get $get): array => static::ideaBeliefs($get))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->placeholder('Elige una idea ganadora para ver qué reforzar/desmentir.')
                                    ->columnSpanFull(),
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
                            ->columns(2)
                            ->schema([
                                TextInput::make('url')
                                    ->label('URL publicada')
                                    ->url()
                                    ->placeholder('https://...'),
                                DatePicker::make('published_at')
                                    ->label('Fecha de publicación')
                                    ->placeholder('Sin publicar'),
                            ]),
                    ]),

                // Panel lateral reactivo: cascada Idea → Preguntas → Mitos/Verdades.
                Section::make('Contexto de la idea')
                    ->description('Lo que esta pieza debería responder y reforzar.')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('context_idea')
                            ->label('Idea ganadora')
                            ->state(fn (Get $get): ?string => static::selectedIdea($get)?->title)
                            ->placeholder('Elige una idea para ver su contexto (o déjala suelta).'),
                        TextEntry::make('context_concept')
                            ->label('Concepto')
                            ->state(fn (Get $get): ?string => static::selectedIdea($get)?->concept)
                            ->placeholder('—'),
                        TextEntry::make('context_questions')
                            ->label('Preguntas que responde')
                            ->state(fn (Get $get): array => static::selectedIdea($get)?->questions->pluck('body')->all() ?? [])
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('—'),
                        TextEntry::make('context_beliefs')
                            ->label('Mitos y verdades a tratar')
                            ->state(fn (Get $get): array => static::ideaBeliefs($get))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('—'),
                    ]),
            ]);
    }

    private static function selectedIdea(Get $get): ?WinningIdea
    {
        $id = $get('winning_idea_id');

        if (blank($id)) {
            return null;
        }

        return WinningIdea::query()
            ->whereBelongsTo(Filament::getTenant())
            ->with('questions.beliefs')
            ->whereKey($id)
            ->first();
    }

    /**
     * @return array<int, string>
     */
    private static function ideaBeliefs(Get $get): array
    {
        $idea = static::selectedIdea($get);

        if ($idea === null) {
            return [];
        }

        return $idea->derivedBeliefs()
            ->map(fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement)
            ->all();
    }
}
