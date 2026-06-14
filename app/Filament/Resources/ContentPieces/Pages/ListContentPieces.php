<?php

namespace App\Filament\Resources\ContentPieces\Pages;

use App\Filament\Resources\ContentPieces\ContentPieceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContentPieces extends ListRecords
{
    protected static string $resource = ContentPieceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
