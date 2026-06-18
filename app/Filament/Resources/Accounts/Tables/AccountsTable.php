<?php

namespace App\Filament\Resources\Accounts\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AccountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('Marca')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->badge()
                    ->color('gray')
                    ->searchable(),
                TextColumn::make('users_count')
                    ->label('Miembros')
                    ->counts('users')
                    ->badge(),
                TextColumn::make('content_pieces_count')
                    ->label('Piezas')
                    ->counts('contentPieces')
                    ->badge()
                    ->color('info'),
                IconColumn::make('is_active')
                    ->label('Activa')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                Action::make('toggleActive')
                    ->label(fn (Model $record): string => $record->is_active ? 'Suspender' : 'Reactivar')
                    ->icon(fn (Model $record): string => $record->is_active ? 'heroicon-m-pause-circle' : 'heroicon-m-play-circle')
                    ->color(fn (Model $record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(function (Model $record): void {
                        $record->update(['is_active' => ! $record->is_active]);

                        Notification::make()
                            ->title($record->is_active ? 'Marca reactivada' : 'Marca suspendida')
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
