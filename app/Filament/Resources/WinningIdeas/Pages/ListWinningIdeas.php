<?php

namespace App\Filament\Resources\WinningIdeas\Pages;

use App\Filament\Resources\WinningIdeas\WinningIdeaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWinningIdeas extends ListRecords
{
    protected static string $resource = WinningIdeaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
