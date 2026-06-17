<?php

namespace Tests\Feature;

use App\Enums\TeamRole;
use App\Filament\Pages\TeamMembers;
use App\Filament\Resources\AccountInvitations\AccountInvitationResource;
use App\Filament\Resources\AccountInvitations\Pages\CreateAccountInvitation;
use App\Filament\Resources\Questions\QuestionResource;
use App\Mail\AccountInvitationMail;
use App\Models\Account;
use App\Models\AccountInvitation;
use App\Models\IdealFollower;
use App\Models\Question;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MultiUserTest extends TestCase
{
    use RefreshDatabase;

    private function admin(Account $account): User
    {
        $user = User::factory()->create();
        $account->users()->attach($user->id, ['role' => TeamRole::Admin->value]);

        return $user;
    }

    public function test_role_helpers(): void
    {
        $account = Account::factory()->create();
        $admin = $this->admin($account);

        $editor = User::factory()->create();
        $account->users()->attach($editor->id, ['role' => TeamRole::Editor->value]);

        $this->assertSame(TeamRole::Admin, $admin->roleIn($account));
        $this->assertTrue($admin->isAdminOf($account));
        $this->assertSame(TeamRole::Editor, $editor->roleIn($account));
        $this->assertFalse($editor->isAdminOf($account));
    }

    public function test_a_new_user_accepts_an_invitation_and_joins_with_role(): void
    {
        $account = Account::factory()->create();
        $invitation = AccountInvitation::create([
            'account_id' => $account->id,
            'email' => 'nuevo@example.test',
            'role' => TeamRole::Editor,
        ]);

        $this->get(route('invitations.show', $invitation->token))->assertOk();

        $this->post(route('invitations.accept', $invitation->token), [
            'name' => 'Persona Nueva',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ])->assertRedirect("/admin/{$account->slug}");

        $user = User::where('email', 'nuevo@example.test')->firstOrFail();
        $this->assertSame(TeamRole::Editor, $user->roleIn($account));
        $this->assertNotNull($invitation->refresh()->accepted_at);
    }

    public function test_an_existing_user_accepts_an_invitation(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create(['email' => 'existe@example.test']);

        $invitation = AccountInvitation::create([
            'account_id' => $account->id,
            'email' => 'existe@example.test',
            'role' => TeamRole::Admin,
        ]);

        $this->actingAs($user)
            ->post(route('invitations.accept', $invitation->token))
            ->assertRedirect("/admin/{$account->slug}");

        $this->assertSame(TeamRole::Admin, $user->refresh()->roleIn($account));
        $this->assertNotNull($invitation->refresh()->accepted_at);
    }

    public function test_creating_an_invitation_sends_the_email(): void
    {
        Mail::fake();

        $account = Account::factory()->create();
        $admin = $this->admin($account);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);

        Livewire::test(CreateAccountInvitation::class)
            ->fillForm(['email' => 'invitado@example.test', 'role' => TeamRole::Editor->value])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('account_invitations', [
            'account_id' => $account->id,
            'email' => 'invitado@example.test',
            'role' => TeamRole::Editor->value,
        ]);
        Mail::assertSent(AccountInvitationMail::class);
    }

    public function test_expired_invitation_cannot_be_accepted(): void
    {
        $account = Account::factory()->create();
        $user = User::factory()->create(['email' => 'exp@example.test']);
        $invitation = AccountInvitation::create([
            'account_id' => $account->id,
            'email' => 'exp@example.test',
            'role' => TeamRole::Editor,
            'expires_at' => now()->subDay(),
        ]);

        $this->get(route('invitations.show', $invitation->token))
            ->assertOk()
            ->assertSee('caducado');

        $this->actingAs($user)
            ->post(route('invitations.accept', $invitation->token))
            ->assertRedirect(route('invitations.show', $invitation->token));

        $this->assertNull($user->roleIn($account));
        $this->assertNull($invitation->refresh()->accepted_at);
    }

    public function test_guest_with_existing_account_is_redirected_to_login_with_intended_url(): void
    {
        $account = Account::factory()->create();
        User::factory()->create(['email' => 'has@example.test']);
        $invitation = AccountInvitation::create([
            'account_id' => $account->id,
            'email' => 'has@example.test',
            'role' => TeamRole::Editor,
        ]);

        $this->get(route('invitations.show', $invitation->token))
            ->assertRedirect('/admin/login')
            ->assertSessionHas('url.intended', $invitation->acceptanceUrl());
    }

    public function test_cannot_invite_an_email_that_already_has_an_invitation(): void
    {
        $account = Account::factory()->create();
        $admin = $this->admin($account);

        AccountInvitation::create([
            'account_id' => $account->id,
            'email' => 'dup@example.test',
            'role' => TeamRole::Editor,
        ]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);

        Livewire::test(CreateAccountInvitation::class)
            ->fillForm(['email' => 'dup@example.test', 'role' => TeamRole::Editor->value])
            ->call('create')
            ->assertHasFormErrors(['email']);

        // Sigue habiendo una sola invitación (no se duplicó ni reventó con un 500).
        $this->assertSame(1, AccountInvitation::where('account_id', $account->id)->count());
    }

    public function test_editor_cannot_manage_the_team_but_admin_can(): void
    {
        $account = Account::factory()->create();
        $admin = $this->admin($account);
        $editor = User::factory()->create();
        $account->users()->attach($editor->id, ['role' => TeamRole::Editor->value]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);

        $this->actingAs($editor);
        $this->assertFalse(AccountInvitationResource::canViewAny());
        $this->assertFalse(TeamMembers::canAccess());

        $this->actingAs($admin);
        $this->assertTrue(AccountInvitationResource::canViewAny());
        $this->assertTrue(TeamMembers::canAccess());
    }

    public function test_editor_cannot_delete_content_but_admin_can(): void
    {
        $account = Account::factory()->create();
        $admin = $this->admin($account);
        $editor = User::factory()->create();
        $account->users()->attach($editor->id, ['role' => TeamRole::Editor->value]);

        $follower = IdealFollower::factory()->create(['account_id' => $account->id]);
        $question = Question::factory()->create(['account_id' => $account->id, 'ideal_follower_id' => $follower->id]);

        $this->actingAs($admin);
        Filament::setCurrentPanel('admin');
        Filament::setTenant($account);

        $this->actingAs($editor);
        $this->assertFalse(QuestionResource::canDelete($question));
        $this->assertFalse(QuestionResource::canDeleteAny());

        $this->actingAs($admin);
        $this->assertTrue(QuestionResource::canDelete($question));
        $this->assertTrue(QuestionResource::canDeleteAny());
    }
}
