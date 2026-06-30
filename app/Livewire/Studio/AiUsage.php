<?php

namespace App\Livewire\Studio;

use App\Models\Account;
use App\Models\AiGeneration;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Uso de IA por marca: volumen de generaciones (no coste — no registramos tokens).
 * Cada generación es una llamada de pago a Anthropic; esto ayuda a dimensionar el gasto.
 */
#[Layout('components.layouts.studio')]
class AiUsage extends Component
{
    public Account $account;

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    private function kindLabel(string $kind): string
    {
        return match ($kind) {
            'script' => 'Guiones de pieza',
            'idea' => 'Ideas ganadoras',
            'kickstart' => 'Kickstart (seguidores)',
            default => ucfirst($kind),
        };
    }

    public function render(): View
    {
        $base = $this->account->aiGenerations();

        $total = (clone $base)->count();
        $thisMonth = (clone $base)->where('created_at', '>=', now()->startOfMonth())->count();
        $failed = (clone $base)->where('status', AiGeneration::STATUS_FAILED)->count();

        $byKind = (clone $base)
            ->selectRaw('kind, count(*) as total')
            ->groupBy('kind')
            ->pluck('total', 'kind')
            ->mapWithKeys(fn (int $count, string $kind) => [$this->kindLabel($kind) => $count])
            ->all();

        $byUser = (clone $base)
            ->with('user:id,name')
            ->selectRaw('user_id, count(*) as total')
            ->groupBy('user_id')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(fn ($row) => [optional($row->user)->name ?? 'Sistema' => $row->total])
            ->all();

        $recent = (clone $base)
            ->with('user:id,name')
            ->latest()
            ->take(12)
            ->get();

        return view('livewire.studio.ai-usage', [
            'total' => $total,
            'thisMonth' => $thisMonth,
            'failed' => $failed,
            'byKind' => $byKind,
            'byUser' => $byUser,
            'recent' => $recent,
            'kindLabel' => fn (string $k) => $this->kindLabel($k),
        ]);
    }
}
