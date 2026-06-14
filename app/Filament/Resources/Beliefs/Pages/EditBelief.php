<?php

namespace App\Filament\Resources\Beliefs\Pages;

use App\Filament\Resources\Beliefs\BeliefResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBelief extends EditRecord
{
    protected static string $resource = BeliefResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
