<?php

namespace App\Filament\Pages;

use App\Enums\BeliefType;
use App\Models\Belief;
use App\Models\IdealFollower;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BulkBeliefs extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|UnitEnum|null $navigationGroup = 'Audiencia';

    protected static ?int $navigationSort = 6;

    protected static ?string $navigationLabel = 'Creencias en lote';

    protected static ?string $title = 'Crear creencias en lote';

    protected string $view = 'filament.pages.bulk-beliefs';

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
                // El seguidor es el centro: todas las creencias de este lote se crean para él.
                Select::make('ideal_follower_id')
                    ->label('Seguidor ideal')
                    ->options(fn (): array => IdealFollower::query()
                        ->whereBelongsTo(Filament::getTenant())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->native(false)
                    ->required()
                    ->helperText('Las creencias creadas pertenecerán a este seguidor.'),

                Repeater::make('batches')
                    ->label('Grupos de creencias')
                    ->addActionLabel('Agregar grupo de creencias')
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->columns(1)
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Textarea::make('myths')
                                    ->label('🔴 Mitos a desmentir')
                                    ->rows(8)
                                    ->helperText('Una afirmación por línea.'),
                                Textarea::make('truths')
                                    ->label('🟢 Verdades a impulsar')
                                    ->rows(8)
                                    ->helperText('Una afirmación por línea.'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $accountId = Filament::getTenant()->getKey();
        $followerId = $state['ideal_follower_id'] ?? null;
        $created = 0;

        foreach ($state['batches'] ?? [] as $batch) {
            $byType = [
                BeliefType::Myth->value => $batch['myths'] ?? '',
                BeliefType::Truth->value => $batch['truths'] ?? '',
            ];

            foreach ($byType as $type => $text) {
                foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
                    $statement = trim($line);

                    if ($statement === '') {
                        continue;
                    }

                    Belief::create([
                        'account_id' => $accountId,
                        'ideal_follower_id' => $followerId,
                        'type' => BeliefType::from($type),
                        'statement' => $statement,
                    ]);

                    $created++;
                }
            }
        }

        if ($created === 0) {
            Notification::make()
                ->title('No se agregó ninguna creencia')
                ->body('Escribe al menos una línea en mitos o en verdades.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title($created.' '.($created === 1 ? 'creencia creada' : 'creencias creadas'))
            ->success()
            ->send();

        $this->form->fill(['ideal_follower_id' => $state['ideal_follower_id'] ?? null]);
    }
}
