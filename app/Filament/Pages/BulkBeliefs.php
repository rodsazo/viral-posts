<?php

namespace App\Filament\Pages;

use App\Enums\BeliefType;
use App\Models\Belief;
use App\Models\Question;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BulkBeliefs extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 4;

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
                Grid::make(2)
                    ->schema([
                        Textarea::make('myths')
                            ->label('🔴 Mitos a desmentir')
                            ->rows(10)
                            ->helperText('Una afirmación por línea. Se guardan como mitos.'),
                        Textarea::make('truths')
                            ->label('🟢 Verdades a impulsar')
                            ->rows(10)
                            ->helperText('Una afirmación por línea. Se guardan como verdades.'),
                    ]),
                Select::make('question_ids')
                    ->label('Relacionar todas con estas preguntas (opcional)')
                    ->options(fn (): array => Question::query()
                        ->whereBelongsTo(Filament::getTenant())
                        ->orderByDesc('created_at')
                        ->pluck('body', 'id')
                        ->all())
                    ->multiple()
                    ->searchable()
                    ->preload()
                    ->helperText('Si eliges preguntas, todas las creencias creadas se vincularán a ellas.'),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $questionIds = $state['question_ids'] ?? [];
        $created = 0;

        $byType = [
            BeliefType::Myth->value => $state['myths'] ?? '',
            BeliefType::Truth->value => $state['truths'] ?? '',
        ];

        foreach ($byType as $type => $text) {
            foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $line) {
                $statement = trim($line);

                if ($statement === '') {
                    continue;
                }

                $belief = Belief::create([
                    'account_id' => Filament::getTenant()->getKey(),
                    'type' => BeliefType::from($type),
                    'statement' => $statement,
                ]);

                if (! empty($questionIds)) {
                    $belief->questions()->attach($questionIds);
                }

                $created++;
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

        $this->form->fill();
    }
}
