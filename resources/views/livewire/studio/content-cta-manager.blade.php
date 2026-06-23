<div>
    <div class="mb-4 flex items-end justify-between">
        <div>
            <flux:heading size="xl">CTAs de la marca</flux:heading>
            <flux:subheading>Llamadas a la acción reutilizables. Al generar piezas podrás elegir una para que el guión fluya hacia ella.</flux:subheading>
        </div>
        @if ($saved)
            <flux:badge size="sm" color="green" icon="check">Guardado</flux:badge>
        @endif
    </div>

    <div class="mx-auto max-w-3xl space-y-4">
        {{-- Alta de una CTA nueva --}}
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
            <flux:heading size="lg" class="mb-3">Nueva CTA</flux:heading>
            <div class="flex flex-col gap-3">
                <flux:select wire:model="newCta.category" label="Categoría">
                    @foreach ($categories as $category)
                        <flux:select.option value="{{ $category->value }}">{{ $category->getLabel() }}</flux:select.option>
                    @endforeach
                </flux:select>
                <flux:textarea wire:model="newCta.text" label="Texto" rows="2" maxlength="600" placeholder="Sígueme para más trucos como este…" />
                <div class="flex justify-end">
                    <flux:button wire:click="addCta" variant="primary" icon="plus" size="sm">Añadir CTA</flux:button>
                </div>
            </div>
        </div>

        {{-- Listado de CTAs existentes --}}
        @forelse ($ctas as $id => $cta)
            <div wire:key="cta-{{ $id }}" class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <div class="flex items-start gap-3">
                    <div class="w-44 shrink-0">
                        <flux:select wire:model.live="ctas.{{ $id }}.category" label="Categoría" size="sm">
                            @foreach ($categories as $category)
                                <flux:select.option value="{{ $category->value }}">{{ $category->getLabel() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                    <div class="min-w-0 flex-1">
                        <flux:textarea wire:model.live.debounce.600ms="ctas.{{ $id }}.text" label="Texto" rows="2" maxlength="600" />
                    </div>
                    <div class="pt-6">
                        <flux:button wire:click="deleteCta({{ $id }})" wire:confirm="¿Eliminar esta CTA?" variant="subtle" size="sm" icon="trash" />
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                <flux:text class="text-zinc-500">Aún no hay CTAs. Crea la primera arriba.</flux:text>
            </div>
        @endforelse
    </div>
</div>
