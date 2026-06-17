<?php

namespace App\Filament\Resources\ContentPieces;

use App\Enums\ContentStatus;
use App\Filament\Concerns\RestrictsDeletionToAdmins;
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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ContentPieceResource extends Resource
{
    use RestrictsDeletionToAdmins;

    protected static ?string $model = ContentPiece::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFilm;

    protected static string|UnitEnum|null $navigationGroup = 'Producción';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'pieza de contenido';

    protected static ?string $pluralModelLabel = 'piezas de contenido';

    protected static ?string $navigationLabel = 'Piezas de contenido';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'hook', 'cta'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Estado' => $record->status?->getLabel(),
            'Idea' => $record->winningIdea?->title ?? 'Pieza suelta',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('winningIdea');
    }

    /** Piezas aún en producción (sin publicar): el pipeline activo de un vistazo. */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getEloquentQuery()
            ->where('status', '!=', ContentStatus::Publicada->value)
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Piezas en producción (sin publicar)';
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
