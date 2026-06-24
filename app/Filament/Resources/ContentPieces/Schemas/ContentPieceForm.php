<?php

namespace App\Filament\Resources\ContentPieces\Schemas;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentRating;
use App\Enums\ContentStatus;
use App\Models\Belief;
use App\Models\WinningIdea;
use App\Support\LinkPreview;
use App\Support\Rum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                                Select::make('ideal_follower_id')
                                    ->label('Seguidor ideal')
                                    ->relationship('idealFollower', 'name', $scopeToTenant)
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->placeholder('Sin seguidor')
                                    ->helperText('De él salen los mitos/verdades a tratar (suele coincidir con el de la idea).')
                                    ->columnSpanFull(),
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
                                    ->state(fn (Get $get): array => static::followerBeliefs($get))
                                    ->listWithLineBreaks()
                                    ->bulleted()
                                    ->placeholder('Elige un seguidor ideal para ver qué reforzar/desmentir.')
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

                        Section::make('Evaluación RUM')
                            ->description('Relevancia Única de Mercado: busca un RUM alto para más chance de viralidad.')
                            ->columns(2)
                            ->schema([
                                ...array_map(
                                    fn (string $key) => Select::make("rum_factors.{$key}")
                                        ->label(Rum::FACTORS[$key]['label'])
                                        ->options(Rum::optionsFor($key))
                                        ->helperText(Rum::FACTORS[$key]['help'])
                                        ->native(false)
                                        ->live()
                                        ->placeholder('Sin evaluar'),
                                    array_keys(Rum::FACTORS),
                                ),
                                TextEntry::make('rum_display')
                                    ->label('RUM')
                                    ->state(fn (Get $get): ?float => Rum::compute($get('rum_factors')))
                                    ->badge()
                                    ->size('lg')
                                    ->color(fn (?float $state): string => Rum::color($state))
                                    ->formatStateUsing(fn (?float $state): string => $state !== null ? number_format($state, 1) : '—')
                                    ->placeholder('Sin evaluar')
                                    ->columnSpanFull(),
                            ]),

                        Section::make('Publicación')
                            ->columns(2)
                            ->schema([
                                TextInput::make('url')
                                    ->label('URL publicada')
                                    ->url()
                                    ->maxLength(2048)
                                    ->placeholder('https://...')
                                    ->suffixAction(
                                        Action::make('fetchPreview')
                                            ->label('Obtener vista previa')
                                            ->icon('heroicon-m-photo')
                                            ->action(function (Get $get, Set $set): void {
                                                $image = app(LinkPreview::class)->imageFor($get('url'));

                                                if ($image !== null) {
                                                    $set('preview_image_url', $image);
                                                    Notification::make()->title('Vista previa obtenida')->success()->send();

                                                    return;
                                                }

                                                Notification::make()
                                                    ->title('No se pudo obtener la imagen automáticamente')
                                                    ->body('Pega la URL de la imagen a mano (Instagram suele requerirlo).')
                                                    ->warning()
                                                    ->send();
                                            }),
                                    ),
                                DatePicker::make('published_at')
                                    ->label('Fecha de publicación')
                                    ->placeholder('Sin publicar'),
                                TextInput::make('preview_image_url')
                                    ->label('URL de imagen de previsualización')
                                    ->url()
                                    ->maxLength(2048)
                                    ->prefixIcon('heroicon-m-photo')
                                    ->helperText('Se rellena con "Obtener vista previa", o pégala manualmente.')
                                    ->columnSpanFull(),
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
                            ->state(fn (Get $get): array => static::followerBeliefs($get))
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
            ->with('questions')
            ->whereKey($id)
            ->first();
    }

    /**
     * Mitos/verdades a tratar: del seguidor elegido (o, en su defecto, del seguidor
     * de la idea). El seguidor es el centro.
     *
     * @return array<int, string>
     */
    private static function followerBeliefs(Get $get): array
    {
        $followerId = $get('ideal_follower_id') ?: static::selectedIdea($get)?->ideal_follower_id;

        if (blank($followerId)) {
            return [];
        }

        return Belief::query()
            ->whereBelongsTo(Filament::getTenant())
            ->where('ideal_follower_id', $followerId)
            ->orderBy('statement')
            ->get()
            ->map(fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement)
            ->all();
    }
}
