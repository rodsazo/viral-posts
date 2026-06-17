<?php

namespace App\Filament\Pages;

use App\Enums\ViralMechanism;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\WinningIdea;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
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
                Select::make('ideal_follower_id')
                    ->label('Seguidor ideal (filtro)')
                    ->options(fn (): array => IdealFollower::query()
                        ->whereBelongsTo(Filament::getTenant())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->placeholder('Todos los seguidores')
                    ->helperText('Opcional: filtra las preguntas que aparecen en cada idea.'),

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
                            ->label('Concepto')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),
                        Select::make('question_ids')
                            ->label('Preguntas que resuelve (opcional)')
                            ->options(function (Get $get): array {
                                $followerId = $get('../../ideal_follower_id');

                                return Question::query()
                                    ->whereBelongsTo(Filament::getTenant())
                                    ->when($followerId, fn ($query) => $query->where('ideal_follower_id', $followerId))
                                    ->orderByDesc('created_at')
                                    ->pluck('body', 'id')
                                    ->all();
                            })
                            ->multiple()
                            ->searchable()
                            ->preload()
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

            $idea = WinningIdea::create([
                'account_id' => $accountId,
                'title' => $title,
                'concept' => $concept,
                'viral_mechanism' => $row['viral_mechanism'] ?? null,
            ]);

            if (! empty($row['question_ids'])) {
                $idea->questions()->attach($row['question_ids']);
            }

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

        $this->form->fill(['ideal_follower_id' => $state['ideal_follower_id'] ?? null]);
    }
}
