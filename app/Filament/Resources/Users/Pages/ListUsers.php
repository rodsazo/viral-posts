<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    // Sin "crear": los usuarios entran por invitación o por consola.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
