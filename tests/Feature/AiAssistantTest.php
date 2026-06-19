<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\TeamRole;
use App\Enums\ViralMechanism;
use App\Jobs\GenerateSuggestionsJob;
use App\Livewire\Studio\IdeaGenerator;
use App\Livewire\Studio\PieceComposer;
use App\Livewire\Studio\PieceGenerator;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\Belief;
use App\Models\ContentPiece;
use App\Models\HerasTemplate;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use App\Models\WinningIdea;
use App\Support\Ai\ContentAssistant;
use App\Support\Ai\IdeaContext;
use App\Support\Ai\ScriptContext;
use App\Support\Ai\Suggestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Tests\TestCase;

class AiAssistantTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Editor->value]);

        return $user;
    }

    public function test_is_configured_reflects_the_api_key(): void
    {
        config(['services.anthropic.key' => null]);
        $this->assertFalse(app(ContentAssistant::class)->isConfigured());

        config(['services.anthropic.key' => 'sk-ant-test']);
        $this->assertTrue(app(ContentAssistant::class)->isConfigured());
    }

    public function test_script_context_from_idea_includes_questions_and_beliefs(): void
    {
        $account = Account::factory()->create();
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $question = Question::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'body' => 'PREGUNTA CLAVE',
        ]);
        $belief = Belief::factory()->create([
            'account_id' => $account->id,
            'type' => BeliefType::Truth,
            'statement' => 'VERDAD CLAVE',
        ]);
        $question->beliefs()->attach($belief->id);

        $idea = WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA X']);
        $idea->questions()->attach($question->id);

        $context = ScriptContext::fromIdea($idea->load('questions.beliefs'));
        $prompt = $context->toPrompt();

        $this->assertStringContainsString('IDEA X', $prompt);
        $this->assertStringContainsString('PREGUNTA CLAVE', $prompt);
        $this->assertStringContainsString('VERDAD CLAVE', $prompt);
        $this->assertTrue($context->hasMaterial());
    }

    public function test_idea_context_prompt_includes_questions_and_beliefs(): void
    {
        $context = new IdeaContext(
            questions: ['¿PREGUNTA UNO?'],
            beliefs: ['[Mito] CREENCIA FALSA'],
            draftTitle: 'BORRADOR',
        );

        $prompt = $context->toPrompt();

        $this->assertStringContainsString('¿PREGUNTA UNO?', $prompt);
        $this->assertStringContainsString('CREENCIA FALSA', $prompt);
        $this->assertStringContainsString('BORRADOR', $prompt);
        $this->assertTrue($context->hasMaterial());
        $this->assertFalse((new IdeaContext)->hasMaterial());
    }

    public function test_generating_script_enqueues_a_job_without_rewriting(): void
    {
        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'hook' => 'GANCHO ORIGINAL']);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->set('aiBrief', 'Tono cercano')
            ->call('generateScriptSuggestions')
            ->assertSet('scriptSuggestions', []);

        Queue::assertPushed(GenerateSuggestionsJob::class);
        $this->assertDatabaseHas('ai_generations', [
            'account_id' => $account->id,
            'kind' => 'script',
            'status' => 'processing',
        ]);

        // Pedir sugerencias no reescribe el guión.
        $this->assertSame('GANCHO ORIGINAL', $piece->refresh()->hook);
    }

    public function test_poll_script_loads_suggestions_when_job_is_done(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        ContentPiece::factory()->create(['account_id' => $account->id]);

        $generation = AiGeneration::create([
            'account_id' => $account->id,
            'kind' => 'script',
            'status' => AiGeneration::STATUS_DONE,
            'result' => [
                ['label' => 'Variante 1', 'fields' => ['hook' => 'H', 'story' => 'S', 'moral' => 'M', 'cta' => 'C'], 'preview' => 'p'],
            ],
        ]);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->set('scriptGenerationId', $generation->id)
            ->call('pollScript')
            ->assertCount('scriptSuggestions', 1)
            ->assertSet('scriptGenerationId', null);
    }

    public function test_job_runs_assistant_and_stores_result(): void
    {
        $account = Account::factory()->create();
        $generation = AiGeneration::create([
            'account_id' => $account->id,
            'kind' => 'script',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        $this->mock(ContentAssistant::class, function ($mock): void {
            $mock->shouldReceive('suggestScripts')->once()->andReturn([
                new Suggestion('V1', ['hook' => 'H', 'story' => 'S', 'moral' => 'M', 'cta' => 'C'], 'p'),
            ]);
        });

        (new GenerateSuggestionsJob($generation->id, new ScriptContext, 3))->handle(app(ContentAssistant::class));

        $generation->refresh();
        $this->assertSame(AiGeneration::STATUS_DONE, $generation->status);
        $this->assertCount(1, $generation->result);
    }

    public function test_job_marks_failed_on_error(): void
    {
        $account = Account::factory()->create();
        $generation = AiGeneration::create([
            'account_id' => $account->id,
            'kind' => 'script',
            'status' => AiGeneration::STATUS_PROCESSING,
        ]);

        $this->mock(ContentAssistant::class, function ($mock): void {
            $mock->shouldReceive('suggestScripts')->andThrow(new \RuntimeException('boom'));
        });

        (new GenerateSuggestionsJob($generation->id, new ScriptContext, 3))->handle(app(ContentAssistant::class));

        $this->assertSame(AiGeneration::STATUS_FAILED, $generation->refresh()->status);
    }

    public function test_applying_a_suggestion_rewrites_several_fields_and_saves(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->set('scriptSuggestions', [
                ['label' => 'Variante 1', 'fields' => ['hook' => 'NUEVO GANCHO', 'story' => 'NUEVA HISTORIA', 'moral' => 'NUEVA MORAL', 'cta' => 'NUEVO CTA'], 'preview' => 'p'],
            ])
            ->call('applyScriptSuggestion', 0)
            ->assertSet('hookText', 'NUEVO GANCHO')
            ->assertSet('story', 'NUEVA HISTORIA');

        $piece->refresh();
        $this->assertSame('NUEVO GANCHO', $piece->hook);
        $this->assertSame('NUEVA HISTORIA', $piece->story);
        $this->assertSame('NUEVA MORAL', $piece->moral);
        $this->assertSame('NUEVO CTA', $piece->cta);
    }

    public function test_generator_requires_membership(): void
    {
        $account = Account::factory()->create();
        $outsider = User::factory()->create();

        $this->actingAs($outsider)
            ->get("/studio/{$account->slug}/generador")
            ->assertForbidden();
    }

    public function test_generator_enqueues_a_job_when_generating(): void
    {
        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('instructions', 'tono cercano')
            ->call('generate')
            ->assertSet('suggestions', []);

        Queue::assertPushed(GenerateSuggestionsJob::class);
        $this->assertDatabaseHas('ai_generations', ['account_id' => $account->id, 'kind' => 'script', 'status' => 'processing']);
        // Generar no crea piezas todavía.
        $this->assertSame(0, ContentPiece::where('account_id', $account->id)->count());
    }

    public function test_generator_sends_manually_chosen_questions_and_beliefs(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $q1 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA ELEGIDA']);
        $q2 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA NO ELEGIDA']);
        $belief = Belief::factory()->create(['account_id' => $account->id, 'type' => BeliefType::Myth, 'statement' => 'MITO ELEGIDO']);
        $q1->beliefs()->attach($belief->id);

        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('idealFollowerId', $follower->id)
            ->set('questionIds', [$q1->id])
            ->set('beliefIds', [$belief->id])
            ->call('generate');

        // El contexto que se encola lleva solo lo elegido manualmente.
        Queue::assertPushed(GenerateSuggestionsJob::class, function (GenerateSuggestionsJob $job): bool {
            return $job->context instanceof ScriptContext
                && $job->context->questions === ['PREGUNTA ELEGIDA']
                && $job->context->beliefs === ['[Mito] MITO ELEGIDO'];
        });
    }

    public function test_generator_creates_one_piece_per_selected_suggestion(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $idea = WinningIdea::factory()->create(['account_id' => $account->id, 'title' => 'IDEA BASE']);

        $objective = ContentObjective::cases()[0];
        $format = ContentFormat::cases()[0];

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('winning_idea_id', $idea->id)
            ->set('objective', $objective->value)
            ->set('format', $format->value)
            ->set('suggestions', [
                ['label' => 'V1', 'fields' => ['hook' => 'GANCHO A', 'story' => 'S A', 'moral' => 'M A', 'cta' => 'C A'], 'preview' => 'a'],
                ['label' => 'V2', 'fields' => ['hook' => 'GANCHO B', 'story' => 'S B', 'moral' => 'M B', 'cta' => 'C B'], 'preview' => 'b'],
                ['label' => 'V3', 'fields' => ['hook' => 'GANCHO C', 'story' => 'S C', 'moral' => 'M C', 'cta' => 'C C'], 'preview' => 'c'],
            ])
            ->set('selected', [0, 2])
            ->call('createPieces')
            ->assertRedirect(route('studio.pieces', $account));

        $pieces = ContentPiece::where('account_id', $account->id)->get();
        $this->assertCount(2, $pieces);

        $hooks = $pieces->pluck('hook')->all();
        sort($hooks);
        $this->assertSame(['GANCHO A', 'GANCHO C'], $hooks);

        $first = $pieces->first();
        $this->assertSame($idea->id, $first->winning_idea_id);
        $this->assertSame($objective, $first->objective);
        $this->assertSame($format, $first->format);
    }

    public function test_idea_generator_requires_membership(): void
    {
        $account = Account::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get("/studio/{$account->slug}/ideas")
            ->assertForbidden();
    }

    public function test_idea_generator_enqueues_an_idea_job_with_chosen_context(): void
    {
        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $question = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA X']);
        $belief = Belief::factory()->create(['account_id' => $account->id, 'type' => BeliefType::Myth, 'statement' => 'CREENCIA X']);
        $question->beliefs()->attach($belief->id);

        Livewire::test(IdeaGenerator::class, ['account' => $account])
            ->set('idealFollowerId', $follower->id)
            ->set('questionIds', [$question->id])
            ->set('beliefIds', [$belief->id])
            ->call('generate')
            ->assertSet('suggestions', []);

        Queue::assertPushed(GenerateSuggestionsJob::class, function (GenerateSuggestionsJob $job): bool {
            return $job->context instanceof IdeaContext
                && $job->context->questions === ['PREGUNTA X']
                && $job->context->beliefs === ['[Mito] CREENCIA X'];
        });

        $this->assertDatabaseHas('ai_generations', ['account_id' => $account->id, 'kind' => 'idea', 'status' => 'processing']);
    }

    public function test_idea_generator_creates_winning_ideas_and_links_questions(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $question = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);

        Livewire::test(IdeaGenerator::class, ['account' => $account])
            ->set('idealFollowerId', $follower->id)
            ->set('questionIds', [$question->id])
            ->set('suggestions', [
                ['label' => 'Idea 1', 'fields' => ['title' => 'IDEA NUEVA', 'concept' => 'CONCEPTO', 'viral_mechanism' => 'curiosidad'], 'preview' => 'p'],
                ['label' => 'Idea 2', 'fields' => ['title' => 'OTRA IDEA', 'concept' => 'CONCEPTO 2'], 'preview' => 'p2'],
            ])
            ->set('selected', [0])
            ->call('createIdeas')
            ->assertRedirect(route('studio.generator', $account));

        $idea = WinningIdea::where('account_id', $account->id)->where('title', 'IDEA NUEVA')->first();
        $this->assertNotNull($idea);
        $this->assertSame('CONCEPTO', $idea->concept);
        $this->assertSame(ViralMechanism::Curiosidad, $idea->viral_mechanism);
        $this->assertTrue($idea->questions->contains($question->id));

        // Solo se creó la idea seleccionada.
        $this->assertSame(1, WinningIdea::where('account_id', $account->id)->count());
    }

    public function test_template_lines_include_structure_and_skip_empty(): void
    {
        $full = HerasTemplate::factory()->create([
            'number' => 1,
            'name' => 'Antes y después',
            'structure' => 'Muestra el antes, el giro y el después.',
            'suggested_format' => 'Reel',
        ]);

        $lines = ScriptContext::templateLines([$full]);

        $this->assertCount(1, $lines);
        $this->assertStringContainsString('Antes y después', $lines[0]);
        $this->assertStringContainsString('Muestra el antes', $lines[0]);
    }

    public function test_suggest_script_does_nothing_when_not_configured(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        ContentPiece::factory()->create(['account_id' => $account->id]);

        $this->mock(ContentAssistant::class, function ($mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(false);
            $mock->shouldNotReceive('suggestScripts');
        });

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('generateScriptSuggestions')
            ->assertCount('scriptSuggestions', 0);
    }
}
