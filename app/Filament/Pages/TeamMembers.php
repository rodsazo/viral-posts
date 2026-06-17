<?php

namespace App\Filament\Pages;

use App\Enums\TeamRole;
use App\Filament\Resources\AccountInvitations\AccountInvitationResource;
use App\Models\Membership;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class TeamMembers extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Equipo';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Miembros';

    protected static ?string $title = 'Miembros del equipo';

    protected string $view = 'filament.pages.team-members';

    public static function isAdmin(): bool
    {
        $user = Filament::auth()->user();
        $tenant = Filament::getTenant();

        return $user !== null && $tenant !== null && $user->isAdminOf($tenant);
    }

    public static function canAccess(): bool
    {
        return static::isAdmin();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::isAdmin();
    }

    private static function adminCount(): int
    {
        return Membership::query()
            ->where('account_id', Filament::getTenant()->getKey())
            ->where('role', TeamRole::Admin->value)
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Membership::query()
                ->where('account_id', Filament::getTenant()->getKey())
                ->with('user'))
            ->defaultSort('role')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Rol')
                    ->badge(),
            ])
            ->headerActions([
                Action::make('invite')
                    ->label('Invitar')
                    ->icon('heroicon-m-envelope')
                    ->url(fn (): string => AccountInvitationResource::getUrl('create')),
            ])
            ->recordActions([
                Action::make('toggleRole')
                    ->label(fn (Membership $record): string => $record->role === TeamRole::Admin ? 'Hacer editor' : 'Hacer admin')
                    ->icon('heroicon-m-arrows-right-left')
                    ->requiresConfirmation()
                    ->action(function (Membership $record): void {
                        $new = $record->role === TeamRole::Admin ? TeamRole::Editor : TeamRole::Admin;

                        if ($new === TeamRole::Editor && static::adminCount() <= 1) {
                            Notification::make()->title('No puedes quitar el último administrador')->danger()->send();

                            return;
                        }

                        $record->update(['role' => $new]);
                        Notification::make()->title('Rol actualizado')->success()->send();
                    }),
                Action::make('remove')
                    ->label('Quitar')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Membership $record): bool => $record->user_id !== Filament::auth()->id())
                    ->action(function (Membership $record): void {
                        if ($record->role === TeamRole::Admin && static::adminCount() <= 1) {
                            Notification::make()->title('No puedes quitar el último administrador')->danger()->send();

                            return;
                        }

                        $record->delete();
                        Notification::make()->title('Miembro quitado de la marca')->success()->send();
                    }),
            ]);
    }
}
