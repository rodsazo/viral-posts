<?php

namespace App\Filament\Resources\IdealFollowers\Pages;

use App\Filament\Resources\IdealFollowers\IdealFollowerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIdealFollowers extends ListRecords
{
    protected static string $resource = IdealFollowerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
