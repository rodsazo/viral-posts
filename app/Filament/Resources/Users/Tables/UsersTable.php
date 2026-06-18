<?php

namespace App\Filament\Resources\Users\Tables;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean(),
                IconColumn::make('is_super_admin')
                    ->label('Super admin')
                    ->boolean(),
                TextColumn::make('accounts.name')
                    ->label('Marcas')
                    ->badge()
                    ->color('gray')
                    ->placeholder('— sin marcas —'),
                TextColumn::make('created_at')
                    ->label('Alta')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (User $record): string => $record->is_active ? 'Desactivar' : 'Activar')
                    ->icon(fn (User $record): string => $record->is_active ? 'heroicon-m-no-symbol' : 'heroicon-m-check-circle')
                    ->color(fn (User $record): string => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    // No puedes desactivarte a ti mismo.
                    ->visible(fn (User $record): bool => Filament::auth()->id() !== $record->getKey())
                    ->action(function (User $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Usuario activado' : 'Usuario desactivado')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }
}
