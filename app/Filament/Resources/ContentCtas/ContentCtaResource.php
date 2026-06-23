<?php

namespace App\Filament\Resources\ContentCtas;

use App\Filament\Concerns\RestrictsDeletionToAdmins;
use App\Filament\Resources\ContentCtas\Pages\CreateContentCta;
use App\Filament\Resources\ContentCtas\Pages\EditContentCta;
use App\Filament\Resources\ContentCtas\Pages\ListContentCtas;
use App\Filament\Resources\ContentCtas\Schemas\ContentCtaForm;
use App\Filament\Resources\ContentCtas\Tables\ContentCtasTable;
use App\Models\ContentCta;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class ContentCtaResource extends Resource
{
    use RestrictsDeletionToAdmins;

    protected static ?string $model = ContentCta::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|UnitEnum|null $navigationGroup = 'Producción';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'CTAs';

    protected static ?string $modelLabel = 'CTA';

    protected static ?string $pluralModelLabel = 'CTAs';

    protected static ?string $recordTitleAttribute = 'text';

    public static function getGloballySearchableAttributes(): array
    {
        return ['text'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Categoría' => $record->category?->getLabel(),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return ContentCtaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ContentCtasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListContentCtas::route('/'),
            'create' => CreateContentCta::route('/create'),
            'edit' => EditContentCta::route('/{record}/edit'),
        ];
    }
}
