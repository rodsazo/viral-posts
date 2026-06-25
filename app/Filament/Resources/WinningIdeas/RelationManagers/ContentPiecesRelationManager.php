<?php

namespace App\Filament\Resources\WinningIdeas\RelationManagers;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentStatus;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContentPiecesRelationManager extends RelationManager
{
    protected static string $relationship = 'contentPieces';

    protected static ?string $title = 'Piezas de contenido';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título de trabajo')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('status')
                    ->label('Estado')
                    ->options(ContentStatus::class)
                    ->default(ContentStatus::Borrador->value)
                    ->required()
                    ->native(false),
                Select::make('objective')
                    ->label('Objetivo')
                    ->options(ContentObjective::class)
                    ->native(false)
                    ->placeholder('Sin objetivo'),
                Select::make('format')
                    ->label('Formato')
                    ->options(ContentFormat::class)
                    ->native(false)
                    ->placeholder('Sin formato'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->emptyStateHeading('Esta idea aún no tiene piezas')
            ->emptyStateDescription('Crea la primera pieza que nace de este concepto.')
            ->emptyStateIcon('heroicon-o-film')
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->wrap()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),
                TextColumn::make('objective')
                    ->label('Objetivo')
                    ->badge()
                    ->placeholder('—'),
                TextColumn::make('published_at')
                    ->label('Publicada')
                    ->date()
                    ->placeholder('—')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    // account_id no está en el formulario: lo heredamos de la idea (misma marca).
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
