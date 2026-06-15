<?php

namespace App\Filament\Resources\Questions\Pages;

use App\Filament\Pages\BulkQuestions;
use App\Filament\Resources\Questions\QuestionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListQuestions extends ListRecords
{
    protected static string $resource = QuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk')
                ->label('Crear en lote')
                ->icon('heroicon-o-queue-list')
                ->color('gray')
                ->url(fn (): string => BulkQuestions::getUrl()),
            CreateAction::make(),
        ];
    }
}
