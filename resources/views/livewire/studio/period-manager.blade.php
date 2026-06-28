<div class="mx-auto max-w-3xl">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <flux:heading size="xl">Periodos</flux:heading>
            <flux:text class="mt-1 text-zinc-500">Ventanas de planificación de tu contenido (p. ej. «Julio 2026»). Un periodo <strong>Publicado</strong> habilita la URL pública de sus piezas que ya estén «Lista para grabación» en adelante.</flux:text>
        </div>
        <flux:badge x-show="$wire.saved" x-cloak size="sm" color="green" icon="check">Guardado</flux:badge>
    </div>

    {{-- Alta --}}
    <div class="mb-5 flex items-end gap-2 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <flux:input
            wire:model="newName"
            wire:keydown.enter="addPeriod"
            label="Nuevo periodo"
            placeholder="Julio 2026"
            class="flex-1"
        />
        <flux:button wire:click="addPeriod" variant="primary" icon="plus">Añadir periodo</flux:button>
    </div>

    {{-- Lista --}}
    <div class="flex flex-col gap-2">
        @forelse ($periods as $id => $period)
            <div class="flex items-center gap-3 rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:input wire:model.blur="periods.{{ $id }}.name" class="flex-1" />

                <flux:select wire:model.live="periods.{{ $id }}.status" class="w-40">
                    @foreach ($statuses as $status)
                        <flux:select.option value="{{ $status->value }}">{{ $status->getLabel() }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:badge size="sm" color="zinc">{{ $counts[$id] ?? 0 }} piezas</flux:badge>

                @if ($this->canDelete())
                    <flux:button
                        wire:click="deletePeriod({{ $id }})"
                        wire:confirm="¿Eliminar este periodo? Sus piezas quedarán sin periodo (no se borran)."
                        variant="subtle"
                        size="sm"
                        icon="trash"
                        aria-label="Eliminar periodo"
                    />
                @endif
            </div>
        @empty
            <flux:text class="text-zinc-500">Aún no hay periodos. Crea el primero arriba.</flux:text>
        @endforelse
    </div>
</div>
