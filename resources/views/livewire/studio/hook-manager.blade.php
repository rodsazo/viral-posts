<div>
    <div class="mb-4">
        <flux:heading size="xl">Ganchos</flux:heading>
        <flux:subheading>Tus plantillas de gancho propias de la marca. En el Generador de piezas las verás junto a las globales de referencia.</flux:subheading>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Lista --}}
        <section class="lg:col-span-4">
            <div class="flex flex-col gap-2 rounded-xl border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:button wire:click="newHook" variant="primary" icon="plus" size="sm" class="w-full">Nuevo gancho</flux:button>

                <div class="mt-1 flex max-h-[32rem] flex-col gap-1 overflow-y-auto">
                    @forelse ($hooks as $hook)
                        <button
                            type="button"
                            wire:key="hook-{{ $hook->id }}"
                            wire:click="selectHook({{ $hook->id }})"
                            @class([
                                'flex items-center gap-2 rounded-lg border px-3 py-2 text-left text-sm transition',
                                'border-violet-300 bg-violet-50 dark:border-violet-500/40 dark:bg-violet-500/10' => $selectedId === $hook->id,
                                'border-transparent hover:bg-zinc-50 dark:hover:bg-zinc-800/50' => $selectedId !== $hook->id,
                            ])
                        >
                            @if ($hook->icon)<i class="{{ $hook->icon }} text-zinc-500"></i>@endif
                            <span class="min-w-0 flex-1 truncate font-medium">{{ $hook->name }}</span>
                        </button>
                    @empty
                        <flux:text class="px-3 py-6 text-center text-zinc-500">Aún no hay ganchos propios. Crea el primero.</flux:text>
                    @endforelse
                </div>
            </div>
        </section>

        {{-- Editor --}}
        <section class="lg:col-span-8">
            @if ($selectedId)
                <div class="flex flex-col gap-5 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center justify-end gap-2">
                        @if ($saved)
                            <flux:badge size="sm" color="green" icon="check">Guardado</flux:badge>
                        @endif
                        @if ($this->canDelete())
                            <flux:button wire:click="deleteHook({{ $selectedId }})" wire:confirm="¿Eliminar este gancho?" variant="subtle" size="sm" icon="trash" />
                        @endif
                    </div>

                    <flux:input wire:model.blur="name" label="Nombre" placeholder="P. ej. «Confesión incómoda»" />

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:select wire:model.live="viral_referent_id" label="Referente viral (opcional)" placeholder="Sin referente">
                            <flux:select.option value="">Sin referente</flux:select.option>
                            @foreach ($referents as $referent)
                                <flux:select.option value="{{ $referent->id }}">{{ $referent->name }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <div class="flex items-end gap-2">
                            <flux:select wire:model.live="icon" label="Ícono" placeholder="Sin ícono" class="flex-1">
                                <flux:select.option value="">Sin ícono</flux:select.option>
                                @foreach ($icons as $class => $label)
                                    <flux:select.option value="{{ $class }}">{{ $label }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            @if ($icon)
                                <div class="flex size-9 items-center justify-center rounded-lg border border-zinc-200 text-lg dark:border-zinc-700">
                                    <i class="{{ $icon }}"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    <flux:textarea wire:model.blur="objective" label="Objetivo" rows="2" placeholder="Qué busca lograr este gancho." />
                    <flux:textarea wire:model.blur="example" label="Ejemplo" rows="3" placeholder="Un ejemplo de gancho escrito con esta plantilla." />
                    <flux:textarea wire:model.blur="notes" label="Notas" rows="2" placeholder="Notas internas (opcional)." />

                    {{-- Ejemplos reales (URLs) --}}
                    <div class="flex flex-col gap-2">
                        <flux:subheading>Ejemplos reales (URLs)</flux:subheading>
                        <flux:text class="text-xs text-zinc-400">Posts donde se usó este gancho (Instagram, TikTok…).</flux:text>

                        @if (count($exampleUrls))
                            <div class="flex flex-col gap-2">
                                @foreach ($exampleUrls as $i => $url)
                                    <div class="flex items-center gap-2" wire:key="ex-{{ $i }}">
                                        <flux:input wire:model.blur="exampleUrls.{{ $i }}" class="flex-1" icon="link" />
                                        <flux:button wire:click="removeExampleUrl({{ $i }})" wire:confirm="¿Quitar este ejemplo?" variant="subtle" size="sm" icon="x-mark" />
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="flex items-center gap-2">
                            <flux:input wire:model="newExampleUrl" wire:keydown.enter.prevent="addExampleUrl" class="flex-1" icon="link" placeholder="https://instagram.com/p/…" />
                            <flux:button wire:click="addExampleUrl" variant="filled" size="sm" icon="plus">Añadir</flux:button>
                        </div>
                    </div>
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Elige un gancho de la lista o crea uno nuevo para editarlo.</flux:text>
                </div>
            @endif
        </section>
    </div>
</div>
