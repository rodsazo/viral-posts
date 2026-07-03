<div class="mx-auto max-w-3xl">
    <div class="mb-4 flex items-center gap-2">
        <flux:heading size="xl">Diseño de Marca</flux:heading>
        <flux:badge x-show="$wire.saved" x-cloak size="sm" color="green" icon="check">Guardado</flux:badge>
        <flux:spacer />
        <flux:button wire:click="save" variant="primary" size="sm" icon="check">Guardar</flux:button>
    </div>

    <flux:text class="mb-6 text-zinc-500">
        La identidad de la marca. La <span class="font-medium">promesa</span> y las <span class="font-medium">ofertas</span>
        alimentan la IA en toda generación de ideas y guiones, así que cuídalas.
    </flux:text>

    <div class="flex flex-col gap-6 rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">

        {{-- Logo --}}
        <div class="flex items-start gap-4">
            <div class="shrink-0">
                @php($previewUrl = $logo ? $logo->temporaryUrl() : $account->logoUrl())
                @if ($previewUrl)
                    <img src="{{ $previewUrl }}" alt="Logo de la marca" class="size-20 rounded-xl border border-zinc-200 object-cover dark:border-zinc-700" />
                @else
                    <div class="flex size-20 items-center justify-center rounded-xl border border-dashed border-zinc-300 text-zinc-400 dark:border-zinc-600">
                        <flux:icon.photo class="size-7" />
                    </div>
                @endif
            </div>
            <div class="flex-1">
                <flux:subheading>Logo / imagen</flux:subheading>
                <flux:text class="mb-2 text-zinc-500">PNG o JPG, hasta 4 MB.</flux:text>
                <div class="flex items-center gap-2">
                    <flux:button as="label" size="sm" icon="arrow-up-tray" class="cursor-pointer">
                        {{ $account->logoUrl() ? 'Cambiar' : 'Subir' }}
                        <input type="file" wire:model="logo" accept="image/*" class="hidden" />
                    </flux:button>
                    @if ($account->logoUrl())
                        <flux:button wire:click="removeLogo" wire:confirm="¿Quitar el logo de la marca?" size="sm" variant="subtle" icon="trash">Quitar</flux:button>
                    @endif
                    <span wire:loading wire:target="logo" class="text-sm text-zinc-500">Subiendo…</span>
                </div>
                @error('logo') <flux:text class="mt-1 text-red-500">{{ $message }}</flux:text> @enderror
            </div>
        </div>

        <flux:separator />

        {{-- Campos de identidad / contexto --}}
        <flux:input wire:model.blur="name" label="Nombre de la marca" />

        <flux:textarea wire:model.blur="description" label="Descripción" rows="3" placeholder="¿Qué es la marca y a qué se dedica?" />

        <flux:textarea wire:model.blur="brandPromise" label="Promesa de la marca" rows="3" placeholder="Lo que la marca promete a su audiencia." description="Se envía a la IA en toda generación." />

        <flux:textarea wire:model.blur="mainOffers" label="Oferta(s) principal(es)" rows="3" placeholder="Producto(s)/servicio(s) que vende la marca." description="Se envía a la IA en toda generación." />

        <flux:textarea wire:model.blur="idealCustomerProfile" label="Perfil del cliente ideal" rows="3" placeholder="A quién le vende (más estrecho que el seguidor ideal)." />
    </div>
</div>
