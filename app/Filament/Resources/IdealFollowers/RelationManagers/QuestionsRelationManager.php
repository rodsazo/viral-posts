<?php

namespace App\Filament\Resources\IdealFollowers\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Preguntas';

    public function form(Schema $schema): Schema
    {
        $scopeToTenant = fn (Builder $query) => $query->whereBelongsTo(Filament::getTenant());

        return $schema
            ->components([
                Textarea::make('body')
                    ->label('Pregunta')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Select::make('category_id')
                    ->label('Categoría')
                    ->relationship('category', 'name', $scopeToTenant)
                    ->searchable()
                    ->preload()
                    ->placeholder('Sin categoría'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('body')
            ->emptyStateHeading('Este seguidor aún no tiene preguntas')
            ->emptyStateDescription('Registra sus dudas para inspirar contenido.')
            ->emptyStateIcon('heroicon-o-question-mark-circle')
            ->columns([
                TextColumn::make('body')
                    ->label('Pregunta')
                    ->wrap()
                    ->limit(80)
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label('Categoría')
                    ->badge()
                    ->color(fn ($record) => $record->category?->color ?? 'gray')
                    ->placeholder('Sin categoría'),
                TextColumn::make('beliefs_count')
                    ->label('Mitos/Verdades')
                    ->counts('beliefs')
                    ->badge(),
            ])
            ->headerActions([
                CreateAction::make()
                    // account_id no está en el formulario: lo heredamos del seguidor (misma marca).
                    ->mutateDataUsing(function (array $data): array {
                        $data['account_id'] = $this->getOwnerRecord()->account_id;

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
