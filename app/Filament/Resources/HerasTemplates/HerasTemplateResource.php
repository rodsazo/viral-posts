<?php

namespace App\Filament\Resources\HerasTemplates;

use App\Filament\Concerns\RestrictsMutationToSuperAdmins;
use App\Filament\Resources\HerasTemplates\Pages\CreateHerasTemplate;
use App\Filament\Resources\HerasTemplates\Pages\EditHerasTemplate;
use App\Filament\Resources\HerasTemplates\Pages\ListHerasTemplates;
use App\Filament\Resources\HerasTemplates\Pages\ViewHerasTemplate;
use App\Filament\Resources\HerasTemplates\Schemas\HerasTemplateForm;
use App\Filament\Resources\HerasTemplates\Schemas\HerasTemplateInfolist;
use App\Filament\Resources\HerasTemplates\Tables\HerasTemplatesTable;
use App\Models\HerasTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class HerasTemplateResource extends Resource
{
    use RestrictsMutationToSuperAdmins;

    protected static ?string $model = HerasTemplate::class;

    // Catálogo global: NO se escopa a la marca; las 30 plantillas son compartidas.
    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Referencia';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Ideas ganadoras';

    protected static ?string $modelLabel = 'idea de referencia';

    protected static ?string $pluralModelLabel = 'ideas de referencia';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'structure', 'viral_mechanism'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Referente' => $record->viralReferent?->name ?? '—',
            'Nicho' => $record->viralReferent?->niche?->name ?? '—',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return HerasTemplateForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return HerasTemplateInfolist::configure($schema);
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
            'view' => ViewHerasTemplate::route('/{record}'),
            'edit' => EditHerasTemplate::route('/{record}/edit'),
        ];
    }
}
