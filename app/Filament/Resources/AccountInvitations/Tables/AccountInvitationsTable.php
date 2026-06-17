<?php

namespace App\Filament\Resources\AccountInvitations\Tables;

use App\Mail\AccountInvitationMail;
use App\Models\AccountInvitation;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class AccountInvitationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                TextColumn::make('role')
                    ->label('Rol')
                    ->badge(),
                IconColumn::make('accepted_at')
                    ->label('Aceptada')
                    ->boolean()
                    ->state(fn (AccountInvitation $record): bool => $record->isAccepted()),
                TextColumn::make('acceptance_url')
                    ->label('Enlace')
                    ->state(fn (AccountInvitation $record): string => $record->acceptanceUrl())
                    ->copyable()
                    ->copyMessage('Enlace copiado')
                    ->limit(28)
                    ->toggleable(),
                TextColumn::make('expires_at')
                    ->label('Estado')
                    ->state(fn (AccountInvitation $record): string => $record->isAccepted()
                        ? 'Aceptada'
                        : ($record->isExpired() ? 'Caducada' : 'Pendiente'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Aceptada' => 'success',
                        'Caducada' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('created_at')
                    ->label('Enviada')
                    ->dateTime()
                    ->sortable(),
            ])
            ->emptyStateHeading('Sin invitaciones')
            ->emptyStateDescription('Invita a alguien con el botón de arriba.')
            ->emptyStateIcon('heroicon-o-envelope')
            ->recordActions([
                Action::make('resend')
                    ->label('Reenviar')
                    ->icon('heroicon-m-paper-airplane')
                    ->visible(fn (AccountInvitation $record): bool => ! $record->isAccepted())
                    ->action(function (AccountInvitation $record): void {
                        // Reenviar renueva la caducidad.
                        $record->update(['expires_at' => now()->addDays(AccountInvitation::EXPIRY_DAYS)]);

                        Mail::to($record->email)->send(new AccountInvitationMail($record));

                        Notification::make()->title('Invitación reenviada')->success()->send();
                    }),
                DeleteAction::make()
                    ->label('Revocar'),
            ]);
    }
}
