<?php

namespace App\Support\Ai;

/**
 * Contexto para generar un Personaje de Marca. Reúne lo que el app ya sabe de la marca
 * (promesa, ofertas, audiencia) más los insumos nuevos que aporta el usuario en el
 * formulario del generador (destino de conversión, hechos de la historia de origen, etc.).
 *
 * El "cómo" (los 8 pasos y heurísticas del framework) vive en el system prompt
 * (config ai.character); aquí va el "con qué" (los datos del caso).
 */
class CharacterContext
{
    /**
     * @param  array<int, string>  $audience  seguidores ideales / motivos de entrada (deseos/dolores)
     */
    public function __construct(
        public ?string $desiredName = null,
        public ?string $brandName = null,
        public ?string $brandDescription = null,
        public ?string $brandPromise = null,
        public ?string $mainOffers = null,
        public ?string $idealCustomerProfile = null,
        public array $audience = [],
        public ?string $conversionDestination = null,
        public ?string $validActions = null,
        public bool $isTopOfFunnel = false,
        public ?string $parentBrand = null,
        public ?string $originFacts = null,
        public ?string $convertArc = null,
        public ?string $extra = null,
    ) {}

    public function toPrompt(): string
    {
        $lines = [];
        $lines[] = 'Construye un personaje de marca completo (las 9 secciones) para esta marca, siguiendo el framework paso a paso.';

        if (filled($this->desiredName)) {
            $lines[] = '';
            $lines[] = "Nombre deseado del personaje: {$this->desiredName} (respétalo).";
        }

        $brand = array_filter([
            'Marca' => $this->brandName,
            'Descripción' => $this->brandDescription,
            'Promesa principal' => $this->brandPromise,
            'Oferta(s) principal(es)' => $this->mainOffers,
            'Perfil del cliente ideal' => $this->idealCustomerProfile,
        ], fn ($v) => filled($v));

        if (filled($brand)) {
            $lines[] = '';
            $lines[] = 'INSUMO · Marca:';
            foreach ($brand as $label => $value) {
                $lines[] = "- {$label}: {$value}";
            }
        }

        if (filled($this->audience)) {
            $lines[] = '';
            $lines[] = 'INSUMO · Audiencia objetivo y motivos de entrada (deseos/dolores por los que llegarían):';
            foreach ($this->audience as $a) {
                $lines[] = "- {$a}";
            }
        }

        $conv = array_filter([
            'Destino de conversión' => $this->conversionDestination,
            'Acciones/CTAs reales del destino (no inventar otras)' => $this->validActions,
        ], fn ($v) => filled($v));

        if (filled($conv)) {
            $lines[] = '';
            $lines[] = 'INSUMO · Oferta / destino de conversión:';
            foreach ($conv as $label => $value) {
                $lines[] = "- {$label}: {$value}";
            }
        }

        if ($this->isTopOfFunnel) {
            $lines[] = '';
            $lines[] = 'ARQUITECTURA · Esta marca personal es TOP-OF-FUNNEL de otra marca'
                .(filled($this->parentBrand) ? " ({$this->parentBrand})" : '').'. '
                .'Hereda SOLO la descripción fiel del destino y los CTAs reales; NO heredes su voz, tono, '
                .'identidad visual ni guardrails corporativos (el personaje debe ser viral, no un brandbook).';
        }

        if (filled($this->originFacts)) {
            $lines[] = '';
            $lines[] = 'INSUMO · Hechos reales para la historia de origen (NO inventes: úsalos y marca con [HUECO: ...] lo que falte):';
            $lines[] = $this->originFacts;
        }

        if (filled($this->convertArc)) {
            $lines[] = '';
            $lines[] = "INSUMO · Arco de origen (¿sufría el problema o lo causaba sin saberlo?): {$this->convertArc}. "
                .'Si es un arco de "converso" (estaba del lado que causaba el problema), aprovéchalo: da más autoridad que el arco de víctima.';
        }

        if (filled($this->extra)) {
            $lines[] = '';
            $lines[] = 'Instrucciones adicionales del usuario (priorízalas):';
            $lines[] = $this->extra;
        }

        return implode("\n", $lines);
    }
}
