<?php

namespace App\Livewire\Studio;

use App\Enums\BeliefType;
use App\Models\Account;
use App\Models\Capture;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.studio')]
class CaptureInbox extends Component
{
    public Account $account;

    public string $note = '';

    public ?int $followerId = null;

    public function mount(Account $account): void
    {
        $this->account = $account;
        $this->followerId = $account->idealFollowers()->value('id');
    }

    /** Cada línea no vacía se guarda como una captura. */
    public function capture(): void
    {
        foreach (preg_split('/\r\n|\r|\n/', $this->note) as $line) {
            $body = trim($line);

            if ($body !== '') {
                $this->account->captures()->create(['body' => $body]);
            }
        }

        $this->note = '';
    }

    // No usar el nombre "pull": colisiona con Livewire\Component::pull().
    private function findCapture(int $id): ?Capture
    {
        return $this->account->captures()->find($id);
    }

    public function toBelief(int $id, string $type): void
    {
        // Toda creencia cuelga de un seguidor ideal: requiere uno elegido (igual que "a pregunta").
        if (! $this->followerId || ($capture = $this->findCapture($id)) === null) {
            return;
        }

        $this->account->beliefs()->create([
            'ideal_follower_id' => $this->followerId,
            'type' => BeliefType::from($type),
            'statement' => $capture->body,
        ]);

        $capture->delete();
    }

    public function toIdea(int $id): void
    {
        if (($capture = $this->findCapture($id)) === null) {
            return;
        }

        $this->account->winningIdeas()->create([
            'title' => Str::limit($capture->body, 250),
            'concept' => $capture->body,
        ]);

        $capture->delete();
    }

    public function toQuestion(int $id): void
    {
        if (! $this->followerId || ($capture = $this->findCapture($id)) === null) {
            return;
        }

        $this->account->questions()->create([
            'ideal_follower_id' => $this->followerId,
            'body' => $capture->body,
        ]);

        $capture->delete();
    }

    public function discard(int $id): void
    {
        $this->findCapture($id)?->delete();
    }

    public function render(): View
    {
        return view('livewire.studio.capture-inbox', [
            'captures' => $this->account->captures()->latest()->get(),
            'followers' => $this->account->idealFollowers()->orderBy('name')->get(),
        ]);
    }
}
