<?php

namespace App\Filament\Resources\IdealFollowers;

use App\Filament\Resources\IdealFollowers\Pages\CreateIdealFollower;
use App\Filament\Resources\IdealFollowers\Pages\EditIdealFollower;
use App\Filament\Resources\IdealFollowers\Pages\ListIdealFollowers;
use App\Filament\Resources\IdealFollowers\Schemas\IdealFollowerForm;
use App\Filament\Resources\IdealFollowers\Tables\IdealFollowersTable;
use App\Models\IdealFollower;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class IdealFollowerResource extends Resource
{
    protected static ?string $model = IdealFollower::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Audiencia';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Seguidor Ideal';

    protected static ?string $modelLabel = 'seguidor ideal';

    protected static ?string $pluralModelLabel = 'seguidores ideales';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }

    public static function form(Schema $schema): Schema
    {
        return IdealFollowerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IdealFollowersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIdealFollowers::route('/'),
            'create' => CreateIdealFollower::route('/create'),
            'edit' => EditIdealFollower::route('/{record}/edit'),
        ];
    }
}
