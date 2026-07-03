<?php

namespace App\Livewire\Studio;

use App\Models\Account;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Diseño de Marca (Estudio): edición de la identidad de la marca activa — logo y campos
 * de contexto (nombre, descripción, promesa, oferta(s), cliente ideal). Autoguardado por
 * campo (como el Composer) + botón Guardar. Pensado para crecer: añadir un campo nuevo es
 * añadir una propiedad + su binding + su clave en save().
 */
#[Layout('components.layouts.studio')]
class BrandDesign extends Component
{
    use WithFileUploads;

    public Account $account;

    public string $name = '';

    public ?string $description = null;

    public ?string $brandPromise = null;

    public ?string $mainOffers = null;

    public ?string $idealCustomerProfile = null;

    /** Logo nuevo pendiente de guardar (subida temporal). */
    #[Validate('nullable|image|max:4096')]
    public $logo = null;

    public bool $saved = false;

    public function mount(Account $account): void
    {
        $this->account = $account;
        $this->name = $account->name;
        $this->description = $account->description;
        $this->brandPromise = $account->brand_promise;
        $this->mainOffers = $account->main_offers;
        $this->idealCustomerProfile = $account->ideal_customer_profile;
    }

    /** Autoguardado de los campos de texto (al salir de cada uno). */
    public function updated(string $name): void
    {
        if ($name === 'logo') {
            $this->saveLogo();

            return;
        }

        if (in_array($name, ['name', 'description', 'brandPromise', 'mainOffers', 'idealCustomerProfile'], true)) {
            $this->save();
        }
    }

    public function save(): void
    {
        $this->account->update([
            'name' => trim($this->name) ?: $this->account->name,
            'description' => $this->description ?: null,
            'brand_promise' => $this->brandPromise ?: null,
            'main_offers' => $this->mainOffers ?: null,
            'ideal_customer_profile' => $this->idealCustomerProfile ?: null,
        ]);

        $this->name = $this->account->name;
        $this->saved = true;
    }

    /** Guarda el logo subido en el disco de marcas y borra el anterior. */
    public function saveLogo(): void
    {
        $this->validateOnly('logo');

        if (! $this->logo instanceof TemporaryUploadedFile) {
            return;
        }

        $disk = config('filesystems.brand_disk', 'public');
        $previous = $this->account->logo_path;

        $path = $this->logo->store('brand-logos', $disk);

        $this->account->update(['logo_path' => $path]);

        if (filled($previous) && $previous !== $path) {
            Storage::disk($disk)->delete($previous);
        }

        $this->logo = null;
        $this->saved = true;
    }

    /** Quita el logo actual de la marca. */
    public function removeLogo(): void
    {
        $disk = config('filesystems.brand_disk', 'public');
        $previous = $this->account->logo_path;

        $this->account->update(['logo_path' => null]);

        if (filled($previous)) {
            Storage::disk($disk)->delete($previous);
        }

        $this->logo = null;
        $this->saved = true;
    }

    public function render(): View
    {
        return view('livewire.studio.brand-design');
    }
}
