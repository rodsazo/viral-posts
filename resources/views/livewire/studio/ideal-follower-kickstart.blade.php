<div>
    <div class="mb-4">
        <flux:heading size="xl">Kickstart · Seguidores ideales</flux:heading>
        <flux:subheading>A partir de la información de tu marca, la IA propone 3 hipótesis de seguidor ideal con sus dolores, preguntas y mitos. Elige cuáles guardar.</flux:subheading>
    </div>

    @unless ($this->aiEnabled)
        <flux:callout variant="warning" icon="key" class="mb-4">
            El asistente de IA no está configurado. Define <code>ANTHROPIC_API_KEY</code> en el entorno.
        </flux:callout>
    @endunless

    @if ($this->aiEnabled && ! $this->brandReady)
        <flux:callout variant="warning" icon="information-circle" class="mb-4">
            Para mejores resultados, completa primero los datos de la marca (descripción, promesa, ofertas) en
            <strong>Admin → Datos de la marca</strong>. Puedes generar igualmente, pero el resultado será más genérico.
        </flux:callout>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
        {{-- Paso 1 --}}
        <section class="lg:col-span-4">
            <div class="flex flex-col gap-4 rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:heading size="lg">1 · Marca</flux:heading>
                <div class="text-sm text-zinc-600 dark:text-zinc-300">
                    <div class="font-medium">{{ $account->name }}</div>
                    <p class="mt-1 whitespace-pre-line">{{ \Illuminate\Support\Str::limit($account->description ?: 'Sin descripción', 200) }}</p>
                </div>

                <flux:textarea wire:model="instructions" label="Instrucciones adicionales (opcional)" rows="2" placeholder="Enfoque, ángulo o restricciones…" />

                <flux:button
                    wire:click="generate"
                    icon="sparkles"
                    variant="primary"
                    :disabled="! $this->aiEnabled || $this->generating"
                >
                    {{ $this->generating ? 'Generando…' : (count($suggestions) ? 'Regenerar' : 'Generar seguidores') }}
                </flux:button>
            </div>
        </section>

        {{-- Paso 2-3 --}}
        <section class="lg:col-span-8">
            @if ($aiError)
                <flux:callout variant="danger" icon="exclamation-triangle" class="mb-4">{{ $aiError }}</flux:callout>
            @endif

            @if ($this->generating)
                <div wire:poll.2s="pollGeneration" class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:icon.arrow-path class="mx-auto mb-3 size-6 animate-spin text-zinc-400" />
                    <flux:text class="text-zinc-500">Generando hipótesis de seguidor ideal en segundo plano…</flux:text>
                </div>
            @elseif (count($suggestions))
                <div class="mb-3 flex items-center justify-between">
                    <flux:heading size="lg">2 · Elige los seguidores</flux:heading>
                    <flux:button
                        wire:click="createFollowers"
                        icon="user-plus"
                        variant="primary"
                        size="sm"
                        :disabled="! count($selected)"
                    >
                        Guardar {{ count($selected) ?: '' }} {{ count($selected) === 1 ? 'seguidor' : 'seguidores' }}
                    </flux:button>
                </div>

                <div class="flex flex-col gap-4">
                    @foreach ($suggestions as $i => $h)
                        @php($level = \App\Enums\AwarenessLevel::tryFrom($h['awareness_level'] ?? -1))
                        <label class="flex cursor-pointer gap-3 rounded-lg border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-800 dark:hover:bg-zinc-800/50">
                            <flux:checkbox wire:model.live="selected" value="{{ $i }}" />
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <flux:heading size="lg">{{ $h['name'] }}</flux:heading>
                                    @if ($level)
                                        <flux:badge size="sm" :color="$level->getColor()">{{ $level->getLabel() }}</flux:badge>
                                    @endif
                                </div>
                                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $h['description'] }}</p>

                                <div class="mt-3 grid grid-cols-1 gap-3 text-sm sm:grid-cols-3">
                                    <div>
                                        <flux:subheading>Dolores/deseos</flux:subheading>
                                        <ul class="mt-1 space-y-1 text-zinc-600 dark:text-zinc-300">
                                            @foreach ($h['pains'] ?? [] as $p)
                                                <li>· {{ $p['body'] }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div>
                                        <flux:subheading>Preguntas</flux:subheading>
                                        <ul class="mt-1 space-y-1 text-zinc-600 dark:text-zinc-300">
                                            @foreach ($h['questions'] ?? [] as $q)
                                                <li>· {{ $q }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <div>
                                        <flux:subheading>Mitos</flux:subheading>
                                        <ul class="mt-1 space-y-1 text-zinc-600 dark:text-zinc-300">
                                            @foreach ($h['beliefs'] ?? [] as $b)
                                                <li>· {{ $b }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </label>
                    @endforeach
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 p-10 text-center dark:border-zinc-700">
                    <flux:text class="text-zinc-500">Pulsa “Generar seguidores” para que la IA proponga 3 hipótesis basadas en tu marca.</flux:text>
                </div>
            @endif
        </section>
    </div>
</div>
