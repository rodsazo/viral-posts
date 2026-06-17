<div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
    {{-- Lista de piezas --}}
    <aside class="lg:col-span-3">
        <div class="mb-3 flex items-center justify-between">
            <flux:heading size="lg">Piezas</flux:heading>
            <flux:button wire:click="newPiece" size="sm" variant="primary" icon="plus">Nueva</flux:button>
        </div>

        <div class="flex flex-col gap-1">
            @forelse ($pieces as $piece)
                <button
                    type="button"
                    wire:click="selectPiece({{ $piece->id }})"
                    @class([
                        'rounded-lg border px-3 py-2 text-left transition',
                        'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:bg-zinc-800' => $piece->id !== $pieceId,
                        'border-amber-400 bg-amber-50 dark:border-amber-500/50 dark:bg-amber-500/10' => $piece->id === $pieceId,
                    ])
                >
                    <div class="truncate text-sm font-medium">{{ $piece->title }}</div>
                    <div class="mt-1">
                        <flux:badge size="sm" color="zinc">{{ $piece->status->getLabel() }}</flux:badge>
                    </div>
                </button>
            @empty
                <flux:text class="text-zinc-500">Aún no hay piezas. Crea la primera.</flux:text>
            @endforelse
        </div>
    </aside>

    @if ($pieceId)
        {{-- Composer --}}
        <section class="lg:col-span-6">
            <div class="mb-3 flex items-center gap-2">
                <flux:heading size="lg">Composer</flux:heading>
                <flux:badge x-show="$wire.saved" x-cloak size="sm" color="green" icon="check">Guardado</flux:badge>
            </div>

            <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:input wire:model.blur="title" label="Título de trabajo" />

                <flux:select wire:model.live="winning_idea_id" label="Idea ganadora" placeholder="Sin idea (pieza suelta)">
                    <flux:select.option value="">Sin idea (pieza suelta)</flux:select.option>
                    @foreach ($ideas as $idea)
                        <flux:select.option value="{{ $idea->id }}">{{ $idea->title }}</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-2 gap-4">
                    <flux:select wire:model.live="status" label="Estado">
                        @foreach (\App\Enums\ContentStatus::cases() as $case)
                            <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="objective" label="Objetivo" placeholder="Sin objetivo">
                        <flux:select.option value="">Sin objetivo</flux:select.option>
                        @foreach (\App\Enums\ContentObjective::cases() as $case)
                            <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="format" label="Formato" placeholder="Sin formato">
                        <flux:select.option value="">Sin formato</flux:select.option>
                        @foreach (\App\Enums\ContentFormat::cases() as $case)
                            <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                    <flux:select wire:model.live="rating" label="Calificación" placeholder="Sin calificar">
                        <flux:select.option value="">Sin calificar</flux:select.option>
                        @foreach (\App\Enums\ContentRating::cases() as $case)
                            <flux:select.option value="{{ $case->value }}">{{ $case->getLabel() }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <flux:separator text="Guión" />

                <flux:textarea wire:model.blur="hookText" label="Gancho" rows="2" />
                <flux:textarea wire:model.blur="story" label="Historia" rows="4" />
                <flux:textarea wire:model.blur="moral" label="Moraleja" rows="2" />
                <flux:textarea wire:model.blur="cta" label="CTA" rows="2" />

                <flux:separator text="Evaluación RUM" />
                <p class="text-xs text-zinc-500 dark:text-zinc-400">Relevancia Única de Mercado — busca un RUM alto para más chance de viralidad.</p>

                @foreach (\App\Support\Rum::FACTORS as $key => $factor)
                    <flux:select wire:model.live="rumFactors.{{ $key }}" label="{{ $factor['label'] }}" placeholder="Sin evaluar" description="{{ $factor['help'] }}">
                        <flux:select.option value="">Sin evaluar</flux:select.option>
                        @foreach ($factor['options'] as $val => $optLabel)
                            <flux:select.option value="{{ $val }}">{{ $optLabel }}</flux:select.option>
                        @endforeach
                    </flux:select>
                @endforeach

                <div class="flex items-center gap-2">
                    <span class="text-sm font-medium">RUM:</span>
                    @if ($this->rum !== null)
                        <flux:badge size="lg" :color="\App\Support\Rum::fluxColor($this->rum)">{{ number_format($this->rum, 1) }}</flux:badge>
                    @else
                        <flux:badge size="lg" color="zinc">Sin evaluar</flux:badge>
                    @endif
                </div>

                <flux:separator text="Publicación" />

                <div class="flex items-end gap-2">
                    <flux:input wire:model.blur="postUrl" label="URL publicada" placeholder="https://..." class="flex-1" />
                    <flux:button wire:click="fetchPreview" icon="photo" variant="subtle">Vista previa</flux:button>
                </div>

                @if ($previewImageUrl)
                    <img src="{{ $previewImageUrl }}" alt="Vista previa del post" class="max-h-48 rounded-lg border border-zinc-200 object-cover dark:border-zinc-800" />
                @endif

                <div class="flex items-center justify-between">
                    <flux:text class="text-zinc-500">
                        @if ($publishedAt)
                            Publicada el {{ $publishedAt }}
                        @else
                            Sin publicar
                        @endif
                    </flux:text>
                    @if ($status !== \App\Enums\ContentStatus::Publicada->value)
                        <flux:button wire:click="markPublished" icon="check-badge" variant="primary" size="sm">Marcar publicada</flux:button>
                    @endif
                </div>
            </div>
        </section>

        {{-- Contexto en vivo --}}
        <aside class="lg:col-span-3">
            <flux:heading size="lg" class="mb-3">Contexto</flux:heading>

            <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
                <div>
                    <flux:subheading>Preguntas que responde</flux:subheading>
                    @if (count($this->contextQuestions))
                        <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-zinc-600 dark:text-zinc-300">
                            @foreach ($this->contextQuestions as $q)
                                <li>{{ $q }}</li>
                            @endforeach
                        </ul>
                    @else
                        <flux:text class="mt-1 text-zinc-400">Elige una idea para ver su contexto.</flux:text>
                    @endif
                </div>

                <flux:separator />

                <div>
                    <flux:subheading>💡 Mitos/verdades a tratar</flux:subheading>
                    @if (count($this->contextBeliefs))
                        <ul class="mt-1 list-disc space-y-1 pl-5 text-sm text-zinc-600 dark:text-zinc-300">
                            @foreach ($this->contextBeliefs as $b)
                                <li>{{ $b }}</li>
                            @endforeach
                        </ul>
                    @else
                        <flux:text class="mt-1 text-zinc-400">—</flux:text>
                    @endif
                </div>
            </div>
        </aside>
    @else
        <section class="lg:col-span-9">
            <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                <flux:text class="text-zinc-500">Selecciona una pieza a la izquierda o crea una nueva para empezar.</flux:text>
            </div>
        </section>
    @endif
</div>
