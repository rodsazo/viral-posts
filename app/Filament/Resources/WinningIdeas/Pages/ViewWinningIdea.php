<?php

namespace App\Filament\Resources\WinningIdeas\Pages;

use App\Filament\Resources\WinningIdeas\WinningIdeaResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewWinningIdea extends ViewRecord
{
    protected static string $resource = WinningIdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
