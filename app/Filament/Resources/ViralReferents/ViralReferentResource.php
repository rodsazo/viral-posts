<?php

namespace App\Filament\Resources\ViralReferents;

use App\Filament\Concerns\RestrictsMutationToSuperAdmins;
use App\Filament\Resources\ViralReferents\Pages\CreateViralReferent;
use App\Filament\Resources\ViralReferents\Pages\EditViralReferent;
use App\Filament\Resources\ViralReferents\Pages\ListViralReferents;
use App\Filament\Resources\ViralReferents\Schemas\ViralReferentForm;
use App\Filament\Resources\ViralReferents\Tables\ViralReferentsTable;
use App\Models\ViralReferent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ViralReferentResource extends Resource
{
    use RestrictsMutationToSuperAdmins;

    protected static ?string $model = ViralReferent::class;

    // Catálogo global compartido por todas las marcas.
    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Referencia';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Referentes virales';

    protected static ?string $modelLabel = 'referente viral';

    protected static ?string $pluralModelLabel = 'referentes virales';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'notes'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Nicho' => $record->niche?->name ?? '—',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ViralReferentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ViralReferentsTable::configure($table);
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
            'index' => ListViralReferents::route('/'),
            'create' => CreateViralReferent::route('/create'),
            'edit' => EditViralReferent::route('/{record}/edit'),
        ];
    }
}
