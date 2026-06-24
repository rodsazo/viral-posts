<?php

namespace App\Livewire\Studio;

use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentStatus;
use App\Jobs\GenerateSuggestionsJob;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\Belief;
use App\Models\ContentPiece;
use App\Models\WinningIdea;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\ScriptContext;
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

    // El seguidor ideal es el centro: de él salen los mitos/verdades a tratar.
    public ?int $idealFollowerId = null;

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

    /**
     * Sugerencias de guión de la IA, en forma serializable. Solo se aplican si el
     * usuario elige una (ver applyScriptSuggestion); la IA no reescribe por su cuenta.
     *
     * @var array<int, array{label: string, fields: array<string, string>, preview: string}>
     */
    public array $scriptSuggestions = [];

    public ?string $aiBrief = null;

    public ?string $aiError = null;

    // Generación en curso (id del registro AiGeneration) mientras se procesa en la cola.
    public ?int $scriptGenerationId = null;

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

    /** Borra una pieza (reservado a administradores de la marca, igual que en el admin). */
    public function deletePiece(int $id): void
    {
        if (! $this->canDelete()) {
            return;
        }

        $this->account->contentPieces()->whereKey($id)->delete();

        if ($this->pieceId === $id) {
            $this->reset([
                'pieceId', 'winning_idea_id', 'idealFollowerId', 'title', 'objective', 'format', 'status', 'rating',
                'hookText', 'story', 'moral', 'cta', 'postUrl', 'previewImageUrl', 'publishedAt', 'rumFactors', 'saved',
            ]);

            $next = $this->account->contentPieces()->latest('updated_at')->first();

            if ($next !== null) {
                $this->loadPiece($next);
            }
        }
    }

    public function canDelete(): bool
    {
        return auth()->user()->isAdminOf($this->account);
    }

    private function loadPiece(ContentPiece $piece): void
    {
        $this->pieceId = $piece->id;
        $this->winning_idea_id = $piece->winning_idea_id;
        $this->idealFollowerId = $piece->ideal_follower_id;
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
        // Al elegir idea, si no hay seguidor aún, hereda el de la idea.
        if ($name === 'winning_idea_id' && blank($this->idealFollowerId)) {
            $this->idealFollowerId = $this->selectedIdea()?->ideal_follower_id;
        }

        $fields = ['winning_idea_id', 'idealFollowerId', 'title', 'objective', 'format', 'status', 'rating', 'hookText', 'story', 'moral', 'cta', 'postUrl', 'previewImageUrl'];

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
            'ideal_follower_id' => $this->idealFollowerId ?: null,
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

    /** ¿Está configurado el asistente de IA? La vista usa esto para mostrar el botón. */
    #[Computed]
    public function aiEnabled(): bool
    {
        return app(ContentAssistant::class)->isConfigured();
    }

    /** Abre el modal del asistente (paso 1). No genera ni reescribe nada todavía. */
    public function suggestScript(): void
    {
        if (! app(ContentAssistant::class)->isConfigured() || $this->pieceId === null) {
            return;
        }

        $this->aiError = null;
        $this->aiBrief = null;
        $this->scriptSuggestions = [];
        $this->scriptGenerationId = null;

        $this->modal('script-suggestions')->show();
    }

    /** ¿Generación de guión en curso (encolada)? La vista la usa para hacer polling. */
    #[Computed]
    public function generatingScript(): bool
    {
        return $this->scriptGenerationId !== null;
    }

    /** Encola la generación (o regeneración) de variantes de guión con el contexto + instrucciones. */
    public function generateScriptSuggestions(): void
    {
        $this->aiError = null;
        $this->scriptSuggestions = [];

        if (! app(ContentAssistant::class)->isConfigured() || $this->pieceId === null) {
            return;
        }

        // Contexto desde el estado actual del composer (idea + borrador del guión + instrucciones).
        $context = ScriptContext::fromIdea($this->selectedIdea());
        $context->title = $this->title;
        $context->objective = $this->objective ? ContentObjective::tryFrom($this->objective)?->getLabel() : null;
        $context->format = $this->format ? ContentFormat::tryFrom($this->format)?->getLabel() : null;
        $context->currentHook = $this->hookText;
        $context->currentStory = $this->story;
        $context->currentMoral = $this->moral;
        $context->currentCta = $this->cta;
        $context->extra = $this->aiBrief;

        $generation = AiGeneration::create([
            'account_id' => $this->account->getKey(),
            'user_id' => auth()->id(),
            'kind' => 'script',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        GenerateSuggestionsJob::dispatch($generation->id, $context, (int) config('ai.script.inline_suggestions', 3));

        $this->scriptGenerationId = $generation->id;
    }

    /** Polling: cuando el job termina, carga las sugerencias (o el error) y deja de sondear. */
    public function pollScript(): void
    {
        if ($this->scriptGenerationId === null) {
            return;
        }

        $generation = AiGeneration::find($this->scriptGenerationId);

        if ($generation === null || $generation->isProcessing()) {
            return;
        }

        if ($generation->isDone()) {
            $this->scriptSuggestions = $generation->result ?? [];
        } else {
            $this->aiError = $generation->error ?: 'No se pudo generar. Inténtalo de nuevo.';
        }

        $this->scriptGenerationId = null;
    }

    /** Aplica la sugerencia elegida (afecta varios campos del guión) y guarda. */
    public function applyScriptSuggestion(int $index): void
    {
        $chosen = $this->scriptSuggestions[$index] ?? null;

        if ($chosen === null) {
            return;
        }

        $fields = $chosen['fields'];
        $this->hookText = $fields['hook'] ?? $this->hookText;
        $this->story = $fields['story'] ?? $this->story;
        $this->moral = $fields['moral'] ?? $this->moral;
        $this->cta = $fields['cta'] ?? $this->cta;

        $this->save();
        $this->modal('script-suggestions')->close();
    }

    private function selectedIdea(): ?WinningIdea
    {
        if (! $this->winning_idea_id) {
            return null;
        }

        return $this->account->winningIdeas()
            ->with('questions')
            ->find($this->winning_idea_id);
    }

    /** @return array<int, string> */
    #[Computed]
    public function contextQuestions(): array
    {
        return $this->selectedIdea()?->questions->pluck('body')->all() ?? [];
    }

    /** Mitos/verdades a tratar: del seguidor de la pieza (o, en su defecto, del de la idea). */
    /** @return array<int, string> */
    #[Computed]
    public function contextBeliefs(): array
    {
        $followerId = $this->idealFollowerId ?: $this->selectedIdea()?->ideal_follower_id;

        if (! $followerId) {
            return [];
        }

        return $this->account->beliefs()
            ->where('ideal_follower_id', $followerId)
            ->orderBy('statement')
            ->get()
            ->map(fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement)
            ->all();
    }

    public function render(): View
    {
        return view('livewire.studio.piece-composer', [
            'pieces' => $this->account->contentPieces()->latest('updated_at')->get(),
            'ideas' => $this->account->winningIdeas()->orderBy('title')->get(),
            'followers' => $this->account->idealFollowers()->orderBy('name')->get(),
        ]);
    }
}
