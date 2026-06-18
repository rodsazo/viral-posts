<?php

namespace App\Filament\Resources\Accounts\RelationManagers;

use App\Enums\TeamRole;
use App\Models\Membership;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\AttachAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Miembros';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('pivot.role')
                    ->label('Rol')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => TeamRole::from($state)->getLabel())
                    ->color(fn (string $state): string => TeamRole::from($state)->getColor()),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Añadir miembro')
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Select::make('role')
                            ->label('Rol')
                            ->options(TeamRole::class)
                            ->default(TeamRole::Editor->value)
                            ->required(),
                    ]),
            ])
            ->recordActions([
                Action::make('toggleRole')
                    ->label(fn (User $record): string => $record->pivot->role === TeamRole::Admin->value ? 'Hacer editor' : 'Hacer admin')
                    ->icon('heroicon-m-arrows-right-left')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        $makingEditor = $record->pivot->role === TeamRole::Admin->value;

                        if ($makingEditor && $this->adminCount() <= 1) {
                            Notification::make()->title('No puedes quitar el último administrador')->danger()->send();

                            return;
                        }

                        $this->getOwnerRecord()->users()->updateExistingPivot($record->getKey(), [
                            'role' => $makingEditor ? TeamRole::Editor->value : TeamRole::Admin->value,
                        ]);

                        Notification::make()->title('Rol actualizado')->success()->send();
                    }),
                Action::make('remove')
                    ->label('Quitar')
                    ->icon('heroicon-m-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        if ($record->pivot->role === TeamRole::Admin->value && $this->adminCount() <= 1) {
                            Notification::make()->title('No puedes quitar el último administrador')->danger()->send();

                            return;
                        }

                        $this->getOwnerRecord()->users()->detach($record->getKey());
                        Notification::make()->title('Miembro quitado de la marca')->success()->send();
                    }),
            ]);
    }

    private function adminCount(): int
    {
        return Membership::query()
            ->where('account_id', $this->getOwnerRecord()->getKey())
            ->where('role', TeamRole::Admin->value)
            ->count();
    }
}
