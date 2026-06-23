<?php

namespace App\Livewire\Studio;

use App\Enums\CtaCategory;
use App\Models\Account;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Gestor de CTAs (llamadas a la acción) de la marca en el Estudio. Lista con
 * autoguardado: editar categoría/texto persiste al vuelo; alta y borrado inmediatos.
 */
#[Layout('components.layouts.studio')]
class ContentCtaManager extends Component
{
    public Account $account;

    /** @var array<int, array{category: string, text: string}> */
    public array $ctas = [];

    // Alta de una CTA nueva.
    /** @var array{category: string, text: string} */
    public array $newCta = ['category' => 'follow', 'text' => ''];

    public bool $saved = false;

    public function mount(Account $account): void
    {
        $this->account = $account;
        $this->loadCtas();
    }

    private function loadCtas(): void
    {
        $this->ctas = $this->account->contentCtas()
            ->orderBy('id')
            ->get()
            ->mapWithKeys(fn ($cta) => [$cta->id => [
                'category' => $cta->category->value,
                'text' => $cta->text,
            ]])
            ->all();
    }

    public function updated(string $name): void
    {
        if (preg_match('/^ctas\.(\d+)\./', $name, $m) === 1) {
            $this->persist((int) $m[1]);
        }
    }

    private function persist(int $id): void
    {
        $row = $this->ctas[$id] ?? null;

        if ($row === null) {
            return;
        }

        $this->account->contentCtas()->whereKey($id)->update([
            'category' => $row['category'] ?: CtaCategory::Follow->value,
            'text' => mb_substr(trim($row['text']), 0, 600),
        ]);

        $this->saved = true;
    }

    public function addCta(): void
    {
        if (trim($this->newCta['text']) === '') {
            return;
        }

        $cta = $this->account->contentCtas()->create([
            'category' => $this->newCta['category'] ?: CtaCategory::Follow->value,
            'text' => mb_substr(trim($this->newCta['text']), 0, 600),
        ]);

        $this->ctas[$cta->id] = ['category' => $cta->category->value, 'text' => $cta->text];
        $this->newCta = ['category' => 'follow', 'text' => ''];
    }

    public function deleteCta(int $id): void
    {
        $this->account->contentCtas()->whereKey($id)->delete();
        unset($this->ctas[$id]);
    }

    public function render(): View
    {
        return view('livewire.studio.content-cta-manager', [
            'categories' => CtaCategory::cases(),
        ]);
    }
}
