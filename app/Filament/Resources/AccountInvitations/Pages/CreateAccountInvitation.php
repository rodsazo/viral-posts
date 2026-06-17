<?php

namespace App\Filament\Resources\AccountInvitations\Pages;

use App\Filament\Resources\AccountInvitations\AccountInvitationResource;
use App\Mail\AccountInvitationMail;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Mail;

class CreateAccountInvitation extends CreateRecord
{
    protected static string $resource = AccountInvitationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['account_id'] ??= Filament::getTenant()?->getKey();

        return $data;
    }

    protected function afterCreate(): void
    {
        Mail::to($this->record->email)->send(new AccountInvitationMail($this->record));

        Notification::make()
            ->title("Invitación enviada a {$this->record->email}")
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
