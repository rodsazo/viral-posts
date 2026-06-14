<?php

namespace App\Filament\Resources\WinningIdeas\Pages;

use App\Filament\Resources\WinningIdeas\WinningIdeaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWinningIdea extends EditRecord
{
    protected static string $resource = WinningIdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
