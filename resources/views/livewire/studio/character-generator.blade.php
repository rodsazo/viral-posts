<div class="mx-auto max-w-3xl">
    <div class="mb-2 flex items-center gap-2">
        <flux:heading size="xl">Generador de Personajes</flux:heading>
    </div>
    <flux:text class="mb-6 text-zinc-500">
        La IA construye un personaje de marca completo (las 9 secciones) a partir de tu marca y unos pocos
        insumos clave. Precargamos lo que ya sabemos; revisa y completa lo demás. Luego podrás editarlo y refinarlo.
    </flux:text>

    @if (! $this->aiEnabled)
        <flux:callout variant="warning" icon="exclamation-triangle">
            El asistente de IA no está configurado (falta <code>ANTHROPIC_API_KEY</code>).
        </flux:callout>
    @else
        <div class="flex flex-col gap-5">

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 flex flex-col gap-4">
                <flux:heading size="sm">La marca (precargado — ajústalo si quieres)</flux:heading>
                <flux:input wire:model="desiredName" label="Nombre del personaje (opcional)" placeholder="Si lo dejas vacío, la IA propone uno." />
                <flux:textarea wire:model="brandPromise" label="Promesa principal de marca" rows="3" description="El problema del mercado + la solución. Es el insumo más importante." />
                <flux:textarea wire:model="mainOffers" label="Oferta(s) principal(es)" rows="2" />
                <flux:textarea wire:model="idealCustomerProfile" label="Perfil del cliente ideal" rows="2" />
                <flux:textarea wire:model="audienceNotes" label="Audiencia y motivos de entrada" rows="4" description="Derivado de tus seguidores ideales; edítalo libremente (una línea por grupo)." />
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 flex flex-col gap-4">
                <flux:heading size="sm">Destino de conversión</flux:heading>
                <flux:input wire:model="conversionDestination" label="¿A dónde llevas a la audiencia?" placeholder="p. ej. MesasRoleras.com" />
                <flux:textarea wire:model="validActions" label="Acciones/CTAs reales del destino" rows="2" description="Solo lo que el destino realmente ofrece (la IA no inventará otras)." />
                <label class="flex items-center gap-2 text-sm text-zinc-600 dark:text-zinc-300">
                    <input type="checkbox" wire:model.live="isTopOfFunnel" class="rounded border-zinc-300" />
                    Esta marca personal es top-of-funnel de otra marca
                </label>
                @if ($isTopOfFunnel)
                    <flux:input wire:model="parentBrand" label="Marca destino (de la que es top-of-funnel)" description="Se hereda la descripción del destino y CTAs reales; NO su voz ni identidad visual." />
                @endif
            </div>

            <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900 flex flex-col gap-4">
                <flux:heading size="sm">Historia de origen (hechos reales)</flux:heading>
                <flux:text class="text-zinc-500">La IA no inventa tu historia: dale los hechos y ella arma el molde. Lo que falte lo marcará como [HUECO].</flux:text>
                <flux:textarea wire:model="originFacts" label="¿Qué creías antes, qué momento te hizo cambiar, qué descubriste?" rows="5" placeholder="Escenas concretas: personas, lugar, una noche específica…" />
                <flux:select wire:model="convertArc" label="En el origen del problema, ¿de qué lado estabas?">
                    <flux:select.option value="">Prefiero no especificar</flux:select.option>
                    <flux:select.option value="sufria">Sufría el problema (víctima)</flux:select.option>
                    <flux:select.option value="causaba">Lo causaba sin saberlo (converso)</flux:select.option>
                </flux:select>
                <flux:textarea wire:model="extra" label="Instrucciones adicionales (opcional)" rows="2" />
            </div>

            @if ($aiError)
                <flux:callout variant="danger" icon="exclamation-triangle">{{ $aiError }}</flux:callout>
            @endif

            @if ($this->generating)
                <div wire:poll.2s="pollGeneration" class="flex items-center justify-center gap-2 rounded-xl border border-dashed border-zinc-300 p-8 text-center dark:border-zinc-700">
                    <flux:icon.arrow-path class="size-5 animate-spin text-zinc-400" />
                    <flux:text class="text-zinc-500">Construyendo el personaje… (puede tardar hasta ~1 min)</flux:text>
                </div>
            @else
                <div class="flex items-center justify-between">
                    <flux:text class="text-zinc-500">
                        @unless ($this->canGenerate)
                            Añade al menos la <span class="font-medium">promesa de marca</span> para generar.
                        @endunless
                    </flux:text>
                    <flux:button wire:click="generate" variant="primary" icon="sparkles" :disabled="! $this->canGenerate">
                        Generar personaje
                    </flux:button>
                </div>
            @endif
        </div>
    @endif
</div>
