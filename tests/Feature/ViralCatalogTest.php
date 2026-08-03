<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Livewire\Studio\PieceComposer;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\User;
use App\Support\Ai\ScriptContext;
use App\Support\Ai\ViralCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Conocimiento viral en código: principios rectores (versionables) + formatos/subformatos,
 * inyectados opcionalmente en el prompt de generación/refinamiento.
 */
class ViralCatalogTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Editor->value]);

        return $user;
    }

    public function test_catalog_exposes_options_and_instructions(): void
    {
        $catalog = app(ViralCatalog::class);

        $this->assertArrayHasKey('heras-2026', $catalog->principlesOptions());
        $this->assertNotNull($catalog->principlesInstructions('heras-2026'));
        $this->assertNull($catalog->principlesInstructions('no-existe'));

        // El formato "personajes" tiene el subformato escéptico-convencido.
        $this->assertArrayHasKey('esceptico-convencido', $catalog->subformatOptions('personajes'));
        $this->assertTrue($catalog->hasSubformats('personajes'));
        $this->assertFalse($catalog->hasSubformats('selfie'));

        // La guía de formato combina formato principal + subformato.
        $guide = $catalog->formatGuide('personajes', 'esceptico-convencido');
        $this->assertStringContainsString('Personajes', $guide);
        $this->assertStringContainsString('escéptico', $guide);

        // Subformato inválido para el formato: no rompe, solo omite.
        $this->assertNull($catalog->formatGuide(null, null));
        $this->assertNotNull($catalog->formatGuide('rankings', null));
    }

    public function test_instructions_are_injected_into_the_script_prompt(): void
    {
        $context = new ScriptContext(
            format: 'Personajes',
            principlesInstructions: app(ViralCatalog::class)->principlesInstructions('heras-2026'),
            formatGuide: app(ViralCatalog::class)->formatGuide('personajes', 'esceptico-convencido'),
        );

        $prompt = $context->toPrompt();

        $this->assertStringContainsString('PRINCIPIOS RECTORES', $prompt);
        $this->assertStringContainsString('FORMATO A REPLICAR', $prompt);
        $this->assertStringContainsString('Escalera de objeciones', $prompt);
    }

    public function test_composer_persists_the_viral_selection(): void
    {
        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('format', 'personajes')
            ->set('viralSubformatKey', 'esceptico-convencido')
            ->set('viralPrinciplesKey', 'heras-2026');

        $piece->refresh();
        $this->assertSame('personajes', $piece->format?->value);
        $this->assertSame('esceptico-convencido', $piece->viral_subformat_key);
        $this->assertSame('heras-2026', $piece->viral_principles_key);
    }

    public function test_changing_format_drops_an_incompatible_subformat(): void
    {
        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('format', 'personajes')
            ->set('viralSubformatKey', 'esceptico-convencido')
            ->set('format', 'selfie') // selfie no tiene ese subformato
            ->assertSet('viralSubformatKey', null);

        $this->assertNull($piece->refresh()->viral_subformat_key);
    }
}
