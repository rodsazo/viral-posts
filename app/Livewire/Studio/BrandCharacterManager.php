<?php

namespace App\Livewire\Studio;

use App\Models\Account;
use App\Models\BrandCharacter;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * CRUD de Personajes de Marca en el Estudio: lista a la izquierda, editor de las 9 secciones
 * a la derecha con autoguardado. Los campos de lista (posturas, enemigos, props, CTAs, reglas)
 * se editan como filas repetibles. El chat de refinamiento vive en el trait CharacterRefining.
 */
#[Layout('components.layouts.studio')]
class BrandCharacterManager extends Component
{
    use Concerns\RefinesCharacter;

    /** Campos escalares del personaje que se autoguardan al editar. */
    private const SCALAR_FIELDS = [
        'name', 'essence', 'promise_line', 'archetype', 'archetype_why', 'authority_source',
        'enemy_abstract', 'polarization_rule', 'origin_full', 'origin_reel', 'origin_oneliner',
        'voice_tone', 'voice_jargon', 'voice_rhythm', 'voice_humor', 'verbal_signature',
        'visual_principle', 'visual_outfit', 'visual_look', 'visual_environment',
        'conversion_destination', 'conversion_chain',
    ];

    /** Campos de lista de strings simples. */
    private const STRING_LISTS = ['enemies_concrete', 'production_formats', 'valid_ctas', 'coherence_rules'];

    public Account $account;

    public ?int $selectedId = null;

    // Escalares (nombres = columnas, para mapear directo en save()).
    public string $name = '';

    public ?string $essence = null;

    public ?string $promise_line = null;

    public ?string $archetype = null;

    public ?string $archetype_why = null;

    public ?string $authority_source = null;

    public ?string $enemy_abstract = null;

    public ?string $polarization_rule = null;

    public ?string $origin_full = null;

    public ?string $origin_reel = null;

    public ?string $origin_oneliner = null;

    public ?string $voice_tone = null;

    public ?string $voice_jargon = null;

    public ?string $voice_rhythm = null;

    public ?string $voice_humor = null;

    public ?string $verbal_signature = null;

    public ?string $visual_principle = null;

    public ?string $visual_outfit = null;

    public ?string $visual_look = null;

    public ?string $visual_environment = null;

    public ?string $conversion_destination = null;

    public ?string $conversion_chain = null;

    // Listas.
    /** @var array<int, string> */
    public array $enemies_concrete = [];

    /** @var array<int, string> */
    public array $production_formats = [];

    /** @var array<int, string> */
    public array $valid_ctas = [];

    /** @var array<int, string> */
    public array $coherence_rules = [];

    /** @var array<int, array{statement: string, why: string, kind: string, bridge: bool}> */
    public array $postures = [];

    /** @var array<int, array{description: string, moment: string}> */
    public array $visual_props = [];

    public bool $saved = false;

    public function mount(Account $account): void
    {
        $this->account = $account;

        $requested = request()->integer('character');
        $character = $requested ? $this->account->brandCharacters()->find($requested) : null;

        if ($character !== null) {
            $this->loadCharacter($character);
        }
    }

    public function newCharacter(): void
    {
        $character = $this->account->brandCharacters()->create(['name' => 'Nuevo personaje']);
        $this->loadCharacter($character);
    }

    public function selectCharacter(int $id): void
    {
        $character = $this->account->brandCharacters()->find($id);

        if ($character !== null) {
            $this->loadCharacter($character);
        }
    }

    public function deleteCharacter(int $id): void
    {
        if (! $this->canDelete()) {
            return;
        }

        $this->account->brandCharacters()->whereKey($id)->delete();

        if ($this->selectedId === $id) {
            $this->selectedId = null;
            $next = $this->account->brandCharacters()->orderBy('name')->first();

            if ($next !== null) {
                $this->loadCharacter($next);
            }
        }
    }

    private function loadCharacter(BrandCharacter $character): void
    {
        $this->selectedId = $character->id;

        foreach (self::SCALAR_FIELDS as $field) {
            $this->{$field} = $character->{$field};
        }

        $this->name = $character->name;
        $this->enemies_concrete = array_values($character->enemies_concrete ?? []);
        $this->production_formats = array_values($character->production_formats ?? []);
        $this->valid_ctas = array_values($character->valid_ctas ?? []);
        $this->coherence_rules = array_values($character->coherence_rules ?? []);
        $this->postures = array_values($character->postures ?? []);
        $this->visual_props = array_values($character->visual_props ?? []);

        $this->saved = false;
        $this->resetRefineState();
    }

    public function updated(string $name): void
    {
        if ($this->selectedId === null) {
            return;
        }

        // El chat de refinamiento gestiona sus propias propiedades.
        if (str_starts_with($name, 'refine')) {
            return;
        }

        $this->save();
    }

    public function save(): void
    {
        $character = $this->currentCharacter();

        if ($character === null) {
            return;
        }

        $data = [];
        foreach (self::SCALAR_FIELDS as $field) {
            $data[$field] = $field === 'name'
                ? (trim((string) $this->name) ?: 'Sin nombre')
                : ($this->{$field} ?: null);
        }

        foreach (self::STRING_LISTS as $list) {
            $data[$list] = $this->cleanStrings($this->{$list});
        }

        $data['postures'] = $this->cleanPostures();
        $data['visual_props'] = $this->cleanProps();

        $character->update($data);

        $this->saved = true;
    }

    // ── Manejo de listas ───────────────────────────────────────────────────────────

    public function addString(string $list): void
    {
        if (! in_array($list, self::STRING_LISTS, true)) {
            return;
        }

        $this->{$list}[] = '';
    }

    public function removeString(string $list, int $index): void
    {
        if (! in_array($list, self::STRING_LISTS, true)) {
            return;
        }

        unset($this->{$list}[$index]);
        $this->{$list} = array_values($this->{$list});
        $this->save();
    }

    public function addPosture(): void
    {
        $this->postures[] = ['statement' => '', 'why' => '', 'kind' => 'principal', 'bridge' => false];
    }

    public function removePosture(int $index): void
    {
        unset($this->postures[$index]);
        $this->postures = array_values($this->postures);
        $this->save();
    }

    public function addProp(): void
    {
        $this->visual_props[] = ['description' => '', 'moment' => 'durante'];
    }

    public function removeProp(int $index): void
    {
        unset($this->visual_props[$index]);
        $this->visual_props = array_values($this->visual_props);
        $this->save();
    }

    /**
     * @param  array<int, string>  $items
     * @return array<int, string>
     */
    private function cleanStrings(array $items): array
    {
        return array_values(array_filter(array_map(fn ($v): string => trim((string) $v), $items), fn (string $v): bool => $v !== ''));
    }

    /** @return array<int, array{statement: string, why: string, kind: string, bridge: bool}> */
    private function cleanPostures(): array
    {
        return array_values(array_filter(array_map(fn ($p): array => [
            'statement' => trim((string) ($p['statement'] ?? '')),
            'why' => trim((string) ($p['why'] ?? '')),
            'kind' => in_array($p['kind'] ?? '', ['principal', 'secundaria'], true) ? $p['kind'] : 'principal',
            'bridge' => (bool) ($p['bridge'] ?? false),
        ], $this->postures), fn (array $p): bool => $p['statement'] !== ''));
    }

    /** @return array<int, array{description: string, moment: string}> */
    private function cleanProps(): array
    {
        return array_values(array_filter(array_map(fn ($p): array => [
            'description' => trim((string) ($p['description'] ?? '')),
            'moment' => in_array($p['moment'] ?? '', ['durante', 'fondo', 'cierre'], true) ? $p['moment'] : 'durante',
        ], $this->visual_props), fn (array $p): bool => $p['description'] !== ''));
    }

    private function currentCharacter(): ?BrandCharacter
    {
        return $this->selectedId === null ? null : $this->account->brandCharacters()->find($this->selectedId);
    }

    public function canDelete(): bool
    {
        return auth()->user()->isAdminOf($this->account);
    }

    public function render(): View
    {
        return view('livewire.studio.brand-character-manager', [
            'characters' => $this->account->brandCharacters()->orderBy('name')->get(),
        ]);
    }
}
