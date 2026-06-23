<div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
    {{-- Lista de seguidores --}}
    <aside class="lg:col-span-3">
        <div class="mb-3 flex items-center justify-between">
            <flux:heading size="lg">Seguidores</flux:heading>
            <flux:button wire:click="newFollower" size="sm" variant="primary" icon="plus">Nuevo</flux:button>
        </div>

        <div class="flex flex-col gap-1">
            @forelse ($followers as $follower)
                <button
                    type="button"
                    wire:click="selectFollower({{ $follower->id }})"
                    @class([
                        'rounded-lg border px-3 py-2 text-left transition',
                        'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800' => $follower->id !== $followerId,
                        'border-amber-400 bg-amber-50 dark:border-amber-500/50 dark:bg-amber-500/10' => $follower->id === $followerId,
                    ])
                >
                    <div class="truncate text-sm font-medium">{{ $follower->name }}</div>
                    @if ($follower->awareness_level)
                        <div class="mt-1">
                            <flux:badge size="sm" :color="$follower->awareness_level->getColor()">Nivel {{ $follower->awareness_level->value }}</flux:badge>
                        </div>
                    @endif
                </button>
            @empty
                <flux:text class="text-zinc-500">Aún no hay seguidores. Crea el primero.</flux:text>
            @endforelse
        </div>
    </aside>

    @if ($followerId)
        <section class="lg:col-span-9">
            <div class="mb-3 flex items-center gap-2">
                <flux:heading size="lg">Editar seguidor</flux:heading>
                <flux:badge x-show="$wire.saved" x-cloak size="sm" color="green" icon="check">Guardado</flux:badge>
                <flux:spacer />
                <flux:button
                    wire:click="deleteFollower({{ $followerId }})"
                    wire:confirm="¿Borrar este seguidor y todas sus preguntas, creencias y dolores?"
                    size="sm"
                    variant="subtle"
                    icon="trash"
                >Borrar</flux:button>
            </div>

            <div class="flex flex-col gap-5 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                {{-- Datos del seguidor --}}
                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <flux:input wire:model.blur="followerName" label="Nombre del perfil" />
                    <flux:select wire:model.live="awareness" label="Nivel de conciencia (Heras)" placeholder="Sin definir">
                        <flux:select.option value="">Sin definir</flux:select.option>
                        @foreach ($awarenessLevels as $level)
                            <flux:select.option value="{{ $level->value }}">{{ $level->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>
                <flux:textarea wire:model.blur="followerDescription" label="Descripción" rows="3" />

                {{-- Preguntas --}}
                <flux:separator text="Preguntas" />
                <div class="flex flex-col gap-2">
                    @foreach ($questions as $id => $q)
                        <div class="flex items-start gap-2" wire:key="q-{{ $id }}">
                            <flux:textarea wire:model.blur="questions.{{ $id }}.body" rows="1" class="flex-1" />
                            <flux:button wire:click="deleteQuestion({{ $id }})" icon="x-mark" variant="subtle" size="sm" />
                        </div>
                    @endforeach
                    <div class="flex items-end gap-2">
                        <flux:input wire:model="newQuestion" wire:keydown.enter="addQuestion" placeholder="Nueva pregunta…" class="flex-1" />
                        <flux:button wire:click="addQuestion" icon="plus" size="sm">Añadir</flux:button>
                    </div>
                </div>

                {{-- Creencias --}}
                <flux:separator text="Creencias (mitos / verdades)" />
                <div class="flex flex-col gap-2">
                    @foreach ($beliefs as $id => $b)
                        <div class="flex items-start gap-2" wire:key="b-{{ $id }}">
                            <flux:select wire:model.live="beliefs.{{ $id }}.type" class="w-32">
                                @foreach ($beliefTypes as $type)
                                    <flux:select.option value="{{ $type->value }}">{{ $type->getLabel() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:textarea wire:model.blur="beliefs.{{ $id }}.statement" rows="1" class="flex-1" />
                            <flux:button wire:click="deleteBelief({{ $id }})" icon="x-mark" variant="subtle" size="sm" />
                        </div>
                    @endforeach
                    <div class="flex items-end gap-2">
                        <flux:select wire:model="newBelief.type" class="w-32">
                            @foreach ($beliefTypes as $type)
                                <flux:select.option value="{{ $type->value }}">{{ $type->getLabel() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="newBelief.statement" wire:keydown.enter="addBelief" placeholder="Nuevo mito/verdad…" class="flex-1" />
                        <flux:button wire:click="addBelief" icon="plus" size="sm">Añadir</flux:button>
                    </div>
                </div>

                {{-- Dolores --}}
                <flux:separator text="Dolores / Problemas / Deseos" />
                <div class="flex flex-col gap-2">
                    @foreach ($pains as $id => $p)
                        <div class="flex items-start gap-2" wire:key="p-{{ $id }}">
                            <flux:select wire:model.live="pains.{{ $id }}.type" class="w-32">
                                @foreach ($painTypes as $type)
                                    <flux:select.option value="{{ $type->value }}">{{ $type->getLabel() }}</flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:textarea wire:model.blur="pains.{{ $id }}.body" rows="1" class="flex-1" />
                            <flux:button wire:click="deletePain({{ $id }})" icon="x-mark" variant="subtle" size="sm" />
                        </div>
                    @endforeach
                    <div class="flex items-end gap-2">
                        <flux:select wire:model="newPain.type" class="w-32">
                            @foreach ($painTypes as $type)
                                <flux:select.option value="{{ $type->value }}">{{ $type->getLabel() }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:input wire:model="newPain.body" wire:keydown.enter="addPain" placeholder="Nuevo dolor/deseo…" class="flex-1" />
                        <flux:button wire:click="addPain" icon="plus" size="sm">Añadir</flux:button>
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="lg:col-span-9">
            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                <flux:text class="text-zinc-500">Selecciona un seguidor a la izquierda o crea uno nuevo para editar su audiencia.</flux:text>
            </div>
        </section>
    @endif
</div>
