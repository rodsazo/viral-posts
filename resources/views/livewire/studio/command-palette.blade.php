<div>
    {{-- Atajo global de teclado: ⌘K / Ctrl+K abre la paleta. --}}
    <div
        x-data
        x-on:keydown.window.meta.k.prevent="$flux.modal('command-palette').show()"
        x-on:keydown.window.ctrl.k.prevent="$flux.modal('command-palette').show()"
    ></div>

    {{-- Disparador visible (cabecera) --}}
    <button
        type="button"
        x-on:click="$flux.modal('command-palette').show()"
        class="inline-flex items-center gap-1.5 rounded-md border border-zinc-200 bg-white px-2.5 py-1.5 text-sm text-zinc-500 hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800"
        aria-label="Buscar (⌘K)"
    >
        <flux:icon.magnifying-glass variant="micro" class="size-4" />
        <kbd class="hidden font-sans text-xs sm:inline">⌘K</kbd>
    </button>

    <flux:modal name="command-palette" class="md:w-[36rem]" x-on:close="$wire.clear()">
        <div class="flex flex-col gap-3">
            <flux:input
                wire:model.live.debounce.200ms="query"
                icon="magnifying-glass"
                placeholder="Buscar piezas, ideas o ir a una sección…"
                autofocus
            />

            <div class="max-h-[24rem] overflow-y-auto">
                {{-- Piezas --}}
                @if ($pieces->isNotEmpty())
                    <p class="px-1 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-zinc-400">Piezas</p>
                    @foreach ($pieces as $piece)
                        <a href="{{ route('studio.pieces', [$account, 'piece' => $piece->id]) }}" wire:navigate
                           class="flex items-center justify-between gap-2 rounded-md px-2 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <span class="flex items-center gap-2 truncate">
                                <flux:icon.document-text variant="micro" class="size-4 text-zinc-400" />
                                <span class="truncate">{{ $piece->title }}</span>
                            </span>
                            <flux:badge size="sm" :color="$piece->status->fluxColor()">{{ $piece->status->getLabel() }}</flux:badge>
                        </a>
                    @endforeach
                @endif

                {{-- Ideas --}}
                @if ($ideas->isNotEmpty())
                    <p class="px-1 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-zinc-400">Ideas ganadoras</p>
                    @foreach ($ideas as $idea)
                        <a href="{{ route('studio.winning-ideas', $account) }}" wire:navigate
                           class="flex items-center justify-between gap-2 rounded-md px-2 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <span class="flex items-center gap-2 truncate">
                                <flux:icon.light-bulb variant="micro" class="size-4 text-zinc-400" />
                                <span class="truncate">{{ $idea->title }}</span>
                            </span>
                            <flux:badge size="sm" :color="$idea->status->fluxColor()">{{ $idea->status->getLabel() }}</flux:badge>
                        </a>
                    @endforeach
                @endif

                {{-- Secciones / atajos --}}
                @if (count($shortcuts))
                    <p class="px-1 pb-1 pt-3 text-xs font-semibold uppercase tracking-wide text-zinc-400">Ir a</p>
                    @foreach ($shortcuts as $s)
                        <a href="{{ $s['href'] }}" wire:navigate
                           class="flex items-center gap-2 rounded-md px-2 py-2 text-sm hover:bg-zinc-100 dark:hover:bg-zinc-800">
                            <flux:icon :name="$s['icon']" variant="micro" class="size-4 text-violet-400" />
                            <span>{{ $s['label'] }}</span>
                        </a>
                    @endforeach
                @endif

                @if (filled($query) && $pieces->isEmpty() && $ideas->isEmpty() && empty($shortcuts))
                    <p class="px-2 py-6 text-center text-sm text-zinc-500">Nada coincide con «{{ $query }}».</p>
                @endif
            </div>
        </div>
    </flux:modal>
</div>
