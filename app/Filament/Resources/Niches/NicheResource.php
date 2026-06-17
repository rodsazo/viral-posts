<?php

namespace App\Filament\Resources\Niches;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\Niches\Pages\CreateNiche;
use App\Filament\Resources\Niches\Pages\EditNiche;
use App\Filament\Resources\Niches\Pages\ListNiches;
use App\Filament\Resources\Niches\Schemas\NicheForm;
use App\Filament\Resources\Niches\Tables\NichesTable;
use App\Models\Niche;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class NicheResource extends Resource
{
    use RestrictsDeletionToAdmins;

    protected static ?string $model = Niche::class;

    // Catálogo global compartido por todas las marcas.
    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static string|UnitEnum|null $navigationGroup = 'Referencia';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Nichos';

    protected static ?string $modelLabel = 'nicho';

    protected static ?string $pluralModelLabel = 'nichos';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'description'];
    }

    public static function form(Schema $schema): Schema
    {
        return NicheForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NichesTable::configure($table);
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
            'index' => ListNiches::route('/'),
            'create' => CreateNiche::route('/create'),
            'edit' => EditNiche::route('/{record}/edit'),
        ];
    }
}
