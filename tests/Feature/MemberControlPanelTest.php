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
}
