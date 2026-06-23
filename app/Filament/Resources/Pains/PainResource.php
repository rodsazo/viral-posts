<?php

namespace App\Filament\Resources\Pains;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\Pains\Pages\CreatePain;
use App\Filament\Resources\Pains\Pages\EditPain;
use App\Filament\Resources\Pains\Pages\ListPains;
use App\Filament\Resources\Pains\Schemas\PainForm;
use App\Filament\Resources\Pains\Tables\PainsTable;
use App\Models\Pain;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class PainResource extends Resource
{
    use RestrictsDeletionToAdmins;

    protected static ?string $model = Pain::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|UnitEnum|null $navigationGroup = 'Audiencia';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Dolores';

    protected static ?string $modelLabel = 'dolor / problema / deseo';

    protected static ?string $pluralModelLabel = 'dolores, problemas y deseos';

    protected static ?string $recordTitleAttribute = 'body';

    public static function getGloballySearchableAttributes(): array
    {
        return ['body'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Tipo' => $record->type?->getLabel(),
            'Seguidor' => $record->idealFollower?->name ?? '—',
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with('idealFollower');
    }

    public static function form(Schema $schema): Schema
    {
        return PainForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PainsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPains::route('/'),
            'create' => CreatePain::route('/create'),
            'edit' => EditPain::route('/{record}/edit'),
        ];
    }
}
