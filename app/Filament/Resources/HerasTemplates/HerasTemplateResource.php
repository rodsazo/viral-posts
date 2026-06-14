<?php

namespace App\Filament\Resources\HerasTemplates;

use App\Filament\Resources\HerasTemplates\Pages\CreateHerasTemplate;
use App\Filament\Resources\HerasTemplates\Pages\EditHerasTemplate;
use App\Filament\Resources\HerasTemplates\Pages\ListHerasTemplates;
use App\Filament\Resources\HerasTemplates\Schemas\HerasTemplateForm;
use App\Filament\Resources\HerasTemplates\Tables\HerasTemplatesTable;
use App\Models\HerasTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HerasTemplateResource extends Resource
{
    protected static ?string $model = HerasTemplate::class;

    // Catálogo global: NO se escopa a la marca; las 30 plantillas son compartidas.
    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Referencia';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Plantillas Heras';

    protected static ?string $modelLabel = 'plantilla Heras';

    protected static ?string $pluralModelLabel = 'plantillas Heras';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'structure', 'viral_mechanism'];
    }

    public static function form(Schema $schema): Schema
    {
        return HerasTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HerasTemplatesTable::configure($table);
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
            'index' => ListHerasTemplates::route('/'),
            'create' => CreateHerasTemplate::route('/create'),
            'edit' => EditHerasTemplate::route('/{record}/edit'),
        ];
    }
}
