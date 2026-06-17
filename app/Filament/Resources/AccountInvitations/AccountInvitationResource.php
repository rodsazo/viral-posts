<?php

namespace App\Filament\Resources\AccountInvitations;

use App\Filament\Resources\AccountInvitations\Pages\CreateAccountInvitation;
use App\Filament\Resources\AccountInvitations\Pages\ListAccountInvitations;
use App\Filament\Resources\AccountInvitations\Schemas\AccountInvitationForm;
use App\Filament\Resources\AccountInvitations\Tables\AccountInvitationsTable;
use App\Models\AccountInvitation;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AccountInvitationResource extends Resource
{
    protected static ?string $model = AccountInvitation::class;

    // El nombre de la relación en Account es 'invitations' (Filament adivinaría 'accountInvitations').
    protected static ?string $tenantRelationshipName = 'invitations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Equipo';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Invitaciones';

    protected static ?string $modelLabel = 'invitación';

    protected static ?string $pluralModelLabel = 'invitaciones';

    /** Solo los administradores de la marca activa gestionan invitaciones. */
    public static function isAdmin(): bool
    {
        $user = Filament::auth()->user();
        $tenant = Filament::getTenant();

        return $user !== null && $tenant !== null && $user->isAdminOf($tenant);
    }

    public static function canViewAny(): bool
    {
        return static::isAdmin();
    }

    public static function canCreate(): bool
    {
        return static::isAdmin();
    }

    public static function canDelete($record): bool
    {
        return static::isAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::isAdmin();
    }

    public static function form(Schema $schema): Schema
    {
        return AccountInvitationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AccountInvitationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccountInvitations::route('/'),
            'create' => CreateAccountInvitation::route('/create'),
        ];
    }
}
