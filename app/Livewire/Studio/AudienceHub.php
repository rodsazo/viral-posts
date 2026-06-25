<?php

namespace App\Livewire\Studio;

use App\Enums\AwarenessLevel;
use App\Enums\BeliefType;
use App\Enums\PainType;
use App\Models\Account;
use App\Models\IdealFollower;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Hub "Audiencia" del Estudio: gestiona los seguidores ideales de la marca y, dentro
 * de cada uno, sus Preguntas, Creencias (mitos/verdades) y Dolores/Problemas/Deseos,
 * más su nivel de conciencia. Todo con autoguardado.
 */
#[Layout('components.layouts.studio')]
class AudienceHub extends Component
{
    public Account $account;

    public ?int $followerId = null;

    // Campos del seguidor seleccionado.
    public string $followerName = '';

    public ?string $awareness = null;

    public ?string $followerDescription = null;

    /** @var array<int, array{body: string}> */
    public array $questions = [];

    /** @var array<int, array{type: string, statement: string}> */
    public array $beliefs = [];

    /** @var array<int, array{type: string, body: string}> */
    public array $pains = [];

    // Altas.
    public string $newQuestion = '';

    /** @var array{type: string, statement: string} */
    public array $newBelief = ['type' => 'myth', 'statement' => ''];

    /** @var array{type: string, body: string} */
    public array $newPain = ['type' => 'pain', 'body' => ''];

    public bool $saved = false;

    public function mount(Account $account): void
    {
        // Sin selección por defecto: el usuario elige un seguidor (evita confusión al abrir).
        $this->account = $account;
    }

    public function newFollower(): void
    {
        $follower = $this->account->idealFollowers()->create(['name' => 'Nuevo seguidor']);
        $this->loadFollower($follower);
    }

    public function selectFollower(int $id): void
    {
        $follower = $this->account->idealFollowers()->find($id);

        if ($follower !== null) {
            $this->loadFollower($follower);
        }
    }

    public function deleteFollower(int $id): void
    {
        $this->account->idealFollowers()->whereKey($id)->delete();

        if ($this->followerId === $id) {
            $this->reset(['followerId', 'followerName', 'awareness', 'followerDescription', 'questions', 'beliefs', 'pains']);
            $next = $this->account->idealFollowers()->orderBy('name')->first();

            if ($next !== null) {
                $this->loadFollower($next);
            }
        }
    }

    private function loadFollower(IdealFollower $follower): void
    {
        $follower->load(['questions', 'beliefs', 'pains']);

        $this->followerId = $follower->id;
        $this->followerName = $follower->name;
        $this->awareness = $follower->awareness_level?->value !== null ? (string) $follower->awareness_level->value : null;
        $this->followerDescription = $follower->description;

        $this->questions = $follower->questions->mapWithKeys(fn ($q) => [$q->id => ['body' => $q->body]])->all();
        $this->beliefs = $follower->beliefs->mapWithKeys(fn ($b) => [$b->id => ['type' => $b->type->value, 'statement' => $b->statement]])->all();
        $this->pains = $follower->pains->mapWithKeys(fn ($p) => [$p->id => ['type' => $p->type->value, 'body' => $p->body]])->all();

        $this->saved = false;
    }

    public function updated(string $name): void
    {
        if (in_array($name, ['followerName', 'awareness', 'followerDescription'], true)) {
            $this->saveFollower();

            return;
        }

        if (preg_match('/^(questions|beliefs|pains)\.(\d+)\./', $name, $m) === 1) {
            $this->persistChild($m[1], (int) $m[2]);
        }
    }

    private function saveFollower(): void
    {
        $follower = $this->currentFollower();

        if ($follower === null) {
            return;
        }

        $follower->update([
            'name' => trim($this->followerName) ?: 'Sin nombre',
            'awareness_level' => ($this->awareness === null || $this->awareness === '') ? null : (int) $this->awareness,
            'description' => $this->followerDescription,
        ]);

        $this->saved = true;
    }

    private function persistChild(string $kind, int $id): void
    {
        $follower = $this->currentFollower();

        if ($follower === null) {
            return;
        }

        match ($kind) {
            'questions' => $follower->questions()->whereKey($id)->update([
                'body' => $this->questions[$id]['body'] ?? '',
            ]),
            'beliefs' => $follower->beliefs()->whereKey($id)->update([
                'type' => $this->beliefs[$id]['type'] ?? BeliefType::Myth->value,
                'statement' => $this->beliefs[$id]['statement'] ?? '',
            ]),
            'pains' => $follower->pains()->whereKey($id)->update([
                'type' => $this->pains[$id]['type'] ?? PainType::Pain->value,
                'body' => $this->pains[$id]['body'] ?? '',
            ]),
        };

        $this->saved = true;
    }

    public function addQuestion(): void
    {
        $follower = $this->currentFollower();

        if ($follower === null || trim($this->newQuestion) === '') {
            return;
        }

        $question = $follower->questions()->create([
            'account_id' => $this->account->getKey(),
            'body' => trim($this->newQuestion),
        ]);

        $this->questions[$question->id] = ['body' => $question->body];
        $this->newQuestion = '';
    }

    public function addBelief(): void
    {
        $follower = $this->currentFollower();

        if ($follower === null || trim($this->newBelief['statement']) === '') {
            return;
        }

        $belief = $follower->beliefs()->create([
            'account_id' => $this->account->getKey(),
            'type' => $this->newBelief['type'],
            'statement' => trim($this->newBelief['statement']),
        ]);

        $this->beliefs[$belief->id] = ['type' => $belief->type->value, 'statement' => $belief->statement];
        $this->newBelief = ['type' => 'myth', 'statement' => ''];
    }

    public function addPain(): void
    {
        $follower = $this->currentFollower();

        if ($follower === null || trim($this->newPain['body']) === '') {
            return;
        }

        $pain = $follower->pains()->create([
            'account_id' => $this->account->getKey(),
            'type' => $this->newPain['type'],
            'body' => trim($this->newPain['body']),
        ]);

        $this->pains[$pain->id] = ['type' => $pain->type->value, 'body' => $pain->body];
        $this->newPain = ['type' => 'pain', 'body' => ''];
    }

    public function deleteQuestion(int $id): void
    {
        $this->currentFollower()?->questions()->whereKey($id)->delete();
        unset($this->questions[$id]);
    }

    public function deleteBelief(int $id): void
    {
        $this->currentFollower()?->beliefs()->whereKey($id)->delete();
        unset($this->beliefs[$id]);
    }

    public function deletePain(int $id): void
    {
        $this->currentFollower()?->pains()->whereKey($id)->delete();
        unset($this->pains[$id]);
    }

    private function currentFollower(): ?IdealFollower
    {
        return $this->followerId === null
            ? null
            : $this->account->idealFollowers()->find($this->followerId);
    }

    public function render(): View
    {
        return view('livewire.studio.audience-hub', [
            'followers' => $this->account->idealFollowers()->orderBy('name')->get(),
            'awarenessLevels' => AwarenessLevel::cases(),
            'beliefTypes' => BeliefType::cases(),
            'painTypes' => PainType::cases(),
        ]);
    }
}
