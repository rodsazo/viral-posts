<div
    x-data="{ open: false }"
    class="relative"
>
    {{-- Disparador: nombre del periodo activo + estado --}}
    <button
        type="button"
        @click="open = ! open"
        class="inline-flex items-center gap-2 rounded-md border border-zinc-200 bg-white px-2.5 py-1.5 text-sm text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
    >
        <flux:icon.calendar-days variant="micro" class="size-4 text-amber-400" />
        @if ($active)
            <span class="font-medium">{{ $active->name }}</span>
            <span class="size-2 rounded-full {{ $active->isPublished() ? 'bg-green-400' : 'bg-zinc-400' }}"
                  title="{{ $active->status->getLabel() }}"></span>
        @else
            <span class="text-zinc-400">Sin periodo</span>
        @endif
        <flux:icon.chevron-down variant="micro" class="size-3.5 opacity-60" />
    </button>

    {{-- Panel --}}
    <div
        x-show="open"
        x-cloak
        x-transition.origin.top
        @click.outside="open = false"
        class="absolute right-0 z-50 mt-1 w-64 rounded-lg border border-zinc-200 bg-white p-1.5 shadow-lg dark:border-zinc-700 dark:bg-zinc-900"
    >
        <p class="px-2 py-1 text-xs font-semibold uppercase tracking-wide text-zinc-400">Periodo de planificación</p>

        <div class="max-h-64 overflow-y-auto">
            {{-- Sin periodo: ver y reasignar piezas sin asignar. --}}
            <button
                type="button"
                wire:click="selectNone"
                @class([
                    'flex w-full items-center justify-between gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800',
                    'bg-zinc-100 dark:bg-zinc-800' => $isNone,
                ])
            >
                <span class="flex items-center gap-2 truncate">
                    @if ($isNone)
                        <flux:icon.check variant="micro" class="size-4 text-violet-400" />
                    @else
                        <span class="size-4"></span>
                    @endif
                    <span class="truncate text-zinc-500">Sin periodo</span>
                </span>
                @if ($unassignedCount > 0)
                    <flux:badge size="sm" color="amber">{{ $unassignedCount }}</flux:badge>
                @endif
            </button>

            @forelse ($periods as $period)
                <button
                    type="button"
                    wire:click="select({{ $period->id }})"
                    @class([
                        'flex w-full items-center justify-between gap-2 rounded-md px-2 py-1.5 text-left text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800',
                        'bg-zinc-100 dark:bg-zinc-800' => $active && $active->is($period),
                    ])
                >
                    <span class="flex items-center gap-2 truncate">
                        @if ($active && $active->is($period))
                            <flux:icon.check variant="micro" class="size-4 text-violet-400" />
                        @else
                            <span class="size-4"></span>
                        @endif
                        <span class="truncate">{{ $period->name }}</span>
                    </span>
                    <flux:badge size="sm" :color="$period->status->fluxColor()">{{ $period->status->getLabel() }}</flux:badge>
                </button>
            @empty
                <p class="px-2 py-2 text-sm text-zinc-500">Aún no hay periodos. Crea el primero.</p>
            @endforelse
        </div>

        {{-- Crear nuevo --}}
        <div class="mt-1.5 border-t border-zinc-100 pt-1.5 dark:border-zinc-800">
            <div class="flex items-center gap-1.5 px-1">
                <input
                    type="text"
                    wire:model="newName"
                    wire:keydown.enter.prevent="create"
                    placeholder="Nuevo periodo (ej. Julio 2026)"
                    class="flex-1 rounded-md border border-zinc-200 bg-white px-2 py-1 text-sm text-zinc-700 placeholder:text-zinc-400 focus:border-violet-400 focus:outline-none dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-200"
                />
                <flux:button wire:click="create" size="xs" variant="primary" icon="plus" aria-label="Crear periodo" />
            </div>
            <a
                href="{{ route('studio.periods', $account) }}"
                class="mt-1 block rounded-md px-2 py-1.5 text-xs font-medium text-violet-500 hover:bg-zinc-100 dark:hover:bg-zinc-800"
            >
                Gestionar periodos →
            </a>
        </div>
    </div>
</div>
