<?php

namespace App\Filament\Resources\HookTemplates;

use App\Filament\Concerns\RestrictsMutationToSuperAdmins;
use App\Filament\Resources\HookTemplates\Pages\CreateHookTemplate;
use App\Filament\Resources\HookTemplates\Pages\EditHookTemplate;
use App\Filament\Resources\HookTemplates\Pages\ListHookTemplates;
use App\Filament\Resources\HookTemplates\Schemas\HookTemplateForm;
use App\Filament\Resources\HookTemplates\Tables\HookTemplatesTable;
use App\Models\HookTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class HookTemplateResource extends Resource
{
    use RestrictsMutationToSuperAdmins;

    protected static ?string $model = HookTemplate::class;

    // Catálogo global: NO se escopa a la marca (como HerasTemplate). Solo se edita en el admin.
    protected static bool $isScopedToTenant = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static string|UnitEnum|null $navigationGroup = 'Referencia';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Ganchos (plantillas)';

    protected static ?string $modelLabel = 'plantilla de gancho';

    protected static ?string $pluralModelLabel = 'plantillas de gancho';

    protected static ?string $recordTitleAttribute = 'name';

    /** El admin gestiona solo el catálogo GLOBAL de referencia; los de marca van en el Estudio. */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('account_id');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'objective', 'notes', 'example_generic'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Referente' => $record->viralReferent?->name ?? '—',
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return HookTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return HookTemplatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHookTemplates::route('/'),
            'create' => CreateHookTemplate::route('/create'),
            'edit' => EditHookTemplate::route('/{record}/edit'),
        ];
    }
}
