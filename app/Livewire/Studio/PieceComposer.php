<?php

namespace App\Livewire\Studio;

use App\Enums\ClientReviewStatus;
use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\ContentStatus;
use App\Enums\IdeaStatus;
use App\Jobs\GenerateSuggestionsJob;
use App\Jobs\RefinePieceJob;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\Belief;
use App\Models\ContentPiece;
use App\Models\Pain;
use App\Models\PieceRefinement;
use App\Models\WinningIdea;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\RefineContext;
use App\Support\Ai\ScriptContext;
use App\Support\LinkPreview;
use App\Support\Rum;
use App\Support\StudioPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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
    // Sin tipar (como winning_idea_id) para tolerar el "" que mandan los selects de Flux.
    public $idealFollowerId = null;

    // Periodo de la pieza (permite mover una pieza sin periodo a uno). Sin tipar por Flux.
    public $periodId = null;

    public string $title = '';

    public ?string $objective = null;

    public ?string $format = null;

    public string $status = 'borrador';

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

    // Producción / planificación de grabación.
    public ?string $location = null;

    public ?string $equipment = null;

    public ?string $people = null;

    public ?string $clientNotes = null;

    public bool $saved = false;

    // Filtro de la lista de piezas por estado ('' = todos). Sin tipar por los selects de Flux.
    public $statusFilter = '';

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

    // ── Asistente conversacional (refinamiento del guión estilo chat) ──────────────
    // Instrucción que escribe el creador ("más cálido", "más corto"…).
    public string $refineInstruction = '';

    // Turno de refinamiento en curso (id del registro AiGeneration) mientras se procesa.
    public ?int $refineGenerationId = null;

    public ?string $refineError = null;

    public function mount(Account $account): void
    {
        $this->account = $account;

        // Sin selección por defecto: solo se carga una pieza si llega por deep-link
        // (?piece={id}, p. ej. desde el kanban). Al abrir la sección no hay nada elegido.
        $requested = request()->integer('piece');

        $piece = $requested
            ? $this->account->contentPieces()->find($requested)
            : null;

        if ($piece !== null) {
            $this->loadPiece($piece);
        }
    }

    public function newPiece(): void
    {
        // Toda pieza nueva se asocia al periodo activo del Estudio (si hay).
        $piece = $this->account->contentPieces()->create([
            'title' => 'Nueva pieza',
            'status' => ContentStatus::Borrador,
            'period_id' => StudioPeriod::id($this->account),
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
                'pieceId', 'winning_idea_id', 'idealFollowerId', 'periodId', 'title', 'objective', 'format', 'status', 'rating',
                'hookText', 'story', 'moral', 'cta', 'postUrl', 'previewImageUrl', 'publishedAt', 'rumFactors', 'saved',
                'location', 'equipment', 'people', 'clientNotes',
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
        $this->periodId = $piece->period_id;
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
        $this->location = $piece->location;
        $this->equipment = $piece->equipment;
        $this->people = $piece->people;
        $this->clientNotes = $piece->client_notes;
        $this->saved = false;

        // Reinicia el estado del chat de refinamiento al cambiar de pieza.
        $this->refineInstruction = '';
        $this->refineGenerationId = null;
        $this->refineError = null;
        unset($this->refinements);
    }

    /** Autoguardado: cualquier campo enlazado dispara save(). */
    public function updated(string $name): void
    {
        $fields = ['winning_idea_id', 'idealFollowerId', 'periodId', 'title', 'objective', 'format', 'status', 'rating', 'hookText', 'story', 'moral', 'cta', 'postUrl', 'previewImageUrl', 'location', 'equipment', 'people', 'clientNotes'];

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
            'period_id' => $this->periodId ?: null,
            'title' => trim((string) $this->title) ?: 'Sin título',
            'objective' => $this->objective ?: null,
            'format' => $this->format ?: null,
            'status' => $this->status ?: ContentStatus::Borrador->value,
            'rating' => $this->rating ?: null,
            'hook' => $this->hookText,
            'story' => $this->story,
            'moral' => $this->moral,
            'cta' => $this->cta,
            'url' => $this->postUrl ?: null,
            'preview_image_url' => $this->previewImageUrl ?: null,
            'rum_factors' => $this->rumFactors ?: null,
            'location' => $this->location ?: null,
            'equipment' => $this->equipment ?: null,
            'people' => $this->people ?: null,
            'client_notes' => $this->clientNotes ?: null,
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
        $context->brandPromise ??= $this->account->brand_promise;
        $context->mainOffers ??= $this->account->main_offers;
        // El contexto del seguidor (preguntas/mitos/dolores) sale del seguidor de la pieza.
        $context->questions = $this->contextQuestions;
        $context->beliefs = $this->contextBeliefs;
        $context->pains = $this->contextPains;
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
        $this->dispatch('ai-generation-done');
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

    // ── Asistente conversacional (chat de refinamiento del guión) ──────────────────

    /**
     * Hilo de refinamiento de la pieza abierta, en orden cronológico.
     *
     * @return Collection<int, PieceRefinement>
     */
    #[Computed]
    public function refinements(): Collection
    {
        if ($this->pieceId === null) {
            return collect();
        }

        return PieceRefinement::where('content_piece_id', $this->pieceId)->orderBy('id')->get();
    }

    /** ¿Hay un turno de refinamiento procesándose (encolado)? La vista lo usa para el polling. */
    #[Computed]
    public function refining(): bool
    {
        return $this->refineGenerationId !== null;
    }

    /**
     * Envía una instrucción de refinamiento ("más cálido", "más corto"). Persiste el turno
     * del usuario en el hilo y encola la respuesta de la IA (que llega por polling).
     */
    public function sendRefinement(): void
    {
        $instruction = trim($this->refineInstruction);

        // Nota: comprobamos la propiedad directa (no el computed `refining`) para no memoizar
        // su valor antiguo antes de arrancar la generación en el mismo request.
        if ($instruction === '' || $this->pieceId === null || $this->refineGenerationId !== null) {
            return;
        }

        if (! app(ContentAssistant::class)->isConfigured()) {
            return;
        }

        $piece = $this->account->contentPieces()->find($this->pieceId);

        if ($piece === null) {
            return;
        }

        // El historial que ve la IA son los turnos YA existentes (antes de este). La nueva
        // instrucción viaja aparte como último mensaje del usuario.
        $history = $this->refinements
            ->map(fn (PieceRefinement $r): array => [
                'role' => $r->role,
                'body' => $r->body,
                'proposal' => $r->proposal,
            ])
            ->all();

        $context = new RefineContext(
            instruction: $instruction,
            brandPromise: $this->account->brand_promise,
            mainOffers: $this->account->main_offers,
            ideaTitle: $this->selectedIdea()?->title,
            ideaConcept: $this->selectedIdea()?->concept,
            objective: $this->objective ? ContentObjective::tryFrom($this->objective)?->getLabel() : null,
            format: $this->format ? ContentFormat::tryFrom($this->format)?->getLabel() : null,
            questions: $this->contextQuestions,
            beliefs: $this->contextBeliefs,
            pains: $this->contextPains,
            baseHook: $this->hookText,
            baseStory: $this->story,
            baseMoral: $this->moral,
            baseCta: $this->cta,
            history: $history,
        );

        // Persiste el turno del usuario (se ve en el chat de inmediato).
        PieceRefinement::create([
            'content_piece_id' => $piece->id,
            'user_id' => auth()->id(),
            'role' => PieceRefinement::ROLE_USER,
            'body' => $instruction,
        ]);

        $generation = AiGeneration::create([
            'account_id' => $this->account->getKey(),
            'user_id' => auth()->id(),
            'kind' => 'refine',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        RefinePieceJob::dispatch($generation->id, $context);

        $this->refineGenerationId = $generation->id;
        $this->refineInstruction = '';
        $this->refineError = null;
        unset($this->refinements);
    }

    /** Atajo: rellena la instrucción con un preajuste ("Más corto"…) y la envía. */
    public function quickRefine(string $instruction): void
    {
        $this->refineInstruction = $instruction;
        $this->sendRefinement();
    }

    /** Polling: cuando el job termina, añade el mensaje del asistente al hilo (o el error). */
    public function pollRefinement(): void
    {
        if ($this->refineGenerationId === null) {
            return;
        }

        $generation = AiGeneration::find($this->refineGenerationId);

        if ($generation === null || $generation->isProcessing()) {
            return;
        }

        if ($generation->isDone() && $this->pieceId !== null) {
            $result = $generation->result ?? [];

            PieceRefinement::create([
                'content_piece_id' => $this->pieceId,
                'role' => PieceRefinement::ROLE_ASSISTANT,
                'body' => $result['note'] ?? null,
                'proposal' => $result['fields'] ?? null,
            ]);
        } elseif (! $generation->isDone()) {
            $this->refineError = $generation->error ?: 'No se pudo refinar. Inténtalo de nuevo.';
        }

        $this->refineGenerationId = null;
        unset($this->refinements);
        $this->dispatch('ai-generation-done');
    }

    /** Aplica al guión la versión propuesta por un turno del asistente y guarda. */
    public function applyRefinement(int $id): void
    {
        $refinement = $this->refinements->firstWhere('id', $id);

        if ($refinement === null || ! $refinement->isAssistant() || ! filled($refinement->proposal)) {
            return;
        }

        $fields = $refinement->proposal;
        $this->hookText = $fields['hook'] ?? $this->hookText;
        $this->story = $fields['story'] ?? $this->story;
        $this->moral = $fields['moral'] ?? $this->moral;
        $this->cta = $fields['cta'] ?? $this->cta;

        $this->save();
    }

    /** Reinicia la conversación de refinamiento (borra el hilo de la pieza). */
    public function resetRefinements(): void
    {
        if ($this->pieceId === null) {
            return;
        }

        PieceRefinement::where('content_piece_id', $this->pieceId)->delete();

        $this->refineGenerationId = null;
        $this->refineError = null;
        unset($this->refinements);
    }

    private function selectedIdea(): ?WinningIdea
    {
        if (! $this->winning_idea_id) {
            return null;
        }

        return $this->account->winningIdeas()->find($this->winning_idea_id);
    }

    /** Preguntas del seguidor ideal de la pieza. */
    /** @return array<int, string> */
    #[Computed]
    public function contextQuestions(): array
    {
        if (! $this->idealFollowerId) {
            return [];
        }

        return $this->account->questions()
            ->where('ideal_follower_id', $this->idealFollowerId)
            ->orderBy('body')
            ->pluck('body')
            ->all();
    }

    /** Mitos/verdades a tratar: del seguidor ideal de la pieza. */
    /** @return array<int, string> */
    #[Computed]
    public function contextBeliefs(): array
    {
        if (! $this->idealFollowerId) {
            return [];
        }

        return $this->account->beliefs()
            ->where('ideal_follower_id', $this->idealFollowerId)
            ->orderBy('statement')
            ->get()
            ->map(fn (Belief $belief): string => '['.$belief->type->getLabel().'] '.$belief->statement)
            ->all();
    }

    /** Dolores/problemas/deseos del seguidor ideal de la pieza. */
    /** @return array<int, string> */
    #[Computed]
    public function contextPains(): array
    {
        if (! $this->idealFollowerId) {
            return [];
        }

        return $this->account->pains()
            ->where('ideal_follower_id', $this->idealFollowerId)
            ->orderBy('type')
            ->orderBy('body')
            ->get()
            ->map(fn (Pain $pain): string => '['.$pain->type->getLabel().'] '.$pain->body)
            ->all();
    }

    /** Respuesta del cliente desde la vista pública (null si aún no ha respondido). */
    #[Computed]
    public function clientReview(): ?ContentPiece
    {
        if (! $this->pieceId) {
            return null;
        }

        $piece = $this->account->contentPieces()->whereKey($this->pieceId)->first();

        return ($piece && $piece->client_review_status !== ClientReviewStatus::Pending) ? $piece : null;
    }

    /** Reels de referencia de la idea ganadora elegida (ejemplos reales, URLs). */
    /** @return array<int, string> */
    #[Computed]
    public function ideaReferences(): array
    {
        return array_values(array_filter(
            $this->selectedIdea()?->example_urls ?? [],
            fn ($u): bool => filled($u),
        ));
    }

    /** Enlace público (sin login) de la pieza seleccionada, para compartir con el cliente. */
    #[Computed]
    public function publicUrl(): ?string
    {
        if (! $this->pieceId) {
            return null;
        }

        $token = $this->account->contentPieces()->whereKey($this->pieceId)->value('public_token');

        return $token ? route('piece.public', $token) : null;
    }

    public function render(): View
    {
        return view('livewire.studio.piece-composer', [
            'pieces' => $this->account->contentPieces()
                ->tap(fn ($q) => StudioPeriod::scopeQuery($q, $this->account))
                ->when(filled($this->statusFilter), fn ($q) => $q->where('status', $this->statusFilter))
                ->latest('updated_at')->get(),
            'activePeriod' => StudioPeriod::get($this->account),
            // Ocultamos las ideas descartadas del selector, pero conservamos la que ya
            // tenga asignada esta pieza (para no romper su selección).
            'ideas' => $this->account->winningIdeas()
                ->where(function ($q): void {
                    $q->where('status', '!=', IdeaStatus::Descartada->value);
                    if ($this->winning_idea_id) {
                        $q->orWhere('id', $this->winning_idea_id);
                    }
                })
                ->orderBy('title')->get(),
            'followers' => $this->account->idealFollowers()->orderBy('name')->get(),
            'periods' => $this->account->periods()->latest('id')->get(),
        ]);
    }
}
