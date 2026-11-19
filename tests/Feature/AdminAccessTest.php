<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_is_rejected_from_admin_api(): void
    {
        $this->getJson('/api/v1/admin/users')->assertStatus(401);
    }

    public function test_customer_token_cannot_reach_admin_api(): void
    {
        $register = $this->postJson('/api/v1/customer/register', [
            'name' => 'Curious Customer',
            'email' => 'curious-'.uniqid().'@example.test',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertStatus(201);

        $token = $register->json('token');

        foreach (['/api/v1/admin/users', '/api/v1/admin/settings', '/api/v1/admin/dashboard', '/api/v1/admin/resources/coupons'] as $uri) {
            $this->withToken($token)->getJson($uri)->assertStatus(403);
        }

        $this->withToken($token)->postJson('/api/v1/admin/users', [
            'name' => 'Escalated',
            'email' => 'escalated-'.uniqid().'@example.test',
            'password' => 'secret123',
            'role' => 'admin',
        ])->assertStatus(403);
    }

    public function test_deactivated_admin_is_rejected(): void
    {
        $admin = User::where('role', 'admin')->first();
        $admin->is_active = false;
        $admin->save();

        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/dashboard')->assertStatus(403);
    }

    public function test_active_admin_can_reach_admin_api(): void
    {
        $admin = User::where('role', 'admin')->first();

        $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/dashboard')->assertStatus(200);
    }
}
