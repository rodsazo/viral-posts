<?php

namespace App\Livewire\Studio;

use App\Enums\ContentStatus;
use App\Models\Account;
use App\Models\Belief;
use App\Models\ContentPiece;
use App\Models\WinningIdea;
use App\Support\LinkPreview;
use App\Support\Rum;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.studio')]
class PieceComposer extends Component
{
    public Account $account;

    public ?int $pieceId = null;

    // Campos del formulario. (sin tipar para tolerar "" desde los selects de Flux)
    public $winning_idea_id = null;

    public string $title = '';

    public ?string $objective = null;

    public ?string $format = null;

    public string $status = 'planificacion';

    public ?string $rating = null;

    // Nota: la propiedad NO puede llamarse "hook" — colisiona con $wire.hook (interno de Livewire).
    public ?string $hookText = null;

    public ?string $story = null;

    public ?string $moral = null;

    public ?string $cta = null;

    // Publicación. (nombres seguros: evitar colisiones con métodos de Livewire\Component)
    public ?string $postUrl = null;

    public ?string $previewImageUrl = null;

    public ?string $publishedAt = null;

    /** @var array<string, string> */
    public array $rumFactors = [];

    public bool $saved = false;

    public function mount(Account $account): void
    {
        $this->account = $account;

        // Deep-link opcional desde el kanban: ?piece={id}
        $requested = request()->integer('piece');

        $piece = $requested
            ? $this->account->contentPieces()->find($requested)
            : $this->account->contentPieces()->latest('updated_at')->first();

        if ($piece !== null) {
            $this->loadPiece($piece);
        }
    }

    public function newPiece(): void
    {
        $piece = $this->account->contentPieces()->create([
            'title' => 'Nueva pieza',
            'status' => ContentStatus::Planificacion,
        ]);

        $this->loadPiece($piece);
    }

    public function selectPiece(int $id): void
    {
        $piece = $this->account->contentPieces()->find($id);

        if ($piece !== null) {
            $this->loadPiece($piece);
        }
    }

    private function loadPiece(ContentPiece $piece): void
    {
        $this->pieceId = $piece->id;
        $this->winning_idea_id = $piece->winning_idea_id;
        $this->title = $piece->title;
        $this->objective = $piece->objective?->value;
        $this->format = $piece->format?->value;
        $this->status = $piece->status->value;
        $this->rating = $piece->rating?->value;
        $this->hookText = $piece->hook;
        $this->story = $piece->story;
        $this->moral = $piece->moral;
        $this->cta = $piece->cta;
        $this->postUrl = $piece->url;
        $this->previewImageUrl = $piece->preview_image_url;
        $this->publishedAt = $piece->published_at?->format('Y-m-d');
        $this->rumFactors = $piece->rum_factors ?? [];
        $this->saved = false;
    }

    /** Autoguardado: cualquier campo enlazado dispara save(). */
    public function updated(string $name): void
    {
        $fields = ['winning_idea_id', 'title', 'objective', 'format', 'status', 'rating', 'hookText', 'story', 'moral', 'cta', 'postUrl', 'previewImageUrl'];

        if (in_array($name, $fields, true) || str_starts_with($name, 'rumFactors')) {
            $this->save();
        }
    }

    public function save(): void
    {
        if ($this->pieceId === null) {
            return;
        }

        $piece = $this->account->contentPieces()->find($this->pieceId);

        if ($piece === null) {
            return;
        }

        $piece->update([
            'winning_idea_id' => $this->winning_idea_id ?: null,
            'title' => trim((string) $this->title) ?: 'Sin título',
            'objective' => $this->objective ?: null,
            'format' => $this->format ?: null,
            'status' => $this->status ?: ContentStatus::Planificacion->value,
            'rating' => $this->rating ?: null,
            'hook' => $this->hookText,
            'story' => $this->story,
            'moral' => $this->moral,
            'cta' => $this->cta,
            'url' => $this->postUrl ?: null,
            'preview_image_url' => $this->previewImageUrl ?: null,
            'rum_factors' => $this->rumFactors ?: null,
        ]);

        $this->saved = true;
    }

    #[Computed]
    public function rum(): ?float
    {
        return Rum::compute($this->rumFactors);
    }

    public function markPublished(): void
    {
        if ($this->pieceId === null) {
            return;
        }

        $piece = $this->account->contentPieces()->find($this->pieceId);

        if ($piece === null) {
            return;
        }

        $piece->update([
            'status' => ContentStatus::Publicada,
            'published_at' => $piece->published_at ?? now(),
        ]);

        $this->status = ContentStatus::Publicada->value;
        $this->publishedAt = $piece->published_at?->format('Y-m-d');
    }

    public function fetchPreview(): void
    {
        $image = app(LinkPreview::class)->imageFor($this->postUrl);

        if ($image !== null) {
            $this->previewImageUrl = $image;
            $this->save();
        }
    }

    private function selectedIdea(): ?WinningIdea
    {
        if (! $this->winning_idea_id) {
            return null;
        }

        return $this->account->winningIdeas()
            ->with('questions.beliefs')
            ->find($this->winning_idea_id);
    }

    /** @return array<int, string> */
    #[Computed]
    public function contextQuestions(): array
    {
        return $this->selectedIdea()?->questions->pluck('body')->all() ?? [];
    }

    /** @return array<int, string> */
    #[Computed]
    public function contextBeliefs(): array
    {
        $idea = $this->selectedIdea();

        if ($idea === null) {
            return [];
        }

        return $idea->derivedBeliefs()
            ->map(fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement)
            ->all();
    }

    public function render(): View
    {
        return view('livewire.studio.piece-composer', [
            'pieces' => $this->account->contentPieces()->latest('updated_at')->get(),
            'ideas' => $this->account->winningIdeas()->orderBy('title')->get(),
        ]);
    }
}
