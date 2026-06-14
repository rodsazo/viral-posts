<?php

namespace App\Filament\Resources\IdealFollowers\Pages;

use App\Filament\Resources\IdealFollowers\IdealFollowerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditIdealFollower extends EditRecord
{
    protected static string $resource = IdealFollowerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
