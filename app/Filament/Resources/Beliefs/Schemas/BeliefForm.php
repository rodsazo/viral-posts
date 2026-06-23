<?php

namespace App\Filament\Resources\Beliefs\Schemas;

use App\Enums\BeliefType;
use App\Models\Question;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BeliefForm
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
                        Section::make()
                            ->schema([
                                Select::make('ideal_follower_id')
                                    ->label('Seguidor ideal')
                                    ->relationship('idealFollower', 'name', $scopeToTenant)
                                    ->searchable()
                                    ->placeholder('Sin seguidor (creencia de marca)')
                                    ->helperText('Opcional: mito/verdad propio de un seguidor ideal.'),
                                Select::make('type')
                                    ->label('Tipo')
                                    ->options(BeliefType::class)
                                    ->required()
                                    ->native(false),
                                Textarea::make('statement')
                                    ->label('Afirmación')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Textarea::make('stance')
                                    ->label('Postura de la marca')
                                    ->helperText('Qué/por qué se desmiente o impulsa.')
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Select::make('questions')
                                    ->label('Preguntas relacionadas')
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
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // Panel lateral reactivo: alcance de las preguntas elegidas.
                Section::make('Alcance')
                    ->description('A qué audiencia llega esta creencia, vía sus preguntas.')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('context_followers')
                            ->label('Seguidores ideales alcanzados')
                            ->state(fn (Get $get): array => static::selectedQuestions($get)
                                ->map(fn (Question $q) => $q->idealFollower?->name)
                                ->filter()
                                ->unique()
                                ->values()
                                ->all())
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Elige preguntas para ver su alcance.'),
                        TextEntry::make('context_categories')
                            ->label('Categorías')
                            ->state(fn (Get $get): array => static::selectedQuestions($get)
                                ->map(fn (Question $q) => $q->category?->name)
                                ->filter()
                                ->unique()
                                ->values()
                                ->all())
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('—'),
                    ]),
            ]);
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
            ->with(['idealFollower', 'category'])
            ->whereKey($ids)
            ->get();
    }
}
