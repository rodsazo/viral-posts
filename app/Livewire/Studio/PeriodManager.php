<?php

namespace App\Livewire\Studio;

use App\Enums\PeriodStatus;
use App\Models\Account;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Gestor de periodos de la marca en el Estudio. Lista con autoguardado: editar nombre o
 * estado (Borrador/Publicado) persiste al vuelo; alta inmediata; borrado solo Admin.
 */
#[Layout('components.layouts.studio')]
class PeriodManager extends Component
{
    public Account $account;

    /** @var array<int, array{name: string, status: string}> */
    public array $periods = [];

    public string $newName = '';

    public bool $saved = false;

    public function mount(Account $account): void
    {
        $this->account = $account;
        $this->loadPeriods();
    }

    private function loadPeriods(): void
    {
        $this->periods = $this->account->periods()
            ->orderByDesc('id')
            ->get()
            ->mapWithKeys(fn ($period) => [$period->id => [
                'name' => $period->name,
                'status' => $period->status->value,
            ]])
            ->all();
    }

    public function updated(string $name): void
    {
        if (preg_match('/^periods\.(\d+)\./', $name, $m) === 1) {
            $this->persist((int) $m[1]);
        }
    }

    private function persist(int $id): void
    {
        $row = $this->periods[$id] ?? null;

        if ($row === null) {
            return;
        }

        $this->account->periods()->whereKey($id)->update([
            'name' => mb_substr(trim($row['name']), 0, 120) ?: 'Sin nombre',
            'status' => PeriodStatus::tryFrom($row['status'])?->value ?? PeriodStatus::Borrador->value,
        ]);

        $this->saved = true;
    }

    public function addPeriod(): void
    {
        $name = trim($this->newName);

        if ($name === '') {
            return;
        }

        $period = $this->account->periods()->create([
            'name' => mb_substr($name, 0, 120),
            'status' => PeriodStatus::Borrador,
        ]);

        $this->periods = [$period->id => ['name' => $period->name, 'status' => $period->status->value]] + $this->periods;
        $this->newName = '';
    }

    public function deletePeriod(int $id): void
    {
        if (! $this->canDelete()) {
            return;
        }

        // Las piezas no se borran: quedan sin periodo (nullOnDelete).
        $this->account->periods()->whereKey($id)->delete();
        unset($this->periods[$id]);
    }

    public function canDelete(): bool
    {
        return auth()->user()->isAdminOf($this->account);
    }

    public function render(): View
    {
        return view('livewire.studio.period-manager', [
            'statuses' => PeriodStatus::cases(),
            'counts' => $this->account->contentPieces()
                ->selectRaw('period_id, count(*) as total')
                ->whereNotNull('period_id')
                ->groupBy('period_id')
                ->pluck('total', 'period_id'),
        ]);
    }
}
