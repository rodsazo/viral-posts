<?php

namespace App\Filament\Resources\ContentPieces;

use App\Filament\Resources\ContentPieces\Pages\CreateContentPiece;
use App\Filament\Resources\ContentPieces\Pages\EditContentPiece;
use App\Filament\Resources\ContentPieces\Pages\ListContentPieces;
use App\Filament\Resources\ContentPieces\Pages\ViewContentPiece;
use App\Filament\Resources\ContentPieces\Schemas\ContentPieceForm;
use App\Filament\Resources\ContentPieces\Schemas\ContentPieceInfolist;
use App\Filament\Resources\ContentPieces\Tables\ContentPiecesTable;
use App\Models\ContentPiece;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ContentPieceResource extends Resource
{
    protected static ?string $model = ContentPiece::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static ?int $navigationSort = 6;

    protected static ?string $modelLabel = 'pieza de contenido';

    protected static ?string $pluralModelLabel = 'piezas de contenido';

    protected static ?string $navigationLabel = 'Piezas de contenido';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'hook', 'cta'];
    }

    public static function form(Schema $schema): Schema
    {
        return ContentPieceForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ContentPieceInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentPiecesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentPieces::route('/'),
            'create' => CreateContentPiece::route('/create'),
            'view' => ViewContentPiece::route('/{record}'),
            'edit' => EditContentPiece::route('/{record}/edit'),
        ];
    }
}
