<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_admin_can_view_the_complete_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('لوحة تحكم المدير')
            ->assertSee('كل المستخدمين وبياناتهم')
            ->assertSee($admin->email);
    }

    public function test_non_admin_cannot_view_the_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'member']);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
