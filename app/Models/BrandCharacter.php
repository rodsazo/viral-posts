<?php

namespace App\Models;

use Database\Factories\BrandCharacterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Personaje de Marca: fuente de verdad sobre quién es la marca frente a cámara. Se genera
 * con IA a partir de la marca + insumos del usuario (framework de 9 secciones) y luego se
 * edita. Al generar contenido, un personaje elegido se inyecta en el contexto de la IA para
 * que ideas y guiones salgan "en personaje".
 */
class BrandCharacter extends Model
{
    /** @use HasFactory<BrandCharacterFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'name',
        'essence',
        'promise_line',
        'archetype',
        'archetype_why',
        'authority_source',
        'enemy_abstract',
        'enemies_concrete',
        'polarization_rule',
        'postures',
        'origin_full',
        'origin_reel',
        'origin_oneliner',
        'voice_tone',
        'voice_jargon',
        'voice_rhythm',
        'voice_humor',
        'verbal_signature',
        'visual_principle',
        'visual_outfit',
        'visual_look',
        'visual_environment',
        'visual_props',
        'production_formats',
        'conversion_destination',
        'conversion_chain',
        'valid_ctas',
        'coherence_rules',
        'generation_inputs',
    ];

    protected function casts(): array
    {
        return [
            'enemies_concrete' => 'array',
            'postures' => 'array',
            'visual_props' => 'array',
            'production_formats' => 'array',
            'valid_ctas' => 'array',
            'coherence_rules' => 'array',
            'generation_inputs' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Hilo de refinamiento (conversación con la IA) del personaje, en orden cronológico.
     *
     * @return HasMany<CharacterRefinement, $this>
     */
    public function refinements(): HasMany
    {
        return $this->hasMany(CharacterRefinement::class)->orderBy('id');
    }

    /**
     * Documento completo del personaje (las 9 secciones) en texto. Se usa como base del
     * chat de refinamiento y como "fuente de verdad" legible/exportable.
     */
    public function toFullDocument(): string
    {
        $lines = [];
        $add = function (string $label, ?string $value) use (&$lines): void {
            if (filled($value)) {
                $lines[] = "{$label}: {$value}";
            }
        };
        $list = function (string $label, ?array $items) use (&$lines): void {
            $items = array_values(array_filter((array) $items, fn ($v): bool => filled($v)));
            if (filled($items)) {
                $lines[] = $label.':';
                foreach ($items as $item) {
                    $lines[] = '- '.$item;
                }
            }
        };

        $lines[] = "# Personaje de Marca: {$this->name}";
        $add('Esencia', $this->essence);
        $add('Promesa', $this->promise_line);
        $lines[] = '';
        $lines[] = '## 1 · Arquetipo';
        $add('Arquetipo', $this->archetype);
        $add('Por qué', $this->archetype_why);
        $add('Fuente de autoridad', $this->authority_source);
        $lines[] = '';
        $lines[] = '## 2 · Enemigo';
        $add('Abstracto', $this->enemy_abstract);
        $list('Concretos', $this->enemies_concrete);
        $add('Regla de polarización', $this->polarization_rule);
        $lines[] = '';
        $lines[] = '## 3 · Posturas';
        foreach ($this->postures ?? [] as $p) {
            if (filled($p['statement'] ?? null)) {
                $tag = ($p['kind'] ?? 'principal') === 'principal' ? 'PRINCIPAL' : 'secundaria';
                $tag .= ! empty($p['bridge']) ? ', PUENTE' : '';
                $lines[] = "- [{$tag}] {$p['statement']}".(filled($p['why'] ?? null) ? " — {$p['why']}" : '');
            }
        }
        $lines[] = '';
        $lines[] = '## 4 · Historia de origen';
        $add('Completa', $this->origin_full);
        $add('Reel', $this->origin_reel);
        $add('Una frase', $this->origin_oneliner);
        $lines[] = '';
        $lines[] = '## 5 · Voz';
        $add('Tono', $this->voice_tone);
        $add('Jerga', $this->voice_jargon);
        $add('Ritmo', $this->voice_rhythm);
        $add('Humor', $this->voice_humor);
        $add('Firma verbal', $this->verbal_signature);
        $lines[] = '';
        $lines[] = '## 6 · Identidad visual';
        $add('Principio rector', $this->visual_principle);
        $add('Atuendo', $this->visual_outfit);
        $add('Look', $this->visual_look);
        $add('Entorno', $this->visual_environment);
        foreach ($this->visual_props ?? [] as $prop) {
            if (filled($prop['description'] ?? null)) {
                $lines[] = "- Prop [{$prop['moment']}]: {$prop['description']}";
            }
        }
        $lines[] = '';
        $lines[] = '## 7 · Formatos';
        $list('Formatos', $this->production_formats);
        $lines[] = '';
        $lines[] = '## 8 · Conversión';
        $add('Destino', $this->conversion_destination);
        $add('Cadena', $this->conversion_chain);
        $list('CTAs válidos', $this->valid_ctas);
        $lines[] = '';
        $lines[] = '## 9 · Reglas de coherencia';
        $list('Reglas', $this->coherence_rules);

        return implode("\n", $lines);
    }

    /**
     * Resumen compacto del personaje para inyectar en el contexto de la IA (generación de
     * ideas/guiones). No incluye todo el documento: lo esencial para mantener la coherencia
     * (arquetipo, enemigo, posturas, voz, guardrails).
     */
    public function toPromptContext(): string
    {
        $lines = [];
        $lines[] = "PERSONAJE DE MARCA «{$this->name}» (habla y piensa SIEMPRE como este personaje):";

        if (filled($this->essence)) {
            $lines[] = "- Esencia: {$this->essence}";
        }
        if (filled($this->archetype)) {
            $lines[] = "- Arquetipo: {$this->archetype}";
        }
        if (filled($this->enemy_abstract)) {
            $lines[] = "- Enemigo común: {$this->enemy_abstract}";
        }

        $mainPostures = collect($this->postures ?? [])
            ->filter(fn ($p): bool => ($p['kind'] ?? 'principal') === 'principal')
            ->map(fn ($p): string => (string) ($p['statement'] ?? ''))
            ->filter()
            ->all();

        if (filled($mainPostures)) {
            $lines[] = '- Posturas principales (defiéndelas):';
            foreach ($mainPostures as $p) {
                $lines[] = "  · {$p}";
            }
        }

        $voice = collect([
            'tono' => $this->voice_tone,
            'ritmo' => $this->voice_rhythm,
            'humor' => $this->voice_humor,
        ])->filter(fn ($v) => filled($v))->map(fn ($v, $k) => "{$k}: {$v}")->implode('; ');

        if ($voice !== '') {
            $lines[] = "- Voz: {$voice}";
        }
        if (filled($this->verbal_signature)) {
            $lines[] = "- Firma verbal de cierre: {$this->verbal_signature} (úsala solo si suena natural en la pieza; no la repitas mecánicamente)";
        }

        // Conversión: sin esto, la IA no sabe el nombre del destino ni sus features
        // reales y termina escribiendo [HUECO: nombre de la plataforma].
        if (filled($this->conversion_destination)) {
            $lines[] = "- Destino de conversión (nómbralo por su nombre): {$this->conversion_destination}";
        }

        if (filled($this->valid_ctas)) {
            $lines[] = '- Acciones/features REALES del destino (usa SOLO estas, no inventes otras):';
            foreach ($this->valid_ctas as $cta) {
                $lines[] = "  · {$cta}";
            }
        }

        if (filled($this->coherence_rules)) {
            $lines[] = '- Reglas de coherencia INVIOLABLES:';
            foreach ($this->coherence_rules as $rule) {
                $lines[] = "  · {$rule}";
            }
        }

        return implode("\n", $lines);
    }
}
