<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    // Sin borrado de usuarios desde la UI (evita orfandad de marcas).
    protected function getHeaderActions(): array
    {
        return [];
    }
}
