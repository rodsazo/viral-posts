<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Jobs\RefinePieceJob;
use App\Livewire\Studio\PieceComposer;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\ContentPiece;
use App\Models\PieceRefinement;
use App\Models\User;
use App\Support\Ai\RefineContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Chat de refinamiento conversacional del guión (estilo Claude): el creador da
 * instrucciones y la IA propone versiones que solo se aplican al elegirlas.
 */
class RefinementTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    private function withAi(): void
    {
        config(['services.anthropic.key' => 'test-key']);
    }

    public function test_sending_an_instruction_persists_the_user_turn_and_queues_the_ai(): void
    {
        Queue::fake();
        $this->withAi();

        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('refineInstruction', 'más corto')
            ->call('sendRefinement')
            ->assertSet('refineInstruction', '')       // se limpia el input
            ->assertSet('refineGenerationId', fn ($v) => $v !== null); // hay generación en curso

        $this->assertDatabaseHas('piece_refinements', [
            'content_piece_id' => $piece->id,
            'role' => PieceRefinement::ROLE_USER,
            'body' => 'más corto',
        ]);
        $this->assertDatabaseHas('ai_generations', ['kind' => 'refine', 'status' => 'processing']);
        Queue::assertPushed(RefinePieceJob::class);
    }

    public function test_polling_a_done_generation_appends_the_assistant_message(): void
    {
        Queue::fake();
        $this->withAi();

        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        $component = Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('refineInstruction', 'más cálido')
            ->call('sendRefinement');

        // El job está encolado (fake): completamos su resultado a mano y sondeamos.
        $generation = AiGeneration::where('kind', 'refine')->latest('id')->firstOrFail();
        $generation->update([
            'status' => AiGeneration::STATUS_DONE,
            'result' => [
                'note' => 'Lo hice más cálido',
                'fields' => ['hook' => 'H2', 'story' => 'S2', 'moral' => 'M2', 'cta' => 'C2'],
            ],
        ]);

        $component->call('pollRefinement')
            ->assertSet('refineGenerationId', null);

        $this->assertDatabaseHas('piece_refinements', [
            'content_piece_id' => $piece->id,
            'role' => PieceRefinement::ROLE_ASSISTANT,
            'body' => 'Lo hice más cálido',
        ]);
    }

    public function test_applying_a_proposal_writes_it_to_the_piece(): void
    {
        $this->withAi();

        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'hook' => 'viejo']);
        $refinement = PieceRefinement::create([
            'content_piece_id' => $piece->id,
            'role' => PieceRefinement::ROLE_ASSISTANT,
            'body' => 'nota',
            'proposal' => ['hook' => 'NUEVO', 'story' => 'HISTORIA', 'moral' => 'MORAL', 'cta' => 'CTA'],
        ]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->call('applyRefinement', $refinement->id);

        $piece->refresh();
        $this->assertSame('NUEVO', $piece->hook);
        $this->assertSame('HISTORIA', $piece->story);
        $this->assertSame('MORAL', $piece->moral);
        $this->assertSame('CTA', $piece->cta);
    }

    public function test_reset_clears_the_thread(): void
    {
        $this->withAi();

        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        PieceRefinement::create(['content_piece_id' => $piece->id, 'role' => 'user', 'body' => 'algo']);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->call('resetRefinements');

        $this->assertDatabaseMissing('piece_refinements', ['content_piece_id' => $piece->id]);
    }

    public function test_deleting_a_piece_cascades_its_refinements(): void
    {
        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        $refinement = PieceRefinement::create(['content_piece_id' => $piece->id, 'role' => 'user', 'body' => 'algo']);

        $piece->delete();

        $this->assertDatabaseMissing('piece_refinements', ['id' => $refinement->id]);
    }

    public function test_refine_context_builds_the_conversation_messages_in_order(): void
    {
        $context = new RefineContext(
            instruction: 'más corto',
            baseHook: 'H0',
            baseStory: 'S0',
            baseMoral: 'M0',
            baseCta: 'C0',
            history: [
                ['role' => 'user', 'body' => 'más cálido', 'proposal' => null],
                ['role' => 'assistant', 'body' => 'Hecho', 'proposal' => ['hook' => 'H1', 'story' => 'S1', 'moral' => 'M1', 'cta' => 'C1']],
            ],
        );

        $messages = $context->toMessages();

        $this->assertCount(3, $messages);
        $this->assertSame('user', $messages[0]['role']);
        $this->assertSame('más cálido', $messages[0]['content']);
        $this->assertSame('assistant', $messages[1]['role']);
        $this->assertStringContainsString('H1', $messages[1]['content']);
        $this->assertSame('user', $messages[2]['role']);
        $this->assertSame('más corto', $messages[2]['content']); // la nueva instrucción cierra el hilo
    }

    public function test_refine_context_system_carries_brand_and_base_draft(): void
    {
        $context = new RefineContext(
            instruction: 'más corto',
            brandPromise: 'PROMESA DE MARCA',
            baseHook: 'GANCHO BASE',
        );

        $system = $context->toSystem();

        $this->assertStringContainsString('PROMESA DE MARCA', $system);
        $this->assertStringContainsString('GANCHO BASE', $system);
        $this->assertStringContainsString('BORRADOR DE PARTIDA', $system);
    }
}
