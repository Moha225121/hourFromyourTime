<?php

namespace Tests\Feature;

use App\Models\CommunityMember;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class MemberControlPanelTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_sees_the_public_landing_page(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('ساعة واحدة منك');
    }

    public function test_authenticated_member_is_sent_to_the_control_panel(): void
    {
        $user = User::factory()->create();
        CommunityMember::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'city' => 'طرابلس',
            'initial' => 'م',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertRedirect(route('app.dashboard'));
    }

    public function test_guest_can_join_the_community_and_receive_a_member_profile(): void
    {
        $this->postJson('/api/signup', [
            'name' => 'عضو جديد',
            'email' => 'new-member@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'city' => 'طرابلس',
            'skills' => 'تعليم',
            'needs' => 'تصميم',
        ])
            ->assertCreated()
            ->assertJsonPath('message', 'تم إنشاء حسابك وتسجيل دخولك بنجاح.');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'new-member@example.com']);
        $this->assertDatabaseHas('community_members', ['email' => 'new-member@example.com', 'city' => 'طرابلس']);
    }

    public function test_existing_email_gets_a_clear_validation_error(): void
    {
        User::factory()->create(['email' => 'already-used@example.com']);

        $this->postJson('/api/signup', [
            'name' => 'عضو آخر',
            'email' => 'already-used@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'city' => 'طرابلس',
        ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.email.0', 'هذا البريد الإلكتروني مستخدم بالفعل. جرّب تسجيل الدخول بدلًا من إنشاء حساب جديد.');
    }
}
