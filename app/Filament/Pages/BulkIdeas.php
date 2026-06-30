<?php

namespace App\Filament\Pages;

use App\Enums\ViralMechanism;
use App\Models\WinningIdea;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BulkIdeas extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Producción';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Ideas en lote';

    protected static ?string $title = 'Crear ideas en lote';

    protected string $view = 'filament.pages.bulk-ideas';

    /** @var array<string, mixed>|null */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Repeater::make('ideas')
                    ->label('Ideas')
                    ->addActionLabel('Agregar idea')
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->columns(2)
                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255),
                        Select::make('viral_mechanism')
                            ->label('Mecanismo de viralidad')
                            ->options(ViralMechanism::class)
                            ->native(false)
                            ->placeholder('Sin definir'),
                        Textarea::make('concept')
                            ->label('Concepto / estructura')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $accountId = Filament::getTenant()->getKey();
        $created = 0;

        foreach ($state['ideas'] ?? [] as $row) {
            $title = trim((string) ($row['title'] ?? ''));
            $concept = trim((string) ($row['concept'] ?? ''));

            if ($title === '' || $concept === '') {
                continue;
            }

            WinningIdea::create([
                'account_id' => $accountId,
                'title' => $title,
                'concept' => $concept,
                'viral_mechanism' => $row['viral_mechanism'] ?? null,
            ]);

            $created++;
        }

        if ($created === 0) {
            Notification::make()
                ->title('No se creó ninguna idea')
                ->body('Completa al menos una idea con título y concepto.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title($created.' '.($created === 1 ? 'idea creada' : 'ideas creadas'))
            ->success()
            ->send();

        $this->form->fill();
    }
}
