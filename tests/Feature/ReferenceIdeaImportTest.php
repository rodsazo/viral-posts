<?php

namespace Tests\Feature;

use App\Enums\IdeaStatus;
use App\Enums\TeamRole;
use App\Livewire\Studio\ReferenceIdeaImporter;
use App\Models\Account;
use App\Models\HerasTemplate;
use App\Models\Niche;
use App\Models\User;
use App\Models\ViralReferent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReferenceIdeaImportTest extends TestCase
{
    use RefreshDatabase;

    private function member(Account $account, TeamRole $role = TeamRole::Editor): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => $role->value]);

        return $user;
    }

    public function test_screen_requires_membership(): void
    {
        $account = Account::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get("/studio/{$account->slug}/ideas-referenciales")
            ->assertForbidden();
    }

    public function test_member_can_import_reference_ideas(): void
    {
        $account = Account::factory()->create();
        $niche = Niche::factory()->create();
        $referent = ViralReferent::factory()->create(['niche_id' => $niche->id]);
        $template = HerasTemplate::factory()->create([
            'viral_referent_id' => $referent->id,
            'name' => 'IDEA REFERENCIAL X',
            'structure' => 'Estructura de la idea.',
            'reference_url' => 'https://www.tiktok.com/@a/video/1',
            'reference_urls' => ['https://www.instagram.com/p/2'],
        ]);
        $this->actingAs($this->member($account));

        Livewire::test(ReferenceIdeaImporter::class, ['account' => $account])
            ->assertSee('IDEA REFERENCIAL X')
            ->set('selected', [$template->id])
            ->call('import')
            ->assertRedirect("/studio/{$account->slug}/ideas-ganadoras");

        $idea = $account->winningIdeas()->first();
        $this->assertNotNull($idea);
        $this->assertSame('IDEA REFERENCIAL X', $idea->title);
        $this->assertSame('Estructura de la idea.', $idea->concept);
        $this->assertSame(IdeaStatus::Borrador, $idea->status);
        $this->assertTrue($idea->isImported());
        $this->assertSame($referent->id, $idea->viral_referent_id);
        $this->assertEqualsCanonicalizing(
            ['https://www.tiktok.com/@a/video/1', 'https://www.instagram.com/p/2'],
            $idea->example_urls,
        );
    }

    public function test_filter_by_niche_limits_the_catalog(): void
    {
        $account = Account::factory()->create();
        $nicheA = Niche::factory()->create();
        $nicheB = Niche::factory()->create();
        $refA = ViralReferent::factory()->create(['niche_id' => $nicheA->id]);
        $refB = ViralReferent::factory()->create(['niche_id' => $nicheB->id]);
        HerasTemplate::factory()->create(['viral_referent_id' => $refA->id, 'name' => 'DEL NICHO A']);
        HerasTemplate::factory()->create(['viral_referent_id' => $refB->id, 'name' => 'DEL NICHO B']);
        $this->actingAs($this->member($account));

        Livewire::test(ReferenceIdeaImporter::class, ['account' => $account])
            ->set('nicheFilter', $nicheA->id)
            ->assertSee('DEL NICHO A')
            ->assertDontSee('DEL NICHO B');
    }
}
