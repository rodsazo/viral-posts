<?php

namespace App\Filament\Resources\AccountInvitations\Pages;

use App\Filament\Resources\AccountInvitations\AccountInvitationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAccountInvitations extends ListRecords
{
    protected static string $resource = AccountInvitationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
