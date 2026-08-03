<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Livewire\Studio\PieceComposer;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\User;
use App\Support\Ai\ScriptContext;
use App\Support\Ai\ViralCatalog;
use App\Viral\Format;
use App\Viral\Reference;
use App\Viral\Subformat;
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

    public function test_references_merge_format_and_subformat(): void
    {
        $catalog = new ViralCatalog(formatRegistry: [new FakeReferencedFormat]);

        // Solo formato: sus referencias.
        $refs = $catalog->referencesFor('selfie');
        $this->assertCount(1, $refs);
        $this->assertSame('Ejemplo del formato', $refs[0]->name);

        // Formato + subformato: se combinan.
        $refs = $catalog->referencesFor('selfie', 'fake-sub');
        $this->assertCount(2, $refs);
        $this->assertSame('Ejemplo del subformato', $refs[1]->name);
        $this->assertSame('https://example.com/sub', $refs[1]->url);

        // Formato sin referencias (catálogo real) o inexistente: vacío.
        $this->assertSame([], app(ViralCatalog::class)->referencesFor('rankings'));
        $this->assertSame([], app(ViralCatalog::class)->referencesFor(null));
    }

    public function test_composer_shows_the_example_button_only_when_references_exist(): void
    {
        $account = Account::factory()->create();
        $piece = ContentPiece::factory()->create(['account_id' => $account->id]);
        $this->actingAs($this->member($account));

        // Catálogo real: 'rankings' no tiene referencias → sin botón.
        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('format', 'rankings')
            ->assertDontSee('Ver ejemplo');

        // Catálogo con referencias para 'selfie' → botón + modal con el enlace.
        $this->app->instance(ViralCatalog::class, new ViralCatalog(formatRegistry: [new FakeReferencedFormat]));

        Livewire::test(PieceComposer::class, ['account' => $account])
            ->call('selectPiece', $piece->id)
            ->set('format', 'selfie')
            ->assertSee('Ver ejemplo')
            ->assertSee('Ejemplo del formato')
            ->assertSee('https://example.com/formato');
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

/** Formato de prueba con referencias (clave real del enum para poder elegirlo en la UI). */
class FakeReferencedFormat extends Format
{
    public function key(): string
    {
        return 'selfie';
    }

    public function instructions(): string
    {
        return 'Formato de prueba.';
    }

    public function subformats(): array
    {
        return [new FakeReferencedSubformat];
    }

    public function references(): array
    {
        return [new Reference('Ejemplo del formato', 'https://example.com/formato')];
    }
}

class FakeReferencedSubformat extends Subformat
{
    public function key(): string
    {
        return 'fake-sub';
    }

    public function label(): string
    {
        return 'Subformato de prueba';
    }

    public function instructions(): string
    {
        return 'Subformato de prueba.';
    }

    public function references(): array
    {
        return [new Reference('Ejemplo del subformato', 'https://example.com/sub')];
    }
}
