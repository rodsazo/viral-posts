<?php

namespace App\Filament\Resources\Pains\Pages;

use App\Filament\Resources\Pains\PainResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPain extends EditRecord
{
    protected static string $resource = PainResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
