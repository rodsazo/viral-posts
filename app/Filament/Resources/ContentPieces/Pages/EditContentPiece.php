<?php

namespace App\Filament\Resources\ContentPieces\Pages;

use App\Filament\Resources\ContentPieces\ContentPieceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditContentPiece extends EditRecord
{
    protected static string $resource = ContentPieceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
