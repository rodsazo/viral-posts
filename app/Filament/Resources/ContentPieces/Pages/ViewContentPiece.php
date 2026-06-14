<?php

namespace App\Filament\Resources\ContentPieces\Pages;

use App\Filament\Resources\ContentPieces\ContentPieceResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewContentPiece extends ViewRecord
{
    protected static string $resource = ContentPieceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
