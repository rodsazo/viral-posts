<?php

namespace App\Support\Ai;

use App\Models\Account;

/**
 * Contexto de marca que alimenta el Kickstart (generación de seguidores ideales).
 */
class BrandContext
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?string $brandPromise = null,
        public ?string $mainOffers = null,
        public ?string $idealCustomerProfile = null,
        public ?string $extra = null,
    ) {}

    public static function fromAccount(Account $account): self
    {
        return new self(
            name: $account->name,
            description: $account->description,
            brandPromise: $account->brand_promise,
            mainOffers: $account->main_offers,
            idealCustomerProfile: $account->ideal_customer_profile,
        );
    }

    /** ¿Hay material suficiente de marca para generar con sentido? */
    public function hasMaterial(): bool
    {
        return filled($this->description) || filled($this->brandPromise) || filled($this->mainOffers);
    }

    public function toPrompt(): string
    {
        $lines = ['Genera hipótesis de seguidor ideal para esta marca.', ''];

        $fields = array_filter([
            'Marca' => $this->name,
            'Descripción' => $this->description,
            'Promesa de la marca' => $this->brandPromise,
            'Oferta(s) principal(es)' => $this->mainOffers,
            'Perfil del cliente ideal' => $this->idealCustomerProfile,
        ], fn ($v) => filled($v));

        foreach ($fields as $label => $value) {
            $lines[] = "{$label}: {$value}";
        }

        if (filled($this->extra)) {
            $lines[] = '';
            $lines[] = 'Instrucciones adicionales del creador (priorízalas):';
            $lines[] = $this->extra;
        }

        return implode("\n", $lines);
    }
}
