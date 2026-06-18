<?php

namespace App\Filament\Resources\WinningIdeas\Schemas;

use App\Enums\ViralMechanism;
use App\Filament\Actions\SuggestionAction;
use App\Models\Belief;
use App\Models\Question;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\IdeaContext;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class WinningIdeaForm
{
    public static function configure(Schema $schema): Schema
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $schema
            ->columns(3)
            ->components([
                Section::make()
                    ->columnSpan(2)
                    ->schema([
                        Actions::make([
                            SuggestionAction::make('suggestIdeas', fn (Get $get, ?string $brief): array => app(ContentAssistant::class)
                                ->suggestIdeas(static::ideaContext($get, $brief)))
                                ->label('Sugerir ideas (IA)')
                                ->visible(fn (): bool => app(ContentAssistant::class)->isConfigured()),
                        ]),
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Select::make('viral_mechanism')
                            ->label('Mecanismo de viralidad')
                            ->options(ViralMechanism::class)
                            ->native(false)
                            ->placeholder('Sin definir'),
                        TextInput::make('reference_url')
                            ->label('Referencia viral')
                            ->url()
                            ->maxLength(2048)
                            ->prefixIcon('heroicon-m-link')
                            ->placeholder('https://instagram.com/p/...')
                            ->helperText('URL del post viral de referencia (Instagram, TikTok, Facebook, etc.).')
                            ->columnSpanFull(),
                        Select::make('heras_template_id')
                            ->label('Plantilla Heras')
                            ->relationship('herasTemplate', 'name') // global, sin escopar a la marca
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->display_name)
                            ->searchable()
                            ->preload()
                            ->placeholder('Sin plantilla')
                            ->columnSpanFull(),
                        Textarea::make('concept')
                            ->label('Concepto')
                            ->required()
                            ->rows(5)
                            ->columnSpanFull(),
                        Select::make('questions')
                            ->label('Preguntas que resuelve')
                            ->relationship('questions', 'body', $scopeToTenant)
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->createOptionForm([
                                Select::make('ideal_follower_id')
                                    ->label('Seguidor ideal')
                                    ->relationship('idealFollower', 'name', $scopeToTenant)
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                Select::make('category_id')
                                    ->label('Categoría')
                                    ->relationship('category', 'name', $scopeToTenant)
                                    ->searchable()
                                    ->preload(),
                                Textarea::make('body')
                                    ->label('Pregunta')
                                    ->required()
                                    ->rows(2),
                            ])
                            ->createOptionUsing(fn (array $data): int => Question::create([
                                ...$data,
                                'account_id' => Filament::getTenant()->getKey(),
                            ])->getKey())
                            ->helperText('Elige preguntas y mira a la derecha los mitos y verdades en juego.')
                            ->columnSpanFull(),
                    ]),

                // Panel lateral reactivo: contexto de las preguntas elegidas.
                Section::make('Contexto')
                    ->description('Se actualiza según las preguntas que elijas.')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('context_questions')
                            ->label('Preguntas elegidas')
                            ->state(fn (Get $get): array => static::selectedQuestions($get)->pluck('body')->all())
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Aún no has elegido preguntas.'),
                        TextEntry::make('context_beliefs')
                            ->label('Mitos y verdades relacionados')
                            ->state(fn (Get $get): array => static::relatedBeliefs($get))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Las preguntas elegidas aún no tienen mitos/verdades asociados.'),
                        // Detección de huecos: preguntas elegidas sin ninguna creencia.
                        TextEntry::make('context_gap')
                            ->label('⚠️ Preguntas sin mitos/verdades')
                            ->color('warning')
                            ->state(fn (Get $get): array => static::questionsWithoutBeliefs($get))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Todas las preguntas elegidas tienen al menos un mito/verdad.'),
                    ]),
            ]);
    }

    /**
     * Contexto para la IA: preguntas elegidas + sus mitos/verdades + el borrador actual.
     */
    private static function ideaContext(Get $get, ?string $brief = null): IdeaContext
    {
        return new IdeaContext(
            questions: static::selectedQuestions($get)->pluck('body')->all(),
            beliefs: static::relatedBeliefs($get),
            draftTitle: $get('title'),
            draftConcept: $get('concept'),
            extra: $brief,
        );
    }

    /**
     * @return Collection<int, Question>
     */
    private static function selectedQuestions(Get $get): Collection
    {
        $ids = array_filter((array) $get('questions'));

        if (empty($ids)) {
            return collect();
        }

        return Question::query()
            ->whereBelongsTo(Filament::getTenant())
            ->with('beliefs')
            ->whereKey($ids)
            ->get();
    }

    /**
     * Mitos/verdades únicos de las preguntas elegidas (VISIBILIDAD MULTI-SALTO en vivo).
     *
     * @return array<int, string>
     */
    private static function relatedBeliefs(Get $get): array
    {
        return static::selectedQuestions($get)
            ->flatMap->beliefs
            ->unique('id')
            ->map(fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement)
            ->values()
            ->all();
    }

    /**
     * Detección de huecos: preguntas elegidas que aún no tienen ninguna creencia.
     *
     * @return array<int, string>
     */
    private static function questionsWithoutBeliefs(Get $get): array
    {
        return static::selectedQuestions($get)
            ->filter(fn (Question $question): bool => $question->beliefs->isEmpty())
            ->pluck('body')
            ->all();
    }
}
