<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AiGeneration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiUsageTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_membership(): void
    {
        $account = Account::factory()->create();

        $this->actingAs(User::factory()->create())
            ->get("/studio/{$account->slug}/uso-ia")
            ->assertForbidden();
    }

    public function test_shows_generation_volume_for_the_brand(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => 'editor']);

        AiGeneration::create(['account_id' => $account->id, 'user_id' => $user->id, 'kind' => 'script', 'status' => 'done']);
        AiGeneration::create(['account_id' => $account->id, 'user_id' => $user->id, 'kind' => 'idea', 'status' => 'failed']);
        // De otra marca: no debe contarse.
        AiGeneration::create(['account_id' => Account::factory()->create()->id, 'kind' => 'script', 'status' => 'done']);

        $this->actingAs($user)
            ->get("/studio/{$account->slug}/uso-ia")
            ->assertOk()
            ->assertSee('Uso de IA')
            ->assertSee('Guiones de pieza')
            ->assertSee('Ideas ganadoras');
    }
}
