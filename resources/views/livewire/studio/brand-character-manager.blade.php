<div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
    {{-- Lista de personajes --}}
    <aside class="lg:col-span-3">
        <div class="mb-3 flex items-center justify-between">
            <flux:heading size="lg">Personajes</flux:heading>
            <flux:button wire:click="newCharacter" size="sm" variant="primary" icon="plus">Nuevo</flux:button>
        </div>

        <flux:button :href="route('studio.character-generator', $account)" variant="subtle" size="sm" icon="sparkles" class="mb-3 w-full">
            Generar con IA
        </flux:button>

        <div class="flex flex-col gap-1">
            @forelse ($characters as $character)
                <button
                    type="button"
                    wire:click="selectCharacter({{ $character->id }})"
                    @class([
                        'rounded-lg border px-3 py-2 text-left transition',
                        'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800' => $character->id !== $selectedId,
                        'border-violet-400 bg-violet-50 dark:border-violet-500/50 dark:bg-violet-500/10' => $character->id === $selectedId,
                    ])
                >
                    <div class="truncate text-sm font-medium">{{ $character->name }}</div>
                    @if ($character->archetype)
                        <div class="truncate text-xs text-zinc-500">{{ $character->archetype }}</div>
                    @endif
                </button>
            @empty
                <flux:text class="text-zinc-500">Aún no hay personajes. Crea uno o genéralo con IA.</flux:text>
            @endforelse
        </div>
    </aside>

    @if ($selectedId)
        <section class="lg:col-span-6">
            <div class="mb-3 flex items-center gap-2">
                <flux:heading size="lg">Editar personaje</flux:heading>
                <flux:badge x-show="$wire.saved" x-cloak size="sm" color="green" icon="check">Guardado</flux:badge>
                <flux:spacer />
                @if ($this->canDelete())
                    <flux:button
                        wire:click="deleteCharacter({{ $selectedId }})"
                        wire:confirm="¿Eliminar este personaje de marca? Esta acción no se puede deshacer."
                        variant="subtle" size="sm" icon="trash"
                    >Eliminar</flux:button>
                @endif
                <flux:button wire:click="save" variant="primary" size="sm" icon="check">Guardar</flux:button>
            </div>

            @php($card = 'rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900')

            <div class="flex flex-col gap-5">

                {{-- 0 · Esencia --}}
                <div class="{{ $card }} flex flex-col gap-4">
                    <flux:heading size="sm">0 · Esencia y promesa</flux:heading>
                    <flux:input wire:model.blur="name" label="Nombre del personaje" />
                    <flux:textarea wire:model.blur="essence" label="Esencia en una línea" rows="2" />
                    <flux:textarea wire:model.blur="promise_line" label="Promesa de marca (en su voz)" rows="2" />
                </div>

                {{-- 1 · Arquetipo --}}
                <div class="{{ $card }} flex flex-col gap-4">
                    <flux:heading size="sm">1 · Rol arquetípico</flux:heading>
                    <flux:input wire:model.blur="archetype" label="Arquetipo" />
                    <flux:textarea wire:model.blur="archetype_why" label="Por qué este arquetipo hace creíble la promesa" rows="2" />
                    <flux:textarea wire:model.blur="authority_source" label="Fuente de autoridad" rows="2" />
                </div>

                {{-- 2 · Enemigo --}}
                <div class="{{ $card }} flex flex-col gap-4">
                    <flux:heading size="sm">2 · Enemigo común</flux:heading>
                    <flux:textarea wire:model.blur="enemy_abstract" label="Enemigo abstracto" rows="2" />
                    <div>
                        <flux:subheading class="mb-1">Enemigos concretos</flux:subheading>
                        @foreach ($enemies_concrete as $i => $enemy)
                            <div class="mb-2 flex items-center gap-2" wire:key="enemy-{{ $i }}">
                                <flux:input wire:model.blur="enemies_concrete.{{ $i }}" class="flex-1" />
                                <flux:button wire:click="removeString('enemies_concrete', {{ $i }})" size="sm" variant="subtle" icon="x-mark" aria-label="Quitar" />
                            </div>
                        @endforeach
                        <flux:button wire:click="addString('enemies_concrete')" size="xs" variant="ghost" icon="plus">Añadir enemigo</flux:button>
                    </div>
                    <flux:textarea wire:model.blur="polarization_rule" label="Regla de polarización" rows="2" description="Contra quién se polariza y a quién NUNCA se ataca." />
                </div>

                {{-- 3 · Posturas --}}
                <div class="{{ $card }} flex flex-col gap-3">
                    <flux:heading size="sm">3 · Posturas defendibles</flux:heading>
                    @foreach ($postures as $i => $posture)
                        <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700" wire:key="posture-{{ $i }}">
                            <div class="mb-2 flex items-start gap-2">
                                <flux:textarea wire:model.blur="postures.{{ $i }}.statement" label="Postura" rows="2" class="flex-1" />
                                <flux:button wire:click="removePosture({{ $i }})" size="sm" variant="subtle" icon="x-mark" aria-label="Quitar" class="mt-6" />
                            </div>
                            <flux:input wire:model.blur="postures.{{ $i }}.why" label="Por qué funciona" class="mb-2" />
                            <div class="flex items-center gap-4">
                                <flux:select wire:model.live="postures.{{ $i }}.kind" label="Tipo" class="w-40">
                                    <flux:select.option value="principal">Principal</flux:select.option>
                                    <flux:select.option value="secundaria">Secundaria</flux:select.option>
                                </flux:select>
                                <label class="mt-6 flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                                    <input type="checkbox" wire:model.live="postures.{{ $i }}.bridge" class="rounded border-zinc-300" />
                                    Postura puente (hacia la conversión)
                                </label>
                            </div>
                        </div>
                    @endforeach
                    <flux:button wire:click="addPosture" size="xs" variant="ghost" icon="plus">Añadir postura</flux:button>
                </div>

                {{-- 4 · Historia de origen --}}
                <div class="{{ $card }} flex flex-col gap-4">
                    <flux:heading size="sm">4 · Historia de origen</flux:heading>
                    <flux:textarea wire:model.blur="origin_full" label="Versión completa" rows="8" />
                    <flux:textarea wire:model.blur="origin_reel" label="Versión reel (a cámara)" rows="5" />
                    <flux:textarea wire:model.blur="origin_oneliner" label="Versión una frase" rows="2" />
                </div>

                {{-- 5 · Voz --}}
                <div class="{{ $card }} flex flex-col gap-4">
                    <flux:heading size="sm">5 · Voz y energía</flux:heading>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <flux:textarea wire:model.blur="voice_tone" label="Tono" rows="2" />
                        <flux:textarea wire:model.blur="voice_jargon" label="Jerga" rows="2" />
                        <flux:textarea wire:model.blur="voice_rhythm" label="Ritmo" rows="2" />
                        <flux:textarea wire:model.blur="voice_humor" label="Humor" rows="2" />
                    </div>
                    <flux:input wire:model.blur="verbal_signature" label="Firma verbal de cierre" />
                </div>

                {{-- 6 · Visual --}}
                <div class="{{ $card }} flex flex-col gap-4">
                    <flux:heading size="sm">6 · Identidad visual</flux:heading>
                    <flux:textarea wire:model.blur="visual_principle" label="Principio rector" rows="2" />
                    <flux:textarea wire:model.blur="visual_outfit" label="Atuendo" rows="2" />
                    <flux:textarea wire:model.blur="visual_look" label="Look" rows="2" />
                    <flux:textarea wire:model.blur="visual_environment" label="Entorno / fondo fijo" rows="2" />
                    <div>
                        <flux:subheading class="mb-1">Props (por momento)</flux:subheading>
                        @foreach ($visual_props as $i => $prop)
                            <div class="mb-2 flex items-start gap-2" wire:key="prop-{{ $i }}">
                                <flux:input wire:model.blur="visual_props.{{ $i }}.description" placeholder="Qué es y qué comunica" class="flex-1" />
                                <flux:select wire:model.live="visual_props.{{ $i }}.moment" class="w-36">
                                    <flux:select.option value="durante">Durante</flux:select.option>
                                    <flux:select.option value="fondo">Fondo</flux:select.option>
                                    <flux:select.option value="cierre">Cierre</flux:select.option>
                                </flux:select>
                                <flux:button wire:click="removeProp({{ $i }})" size="sm" variant="subtle" icon="x-mark" aria-label="Quitar" />
                            </div>
                        @endforeach
                        <flux:button wire:click="addProp" size="xs" variant="ghost" icon="plus">Añadir prop</flux:button>
                    </div>
                </div>

                {{-- 7 · Formatos --}}
                <div class="{{ $card }} flex flex-col gap-3">
                    <flux:heading size="sm">7 · Formatos de producción naturales</flux:heading>
                    @foreach ($production_formats as $i => $format)
                        <div class="flex items-center gap-2" wire:key="format-{{ $i }}">
                            <flux:input wire:model.blur="production_formats.{{ $i }}" class="flex-1" />
                            <flux:button wire:click="removeString('production_formats', {{ $i }})" size="sm" variant="subtle" icon="x-mark" aria-label="Quitar" />
                        </div>
                    @endforeach
                    <flux:button wire:click="addString('production_formats')" size="xs" variant="ghost" icon="plus">Añadir formato</flux:button>
                </div>

                {{-- 8 · Conversión --}}
                <div class="{{ $card }} flex flex-col gap-4">
                    <flux:heading size="sm">8 · Conexión con la conversión</flux:heading>
                    <flux:input wire:model.blur="conversion_destination" label="Destino de conversión" />
                    <flux:textarea wire:model.blur="conversion_chain" label="Cadena lógica" rows="2" description="enemigo → postura → CTA → destino" />
                    <div>
                        <flux:subheading class="mb-1">CTAs válidos (acciones reales del destino)</flux:subheading>
                        @foreach ($valid_ctas as $i => $cta)
                            <div class="mb-2 flex items-center gap-2" wire:key="cta-{{ $i }}">
                                <flux:input wire:model.blur="valid_ctas.{{ $i }}" class="flex-1" />
                                <flux:button wire:click="removeString('valid_ctas', {{ $i }})" size="sm" variant="subtle" icon="x-mark" aria-label="Quitar" />
                            </div>
                        @endforeach
                        <flux:button wire:click="addString('valid_ctas')" size="xs" variant="ghost" icon="plus">Añadir CTA</flux:button>
                    </div>
                </div>

                {{-- 9 · Reglas --}}
                <div class="{{ $card }} flex flex-col gap-3">
                    <flux:heading size="sm">9 · Reglas de coherencia (guardrails)</flux:heading>
                    @foreach ($coherence_rules as $i => $rule)
                        <div class="flex items-center gap-2" wire:key="rule-{{ $i }}">
                            <flux:input wire:model.blur="coherence_rules.{{ $i }}" class="flex-1" />
                            <flux:button wire:click="removeString('coherence_rules', {{ $i }})" size="sm" variant="subtle" icon="x-mark" aria-label="Quitar" />
                        </div>
                    @endforeach
                    <flux:button wire:click="addString('coherence_rules')" size="xs" variant="ghost" icon="plus">Añadir regla</flux:button>
                </div>

            </div>
        </section>

        {{-- Chat de refinamiento del personaje --}}
        <aside class="lg:col-span-3">
            @if ($this->aiEnabled)
                <div class="sticky top-6 flex max-h-[calc(100vh-3rem)] flex-col rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
                    <div class="flex items-center gap-2 border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
                        <flux:icon.chat-bubble-left-right variant="micro" class="size-4 text-violet-400" />
                        <flux:heading size="sm">Refinar con IA</flux:heading>
                        <flux:spacer />
                        @if (count($this->characterRefinements))
                            <flux:button wire:click="resetCharacterRefinements" wire:confirm="¿Reiniciar la conversación?" size="xs" variant="subtle" icon="trash" aria-label="Reiniciar" />
                        @endif
                    </div>

                    <div class="flex-1 space-y-3 overflow-y-auto p-4">
                        @forelse ($this->characterRefinements as $message)
                            @if ($message->isUser())
                                <div class="flex justify-end">
                                    <div class="max-w-[90%] rounded-2xl rounded-br-sm bg-violet-500 px-3 py-1.5 text-sm text-white">{{ $message->body }}</div>
                                </div>
                            @else
                                <div class="flex justify-start">
                                    <div class="w-full rounded-2xl rounded-bl-sm border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                        @if (filled($message->body))
                                            <p class="text-sm text-zinc-700 dark:text-zinc-200">{{ $message->body }}</p>
                                        @endif
                                        @if (filled($message->proposal))
                                            <flux:button wire:click="applyCharacterRefinement({{ $message->id }})" size="xs" variant="primary" icon="check" class="mt-2">Usar esta versión</flux:button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @empty
                            <flux:text class="text-zinc-500">Pide ajustes: “cambia el enemigo”, “arquetipo más cercano”, “otra firma verbal”… La IA propone una versión; se aplica solo si la eliges.</flux:text>
                        @endforelse

                        @if ($this->refining)
                            <div wire:poll.2s="pollCharacterRefinement" class="flex items-center gap-2 rounded-2xl border border-zinc-200 bg-zinc-50 px-3 py-2 dark:border-zinc-700 dark:bg-zinc-800">
                                <flux:icon.arrow-path class="size-4 animate-spin text-zinc-400" />
                                <flux:text class="text-zinc-500">Pensando…</flux:text>
                            </div>
                        @endif
                    </div>

                    @if ($refineError)
                        <div class="px-4"><flux:callout variant="danger" icon="exclamation-triangle">{{ $refineError }}</flux:callout></div>
                    @endif

                    <div class="border-t border-zinc-200 p-3 dark:border-zinc-800">
                        <div class="flex items-end gap-2">
                            <flux:textarea wire:model="refineInstruction" wire:keydown.cmd.enter="sendCharacterRefinement" wire:keydown.ctrl.enter="sendCharacterRefinement" rows="2" class="flex-1" placeholder="Pide un ajuste… (⌘/Ctrl + Enter)" />
                            <flux:button wire:click="sendCharacterRefinement" variant="primary" icon="paper-airplane" :disabled="$this->refining" aria-label="Enviar" />
                        </div>
                    </div>
                </div>
            @endif
        </aside>
    @else
        <section class="lg:col-span-9">
            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                <flux:text class="text-zinc-500">Selecciona un personaje o <a href="{{ route('studio.character-generator', $account) }}" class="font-medium text-violet-500 hover:underline">genéralo con IA</a>.</flux:text>
            </div>
        </section>
    @endif
</div>
