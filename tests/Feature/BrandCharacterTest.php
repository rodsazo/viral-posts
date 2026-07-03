<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Jobs\GenerateCharacterJob;
use App\Jobs\RefineCharacterJob;
use App\Livewire\Studio\BrandCharacterManager;
use App\Livewire\Studio\CharacterGenerator;
use App\Livewire\Studio\PieceComposer;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\BrandCharacter;
use App\Models\CharacterRefinement;
use App\Models\ContentPiece;
use App\Models\User;
use App\Support\Ai\ScriptContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class BrandCharacterTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    public function test_manager_renders_for_a_member(): void
    {
        $account = Account::factory()->create();
        BrandCharacter::factory()->create(['account_id' => $account->id, 'name' => 'EL ROD']);

        $this->actingAs($this->member($account))
            ->get("/studio/{$account->slug}/marca/personajes")
            ->assertOk()
            ->assertSee('EL ROD');
    }

    public function test_generator_renders_for_a_member(): void
    {
        $account = Account::factory()->create();

        $this->actingAs($this->member($account))
            ->get("/studio/{$account->slug}/marca/generador-personajes")
            ->assertOk()
            ->assertSee('Generador de Personajes');
    }

    public function test_non_member_cannot_access(): void
    {
        $account = Account::factory()->create();
        $this->actingAs(User::factory()->create())
            ->get("/studio/{$account->slug}/marca/personajes")
            ->assertForbidden();
    }

    public function test_editing_persists_scalars_and_lists(): void
    {
        $account = Account::factory()->create();
        $character = BrandCharacter::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        Livewire::test(BrandCharacterManager::class, ['account' => $account])
            ->call('selectCharacter', $character->id)
            ->set('name', 'El Rod')
            ->set('archetype', 'El amigo que ya jugó')
            ->call('addString', 'coherence_rules')
            ->set('coherence_rules.0', 'El novato nunca es blanco del humor')
            ->call('addPosture')
            ->set('postures.0.statement', 'No necesitas estudiar nada')
            ->set('postures.0.kind', 'principal')
            ->call('save');

        $character->refresh();
        $this->assertSame('El Rod', $character->name);
        $this->assertSame('El amigo que ya jugó', $character->archetype);
        $this->assertContains('El novato nunca es blanco del humor', $character->coherence_rules);
        $this->assertSame('No necesitas estudiar nada', $character->postures[0]['statement']);
    }

    public function test_empty_list_rows_are_dropped_on_save(): void
    {
        $account = Account::factory()->create();
        $character = BrandCharacter::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        Livewire::test(BrandCharacterManager::class, ['account' => $account])
            ->call('selectCharacter', $character->id)
            ->call('addString', 'valid_ctas') // fila vacía
            ->call('save');

        $this->assertSame([], $character->refresh()->valid_ctas);
    }

    public function test_delete_is_admin_only(): void
    {
        $account = Account::factory()->create();
        $character = BrandCharacter::factory()->create(['account_id' => $account->id]);

        // Editor: no borra.
        $this->actingAs($this->member($account, TeamRole::Editor));
        Livewire::test(BrandCharacterManager::class, ['account' => $account])
            ->call('selectCharacter', $character->id)
            ->call('deleteCharacter', $character->id);
        $this->assertDatabaseHas('brand_characters', ['id' => $character->id]);

        // Admin: sí borra.
        $this->actingAs($this->member($account, TeamRole::Admin));
        Livewire::test(BrandCharacterManager::class, ['account' => $account])
            ->call('deleteCharacter', $character->id);
        $this->assertDatabaseMissing('brand_characters', ['id' => $character->id]);
    }

    public function test_generator_queues_the_ai_and_creates_the_entity_on_poll(): void
    {
        Queue::fake();
        config(['services.anthropic.key' => 'test-key']);

        $account = Account::factory()->create(['brand_promise' => 'Ayudamos a empezar sin saber nada']);
        $this->actingAs($this->member($account));

        $component = Livewire::test(CharacterGenerator::class, ['account' => $account])
            ->set('desiredName', 'El Rod')
            ->set('conversionDestination', 'MesasRoleras.com')
            ->call('generate')
            ->assertSet('generationId', fn ($v) => $v !== null);

        $this->assertDatabaseHas('ai_generations', ['kind' => 'character', 'status' => 'processing']);
        Queue::assertPushed(GenerateCharacterJob::class);

        // Completamos el job (fake) y sondeamos: debe crear el personaje.
        $generation = AiGeneration::where('kind', 'character')->latest('id')->firstOrFail();
        $generation->update([
            'status' => AiGeneration::STATUS_DONE,
            'result' => ['essence' => 'El amigo que ya jugó', 'archetype' => 'El par cercano', 'name' => 'ignorado'],
        ]);

        $component->call('pollGeneration');

        $character = $account->brandCharacters()->first();
        $this->assertNotNull($character);
        $this->assertSame('El Rod', $character->name); // el nombre deseado gana
        $this->assertSame('El amigo que ya jugó', $character->essence);
        $this->assertSame('MesasRoleras.com', $character->generation_inputs['conversion_destination']);
    }

    public function test_character_injects_into_the_script_context(): void
    {
        $character = BrandCharacter::factory()->make([
            'name' => 'El Rod',
            'archetype' => 'El amigo que ya jugó',
        ]);

        $context = new ScriptContext(characterContext: $character->toPromptContext());

        $this->assertStringContainsString('PERSONAJE DE MARCA «El Rod»', $context->toPrompt());
        $this->assertStringContainsString('El amigo que ya jugó', $context->toPrompt());
    }

    public function test_composer_remembers_the_selected_character(): void
    {
        $account = Account::factory()->create();
        $character = BrandCharacter::factory()->create(['account_id' => $account->id]);
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('brandCharacterId', $character->id);

        $this->assertSame($character->id, $piece->refresh()->brand_character_id);
    }

    public function test_full_document_includes_the_sections(): void
    {
        $character = BrandCharacter::factory()->make([
            'name' => 'El Rod',
            'enemy_abstract' => 'El gatekeeping rolero',
        ]);

        $doc = $character->toFullDocument();

        $this->assertStringContainsString('# Personaje de Marca: El Rod', $doc);
        $this->assertStringContainsString('El gatekeeping rolero', $doc);
        $this->assertStringContainsString('## 9 · Reglas de coherencia', $doc);
    }

    public function test_character_refinement_flow(): void
    {
        Queue::fake();
        config(['services.anthropic.key' => 'test-key']);

        $account = Account::factory()->create();
        $character = BrandCharacter::factory()->create(['account_id' => $account->id, 'essence' => 'vieja']);
        $this->actingAs($this->member($account));

        $component = Livewire::test(BrandCharacterManager::class, ['account' => $account])
            ->call('selectCharacter', $character->id)
            ->set('refineInstruction', 'cambia el enemigo')
            ->call('sendCharacterRefinement')
            ->assertSet('refineGenerationId', fn ($v) => $v !== null);

        $this->assertDatabaseHas('character_refinements', [
            'brand_character_id' => $character->id, 'role' => 'user', 'body' => 'cambia el enemigo',
        ]);
        Queue::assertPushed(RefineCharacterJob::class);

        $generation = AiGeneration::where('kind', 'character-refine')->latest('id')->firstOrFail();
        $generation->update([
            'status' => AiGeneration::STATUS_DONE,
            'result' => ['note' => 'Cambié el enemigo', 'fields' => ['essence' => 'nueva esencia', 'name' => 'no-usar']],
        ]);

        $component->call('pollCharacterRefinement')->assertSet('refineGenerationId', null);

        $assistant = CharacterRefinement::where('role', 'assistant')->latest('id')->firstOrFail();
        $component->call('applyCharacterRefinement', $assistant->id);

        $character->refresh();
        $this->assertSame('nueva esencia', $character->essence);
        $this->assertNotSame('no-usar', $character->name); // el nombre no se renombra al aplicar
    }
}
