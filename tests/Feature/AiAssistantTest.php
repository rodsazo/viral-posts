<?php

namespace Tests\Feature;

use App\Enums\BeliefType;
use App\Enums\ContentFormat;
use App\Enums\ContentObjective;
use App\Enums\TeamRole;
use App\Livewire\Studio\PieceComposer;
use App\Livewire\Studio\PieceGenerator;
use App\Models\Account;
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

    public function test_suggest_script_populates_suggestions_without_rewriting(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));
        $piece = ContentPiece::factory()->create(['account_id' => $account->id, 'hook' => 'GANCHO ORIGINAL']);

        $this->mock(ContentAssistant::class, function ($mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('suggestScripts')->once()->andReturn([
                new Suggestion('Variante 1', ['hook' => 'G1', 'story' => 'S1', 'moral' => 'M1', 'cta' => 'C1'], 'preview 1'),
                new Suggestion('Variante 2', ['hook' => 'G2', 'story' => 'S2', 'moral' => 'M2', 'cta' => 'C2'], 'preview 2'),
            ]);
        });

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->set('aiBrief', 'Tono cercano')
            ->call('generateScriptSuggestions')
            ->assertCount('scriptSuggestions', 2);

        // No se reescribió nada por el solo hecho de pedir sugerencias.
        $this->assertSame('GANCHO ORIGINAL', $piece->refresh()->hook);
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

    public function test_generator_generates_suggestions_via_assistant(): void
    {
        $account = Account::factory()->create();
        $this->actingAs($this->member($account));

        $this->mock(ContentAssistant::class, function ($mock): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('suggestScripts')->once()->andReturn([
                new Suggestion('Variante 1', ['hook' => 'H1', 'story' => 'S1', 'moral' => 'M1', 'cta' => 'C1'], 'p1'),
                new Suggestion('Variante 2', ['hook' => 'H2', 'story' => 'S2', 'moral' => 'M2', 'cta' => 'C2'], 'p2'),
            ]);
        });

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('instructions', 'tono cercano')
            ->call('generate')
            ->assertCount('suggestions', 2);

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

        $captured = null;
        $this->mock(ContentAssistant::class, function ($mock) use (&$captured): void {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('suggestScripts')->once()
                ->andReturnUsing(function (ScriptContext $context) use (&$captured): array {
                    $captured = $context;

                    return [new Suggestion('V1', ['hook' => 'H', 'story' => 'S', 'moral' => 'M', 'cta' => 'C'], 'p')];
                });
        });

        Livewire::test(PieceGenerator::class, ['account' => $account])
            ->set('idealFollowerId', $follower->id)
            ->set('questionIds', [$q1->id])
            ->set('beliefIds', [$belief->id])
            ->call('generate')
            ->assertCount('suggestions', 1);

        $this->assertSame(['PREGUNTA ELEGIDA'], $captured->questions);
        $this->assertSame(['[Mito] MITO ELEGIDO'], $captured->beliefs);
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
