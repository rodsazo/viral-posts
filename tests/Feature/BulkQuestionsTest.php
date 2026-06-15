<?php

namespace Tests\Feature;

use App\Filament\Pages\BulkQuestions;
use App\Models\Account;
use App\Models\Category;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class BulkQuestionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_questions_from_multiple_batches_skipping_blank_lines(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $user->accounts()->attach($account->id);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $category = Category::factory()->create(['account_id' => $account->id]);

        Livewire::test(BulkQuestions::class)
            ->fillForm([
                'ideal_follower_id' => $follower->id,
                'batches' => [
                    ['category_id' => $category->id, 'questions' => "¿Pregunta uno?\n¿Pregunta dos?\n\n¿Pregunta tres?"],
                    ['category_id' => null, 'questions' => "¿Suelta uno?\n   \n¿Suelta dos?"],
                ],
            ])
            ->call('save')
            ->assertHasNoErrors();

        // 3 con categoría + 2 sin categoría = 5 (las líneas en blanco se ignoran).
        $this->assertSame(5, Question::count());
        $this->assertSame(3, Question::where('category_id', $category->id)->count());
        $this->assertSame(2, Question::whereNull('category_id')->count());
        $this->assertSame(5, Question::where('ideal_follower_id', $follower->id)->count());
        $this->assertSame(5, Question::where('account_id', $account->id)->count());
    }

    public function test_ideal_follower_is_required(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create();
        $user->accounts()->attach($account->id);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);

        Livewire::test(BulkQuestions::class)
            ->fillForm([
                'ideal_follower_id' => null,
                'batches' => [['category_id' => null, 'questions' => '¿Algo?']],
            ])
            ->call('save')
            ->assertHasErrors('data.ideal_follower_id');

        $this->assertSame(0, Question::count());
    }
}
