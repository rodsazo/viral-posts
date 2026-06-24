<?php

namespace App\Filament\Resources\WinningIdeas\Schemas;

use App\Enums\ViralMechanism;
use App\Models\Belief;
use App\Models\Question;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
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
                        // El Seguidor Ideal es el centro: la idea se dirige a un seguidor.
                        Select::make('ideal_follower_id')
                            ->label('Seguidor ideal')
                            ->relationship('idealFollower', 'name', $scopeToTenant)
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->helperText('De este seguidor salen sus preguntas, mitos/verdades y dolores.'),
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Select::make('viral_mechanism')
                            ->label('Mecanismo de viralidad')
                            ->options(ViralMechanism::class)
                            ->native(false)
                            ->placeholder('Sin definir'),
                        Repeater::make('example_urls')
                            ->label('Ejemplos reales (URLs)')
                            ->helperText('Posts virales de otros creadores basados en una idea similar (Instagram, TikTok…). Con al menos uno, la idea queda "Validada".')
                            ->simple(
                                TextInput::make('url')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://instagram.com/p/...'),
                            )
                            ->addActionLabel('Añadir ejemplo')
                            ->reorderable(false)
                            ->defaultItems(0)
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
                            // Preguntas acotadas al seguidor elegido.
                            ->relationship('questions', 'body', fn (Builder $query, Get $get) => $query
                                ->whereBelongsTo(Filament::getTenant())
                                ->when($get('ideal_follower_id'), fn (Builder $q, $fid) => $q->where('ideal_follower_id', $fid)))
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->disabled(fn (Get $get): bool => blank($get('ideal_follower_id')))
                            ->helperText('Solo se listan las preguntas del seguidor elegido.')
                            ->columnSpanFull(),
                    ]),

                // Panel lateral reactivo: contexto del seguidor.
                Section::make('Contexto del seguidor')
                    ->description('Mitos/verdades y dolores del seguidor elegido.')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('context_questions')
                            ->label('Preguntas elegidas')
                            ->state(fn (Get $get): array => static::selectedQuestions($get)->pluck('body')->all())
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Aún no has elegido preguntas.'),
                        TextEntry::make('context_beliefs')
                            ->label('Mitos y verdades del seguidor')
                            ->state(fn (Get $get): array => static::followerBeliefs($get))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('El seguidor aún no tiene mitos/verdades.'),
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
            ->whereKey($ids)
            ->get();
    }

    /**
     * Mitos/verdades del seguidor elegido (el seguidor es el centro).
     *
     * @return array<int, string>
     */
    private static function followerBeliefs(Get $get): array
    {
        $followerId = $get('ideal_follower_id');

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
