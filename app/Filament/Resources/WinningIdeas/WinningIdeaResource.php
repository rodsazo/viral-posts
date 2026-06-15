<?php

namespace App\Filament\Resources\WinningIdeas;

use App\Filament\Resources\WinningIdeas\Pages\CreateWinningIdea;
use App\Filament\Resources\WinningIdeas\Pages\EditWinningIdea;
use App\Filament\Resources\WinningIdeas\Pages\ListWinningIdeas;
use App\Filament\Resources\WinningIdeas\Pages\ViewWinningIdea;
use App\Filament\Resources\WinningIdeas\Schemas\WinningIdeaForm;
use App\Filament\Resources\WinningIdeas\Schemas\WinningIdeaInfolist;
use App\Filament\Resources\WinningIdeas\Tables\WinningIdeasTable;
use App\Models\WinningIdea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class WinningIdeaResource extends Resource
{
    protected static ?string $model = WinningIdea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLightBulb;

    protected static string|UnitEnum|null $navigationGroup = 'Conocimiento';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'idea ganadora';

    protected static ?string $pluralModelLabel = 'ideas ganadoras';

    protected static ?string $navigationLabel = 'Ideas ganadoras';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'concept', 'viral_mechanism'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Mecanismo' => $record->viral_mechanism?->getLabel() ?? '—',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return WinningIdeaForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return WinningIdeaInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WinningIdeasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ContentPiecesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWinningIdeas::route('/'),
            'create' => CreateWinningIdea::route('/create'),
            'view' => ViewWinningIdea::route('/{record}'),
            'edit' => EditWinningIdea::route('/{record}/edit'),
        ];
    }
}
