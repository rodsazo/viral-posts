<?php

namespace App\Filament\Resources\Beliefs;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\Beliefs\Pages\CreateBelief;
use App\Filament\Resources\Beliefs\Pages\EditBelief;
use App\Filament\Resources\Beliefs\Pages\ListBeliefs;
use App\Filament\Resources\Beliefs\Schemas\BeliefForm;
use App\Filament\Resources\Beliefs\Tables\BeliefsTable;
use App\Models\Belief;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class BeliefResource extends Resource
{
    use RestrictsDeletionToAdmins;

    protected static ?string $model = Belief::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string|UnitEnum|null $navigationGroup = 'Audiencia';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Creencias';

    protected static ?string $modelLabel = 'creencia';

    protected static ?string $pluralModelLabel = 'creencias';

    protected static ?string $recordTitleAttribute = 'statement';

    public static function getGloballySearchableAttributes(): array
    {
        return ['statement', 'stance'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Tipo' => $record->type?->getLabel(),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return BeliefForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeliefsTable::configure($table);
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
            'index' => ListBeliefs::route('/'),
            'create' => CreateBelief::route('/create'),
            'edit' => EditBelief::route('/{record}/edit'),
        ];
    }
}
