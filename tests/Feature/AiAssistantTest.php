<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\CtaCategory;
use App\Enums\PainType;
use App\Enums\TeamRole;
use App\Enums\ViralMechanism;
use App\Jobs\GenerateSuggestionsJob;
use App\Livewire\Studio\IdeaGenerator;
use App\Livewire\Studio\PieceComposer;
use App\Livewire\Studio\PieceGenerator;
use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\Belief;
use App\Models\ContentCta;
use App\Models\ContentPiece;
use App\Models\HerasTemplate;
use App\Models\HookTemplate;
use App\Models\IdealFollower;
use App\Models\Pain;
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

    public function test_ai_log_channel_is_a_dedicated_daily_file(): void
    {
        $channel = config('logging.channels.ai');

        $this->assertIsArray($channel);
        $this->assertSame('daily', $channel['driver']);
        $this->assertSame(storage_path('logs/ai.log'), $channel['path']);
        $this->assertNotSame(config('logging.channels.single.path'), $channel['path']);
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
        // La creencia cuelga DIRECTAMENTE del seguidor (el seguidor es el centro).
        Belief::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'type' => BeliefType::Truth,
            'statement' => 'VERDAD CLAVE',
        ]);

        $idea = WinningIdea::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'title' => 'IDEA X',
        ]);
        $idea->questions()->attach($question->id);

        $context = ScriptContext::fromIdea($idea->load('questions', 'idealFollower'));
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
            ->call('selectPiece', $piece->id)
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
            ->assertSet('scriptGenerationId', null)
            // Avisa al navegador para reproducir el sonido de "generación terminada".
            ->assertDispatched('ai-generation-done');
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
            ->call('selectPiece', $piece->id)
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

    public function test_generator_sends_manually_chosen_questions_beliefs_and_pains(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $q1 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA ELEGIDA']);
        $q2 = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'body' => 'PREGUNTA NO ELEGIDA']);
        // La creencia y el dolor cuelgan del seguidor directamente.
        $belief = Belief::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'type' => BeliefType::Myth, 'statement' => 'MITO ELEGIDO']);
        $pain = Pain::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'type' => PainType::Desire, 'body' => 'DESEO ELEGIDO']);

        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('idealFollowerId', $follower->id)
            ->set('questionIds', [$q1->id])
            ->set('beliefIds', [$belief->id])
            ->set('painIds', [$pain->id])
            ->call('generate');

        // El contexto que se encola lleva solo lo elegido manualmente, incluidos los dolores.
        Queue::assertPushed(GenerateSuggestionsJob::class, function (GenerateSuggestionsJob $job): bool {
            return $job->context instanceof ScriptContext
                && $job->context->questions === ['PREGUNTA ELEGIDA']
                && $job->context->beliefs === ['[Mito] MITO ELEGIDO']
                && $job->context->pains === ['[Deseo] DESEO ELEGIDO'];
        });
    }

    public function test_generator_lists_only_the_chosen_followers_ideas_with_piece_counts(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $followerA = IdealFollower::factory()->create(['account_id' => $account->id]);
        $followerB = IdealFollower::factory()->create(['account_id' => $account->id]);

        $ideaA = WinningIdea::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $followerA->id, 'title' => 'IDEA DEL SEGUIDOR A']);
        WinningIdea::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $followerB->id, 'title' => 'IDEA DEL SEGUIDOR B']);
        ContentPiece::factory()->count(2)->create(['account_id' => $account->id, 'winning_idea_id' => $ideaA->id]);

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('idealFollowerId', $followerA->id)
            ->assertSee('IDEA DEL SEGUIDOR A')
            ->assertSee('[2 piezas]')
            ->assertDontSee('IDEA DEL SEGUIDOR B');
    }

    public function test_generated_pieces_start_as_borrador(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('suggestions', [
                ['label' => 'Variante 1', 'fields' => ['hook' => 'H', 'story' => 'S', 'moral' => 'M', 'cta' => 'C'], 'preview' => 'p'],
            ])
            ->set('selected', [0])
            ->call('createPieces');

        $this->assertDatabaseHas('content_pieces', ['account_id' => $account->id, 'status' => 'borrador']);
    }

    public function test_script_context_prompt_includes_cta_instruction(): void
    {
        $context = new ScriptContext(ctas: ['CTA [Seguir] «Sígueme para más» — objetivo: invitar a SEGUIR la cuenta']);
        $prompt = $context->toPrompt();

        $this->assertStringContainsString('Llamada a la acción (CTA) obligatoria', $prompt);
        $this->assertStringContainsString('fluir de forma natural', $prompt);
        $this->assertStringContainsString('Sígueme para más', $prompt);
    }

    public function test_generator_sends_selected_cta_to_claude(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $cta = ContentCta::factory()->create([
            'account_id' => $account->id,
            'category' => CtaCategory::Keyword,
            'text' => 'Comenta GUIA y te la mando',
        ]);

        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('selectedCtaId', $cta->id)
            ->call('generate');

        Queue::assertPushed(GenerateSuggestionsJob::class, function (GenerateSuggestionsJob $job): bool {
            return $job->context instanceof ScriptContext
                && count($job->context->ctas) === 1
                && str_contains($job->context->ctas[0], 'Comenta GUIA y te la mando')
                && str_contains($job->context->ctas[0], 'Palabra clave');
        });
    }

    public function test_generator_only_lists_ctas_from_the_active_brand(): void
    {
        $account = Account::factory()->create();
        $other = Account::factory()->create();
        $this->actingAs($this->member($account));

        ContentCta::factory()->create(['account_id' => $account->id, 'text' => 'CTA PROPIA']);
        ContentCta::factory()->create(['account_id' => $other->id, 'text' => 'CTA AJENA']);

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->assertSee('CTA PROPIA')
            ->assertDontSee('CTA AJENA');
    }

    public function test_piece_generator_caps_hooks_at_five_and_can_remove(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $hooks = HookTemplate::factory()->count(7)->create();

        $component = Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('modalHookIds', $hooks->pluck('id')->all())
            ->call('confirmHooks')
            ->assertCount('selectedHookIds', 5);

        $first = (int) $component->get('selectedHookIds')[0];
        $component->call('removeHook', $first)->assertCount('selectedHookIds', 4);
    }

    public function test_piece_generator_sends_selected_hooks_to_claude(): void
    {
        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $hook = HookTemplate::factory()->create([
            'name' => 'GANCHO PRUEBA',
            'objective' => 'OBJETIVO X',
            'example_generic' => 'EJEMPLO GENERICO',
        ]);

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('modalHookIds', [$hook->id])
            ->call('confirmHooks')
            ->call('generate');

        Queue::assertPushed(GenerateSuggestionsJob::class, function (GenerateSuggestionsJob $job): bool {
            return $job->context instanceof ScriptContext
                && collect($job->context->hooks)->contains(fn (string $h): bool => str_contains($h, 'GANCHO PRUEBA')
                    && str_contains($h, 'OBJETIVO X')
                    && str_contains($h, 'EJEMPLO GENERICO'));
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
        // La creencia cuelga del seguidor directamente.
        $belief = Belief::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id, 'type' => BeliefType::Myth, 'statement' => 'CREENCIA X']);

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

    public function test_idea_generator_sends_direct_beliefs_and_pains(): void
    {
        Queue::fake();
        config(['services.anthropic.key' => 'sk-ant-test']);

        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);

        // Creencia ligada DIRECTAMENTE al seguidor (no vía pregunta) + un deseo.
        $belief = Belief::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'type' => BeliefType::Truth,
            'statement' => 'VERDAD DIRECTA',
        ]);
        $pain = Pain::factory()->create([
            'account_id' => $account->id,
            'ideal_follower_id' => $follower->id,
            'type' => PainType::Desire,
            'body' => 'DESEO X',
        ]);

        Livewire::test(IdeaGenerator::class, ['account' => $account])
            ->set('idealFollowerId', $follower->id)
            ->set('beliefIds', [$belief->id])
            ->set('painIds', [$pain->id])
            ->call('generate');

        Queue::assertPushed(GenerateSuggestionsJob::class, function (GenerateSuggestionsJob $job): bool {
            return $job->context instanceof IdeaContext
                && $job->context->beliefs === ['[Verdad] VERDAD DIRECTA']
                && $job->context->pains === ['[Deseo] DESEO X'];
        });
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
