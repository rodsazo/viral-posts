<div class="flex flex-col gap-5">
    <div>
        <flux:heading size="xl">Ideas Referenciales</flux:heading>
        <flux:subheading>Catálogo de ideas ganadoras de referencia. Filtra y selecciona las que quieras <strong>importar</strong> a tu marca como Ideas Ganadoras (entran en <strong>Borrador</strong>, con su referente y sus URLs).</flux:subheading>
    </div>

    {{-- Filtros + acción --}}
    <div class="flex flex-wrap items-end gap-3 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <flux:input
            wire:model.live.debounce.300ms="search"
            label="Buscar"
            icon="magnifying-glass"
            placeholder="Título, mecanismo, formato o estructura…"
            class="w-72"
        />

        <flux:select wire:model.live="referentFilter" label="Referente" placeholder="Todos los referentes" class="w-56">
            <flux:select.option value="">Todos los referentes</flux:select.option>
            @foreach ($referents as $referent)
                <flux:select.option value="{{ $referent->id }}">{{ $referent->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="nicheFilter" label="Nicho" placeholder="Todos los nichos" class="w-56">
            <flux:select.option value="">Todos los nichos</flux:select.option>
            @foreach ($niches as $niche)
                <flux:select.option value="{{ $niche->id }}">{{ $niche->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:spacer />

        <flux:button
            wire:click="import"
            wire:confirm="¿Importar las ideas seleccionadas a tu marca?"
            variant="primary"
            icon="arrow-down-tray"
            :disabled="count($selected) === 0"
        >
            Importar {{ count($selected) ?: '' }} {{ count($selected) === 1 ? 'idea' : 'ideas' }}
        </flux:button>
    </div>

    {{-- Tarjetas --}}
    @if ($templates->isEmpty())
        <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
            <flux:text class="text-zinc-500">No hay ideas referenciales para estos filtros.</flux:text>
        </div>
    @else
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($templates as $template)
                @php($isSel = in_array($template->id, $selected))
                <label
                    wire:key="ref-{{ $template->id }}"
                    @class([
                        'flex cursor-pointer flex-col gap-3 rounded-xl border p-4 transition',
                        'border-violet-400 bg-violet-50 ring-1 ring-violet-300 dark:border-violet-500/50 dark:bg-violet-500/10' => $isSel,
                        'border-zinc-200 bg-white hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900' => ! $isSel,
                    ])
                >
                    <div class="flex items-start justify-between gap-2">
                        <flux:heading size="sm" class="leading-snug">{{ $template->name }}</flux:heading>
                        <flux:checkbox wire:model.live="selected" value="{{ $template->id }}" />
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5">
                        @if ($template->viralReferent)
                            <flux:badge size="sm" color="zinc" icon="user">{{ $template->viralReferent->name }}</flux:badge>
                        @endif
                        @if ($template->viralReferent?->niche)
                            <flux:badge size="sm" :color="$template->viralReferent->niche->color ? 'zinc' : 'zinc'">{{ $template->viralReferent->niche->name }}</flux:badge>
                        @endif
                        @if ($template->suggested_format)
                            <flux:badge size="sm" color="blue">{{ $template->suggested_format }}</flux:badge>
                        @endif
                    </div>

                    @if (filled($template->structure))
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-300">{{ \Illuminate\Support\Str::limit($template->structure, 220) }}</flux:text>
                    @endif

                    @if (filled($template->viral_mechanism))
                        <flux:text class="text-xs text-zinc-500"><span class="font-medium">Mecanismo:</span> {{ $template->viral_mechanism }}</flux:text>
                    @endif

                    @php($refCount = (int) filled($template->reference_url) + count($template->reference_urls ?? []))
                    @if ($refCount > 0)
                        <flux:text class="flex items-center gap-1 text-xs text-zinc-400">
                            <flux:icon.link variant="micro" class="size-3.5" />
                            {{ $refCount }} {{ $refCount === 1 ? 'URL de referencia' : 'URLs de referencia' }}
                        </flux:text>
                    @endif
                </label>
            @endforeach
        </div>
    @endif
</div>
