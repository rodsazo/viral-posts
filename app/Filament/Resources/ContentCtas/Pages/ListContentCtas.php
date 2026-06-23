<?php

namespace App\Filament\Resources\ContentCtas\Pages;

use App\Filament\Resources\ContentCtas\ContentCtaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentCtas extends ListRecords
{
    protected static string $resource = ContentCtaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
