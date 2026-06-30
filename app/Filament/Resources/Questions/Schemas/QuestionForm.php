<?php

namespace App\Filament\Resources\Questions\Schemas;

use App\Models\IdealFollower;
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
use Illuminate\Database\Eloquent\Model;

class QuestionForm
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
                            ->columns(2)
                            ->schema([
                                Select::make('ideal_follower_id')
                                    ->label('Seguidor ideal')
                                    ->relationship('idealFollower', 'name', $scopeToTenant)
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required(),
                                Select::make('category_id')
                                    ->label('Categoría')
                                    ->relationship('category', 'name', $scopeToTenant)
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Sin categoría'),
                                Textarea::make('body')
                                    ->label('Pregunta')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),
                                Textarea::make('notes')
                                    ->label('Notas internas')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                // Panel lateral reactivo: contexto del seguidor elegido.
                Section::make('Contexto del seguidor')
                    ->description('Evita duplicar preguntas que ya tienes.')
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('context_follower_desc')
                            ->label('Descripción del seguidor')
                            ->state(fn (Get $get): ?string => static::follower($get)?->description)
                            ->placeholder('Elige un seguidor ideal.'),
                        TextEntry::make('context_existing_questions')
                            ->label('Preguntas que ya tienes para este seguidor')
                            ->state(fn (Get $get, ?Model $record): array => static::existingQuestions($get, $record))
                            ->listWithLineBreaks()
                            ->bulleted()
                            ->placeholder('Ninguna todavía.'),
                    ]),
            ]);
    }

    private static function follower(Get $get): ?IdealFollower
    {
        $id = $get('ideal_follower_id');

        if (blank($id)) {
            return null;
        }

        return IdealFollower::query()
            ->whereBelongsTo(Filament::getTenant())
            ->whereKey($id)
            ->first();
    }

    /**
     * @return array<int, string>
     */
    private static function existingQuestions(Get $get, ?Model $record): array
    {
        $id = $get('ideal_follower_id');

        if (blank($id)) {
            return [];
        }

        return Question::query()
            ->whereBelongsTo(Filament::getTenant())
            ->where('ideal_follower_id', $id)
            ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
            ->pluck('body')
            ->all();
    }
}
