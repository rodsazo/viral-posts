<?php

namespace App\Livewire\Studio;

use App\Enums\PeriodStatus;
use App\Models\Account;
use App\Support\StudioPeriod;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Selector de periodo activo del Estudio (vive en la cabecera). Permite cambiar de
 * periodo y crear uno nuevo. Al cambiar emite 'period-changed' para que el Composer y
 * el Kanban refiltren sus piezas.
 */
class PeriodSwitcher extends Component
{
    public Account $account;

    public string $newName = '';

    public function mount(Account $account): void
    {
        $this->account = $account;
    }

    public function select(int $id): void
    {
        if ($this->account->periods()->whereKey($id)->exists()) {
            StudioPeriod::set($this->account, $id);
            $this->dispatch('period-changed');
        }
    }

    public function create(): void
    {
        $name = trim($this->newName);

        if ($name === '') {
            return;
        }

        $period = $this->account->periods()->create([
            'name' => mb_substr($name, 0, 120),
            'status' => PeriodStatus::Borrador,
        ]);

        StudioPeriod::set($this->account, $period->id);
        $this->newName = '';
        $this->dispatch('period-changed');
    }

    public function render(): View
    {
        return view('livewire.studio.period-switcher', [
            'periods' => $this->account->periods()->latest('id')->get(),
            'active' => StudioPeriod::get($this->account),
        ]);
    }
}
