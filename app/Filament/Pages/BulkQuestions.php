<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\IdealFollower;
use App\Models\Question;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class BulkQuestions extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Preguntas en lote';

    protected static ?string $title = 'Crear preguntas en lote';

    protected string $view = 'filament.pages.bulk-questions';

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
                    ->label('Seguidor ideal')
                    ->options(fn (): array => IdealFollower::query()
                        ->whereBelongsTo(Filament::getTenant())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->required()
                    ->searchable()
                    ->native(false)
                    ->helperText('Todas las preguntas de esta pantalla se asignarán a este seguidor.'),

                Repeater::make('batches')
                    ->label('Lotes de preguntas')
                    ->addActionLabel('Agregar lote de preguntas')
                    ->defaultItems(1)
                    ->reorderable(false)
                    ->columns(1)
                    ->schema([
                        Select::make('category_id')
                            ->label('Categoría del lote')
                            ->options(fn (): array => Category::query()
                                ->whereBelongsTo(Filament::getTenant())
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all())
                            ->searchable()
                            ->native(false)
                            ->placeholder('Sin categoría')
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label('Nombre')
                                    ->required()
                                    ->maxLength(255),
                                ColorPicker::make('color')
                                    ->label('Color'),
                            ])
                            ->createOptionUsing(fn (array $data): int => Category::create([
                                ...$data,
                                'account_id' => Filament::getTenant()->getKey(),
                            ])->getKey()),
                        Textarea::make('questions')
                            ->label('Preguntas (una por línea)')
                            ->required()
                            ->rows(6)
                            ->helperText('Cada línea no vacía se guardará como una pregunta independiente.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();

        $followerId = $state['ideal_follower_id'];
        $created = 0;

        foreach ($state['batches'] ?? [] as $batch) {
            $categoryId = $batch['category_id'] ?? null;
            $lines = preg_split('/\r\n|\r|\n/', (string) ($batch['questions'] ?? ''));

            foreach ($lines as $line) {
                $body = trim($line);

                if ($body === '') {
                    continue;
                }

                Question::create([
                    'account_id' => Filament::getTenant()->getKey(),
                    'ideal_follower_id' => $followerId,
                    'category_id' => $categoryId,
                    'body' => $body,
                ]);

                $created++;
            }
        }

        if ($created === 0) {
            Notification::make()
                ->title('No se agregó ninguna pregunta')
                ->body('Escribe al menos una línea con texto.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title($created.' '.($created === 1 ? 'pregunta creada' : 'preguntas creadas'))
            ->success()
            ->send();

        // Mantener el seguidor seleccionado y limpiar los lotes para seguir cargando.
        $this->form->fill(['ideal_follower_id' => $followerId]);
    }
}
