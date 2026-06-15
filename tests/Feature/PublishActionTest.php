<?php

namespace Tests\Feature;

use App\Enums\ContentStatus;
use App\Filament\Resources\ContentPieces\Pages\ListContentPieces;
use App\Models\Account;
use App\Models\ContentPiece;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublishActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_publish_action_sets_status_and_date(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $user->accounts()->attach($account->id);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);

        $piece = ContentPiece::factory()->create([
            'account_id' => $account->id,
            'status' => ContentStatus::GuionListo,
            'published_at' => null,
        ]);

        Livewire::test(ListContentPieces::class)
            ->callTableAction('publish', $piece);

        $piece->refresh();
        $this->assertSame(ContentStatus::Publicada, $piece->status);
        $this->assertNotNull($piece->published_at);
    }
}
