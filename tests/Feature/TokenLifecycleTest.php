<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TokenLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    private function user(string $role = 'manager'): User
    {
        return User::create([
            'name' => 'T', 'email' => 't-'.uniqid().'@example.test', 'password' => 'secret123',
            'role' => $role, 'is_active' => true,
        ]);
    }

    private function statusFor(string $token): int
    {
        $this->app['auth']->forgetGuards();

        return $this->withHeader('Authorization', 'Bearer '.$token)->getJson('/api/v1/admin/me')->getStatusCode();
    }

    public function test_admin_login_issues_a_token_that_expires(): void
    {
        $user = $this->user('admin');
        $this->postJson('/api/v1/admin/login', ['email' => $user->email, 'password' => 'secret123'])->assertOk();

        $expires = $user->tokens()->first()->expires_at;
        $this->assertNotNull($expires);
        $this->assertTrue($expires->between(now()->addHours(11), now()->addHours(13)));
    }

    public function test_an_expired_token_is_rejected(): void
    {
        $user = $this->user('admin');
        $token = $user->createToken('admin-token', ['admin'], now()->addHour());
        $this->assertSame(200, $this->statusFor($token->plainTextToken));

        $token->accessToken->forceFill(['expires_at' => now()->subMinute()])->save();
        $this->assertSame(401, $this->statusFor($token->plainTextToken));
    }

    public function test_deactivating_a_user_revokes_their_tokens(): void
    {
        $user = $this->user();
        $plain = $user->createToken('admin-token', ['admin'])->plainTextToken;
        $this->assertSame(200, $this->statusFor($plain));

        $user->update(['is_active' => false]);

        $this->assertSame(0, $user->tokens()->count());
        $this->assertSame(401, $this->statusFor($plain));
    }

    public function test_changing_password_or_role_revokes_tokens(): void
    {
        $user = $this->user();
        $user->createToken('admin-token', ['admin']);
        $user->update(['password' => 'another-secret1']);
        $this->assertSame(0, $user->tokens()->count());

        $user->createToken('admin-token', ['admin']);
        $user->update(['role' => 'customer']);
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_an_unrelated_edit_keeps_the_tokens(): void
    {
        $user = $this->user();
        $user->createToken('admin-token', ['admin']);
        $user->update(['name' => 'New Name']);

        $this->assertSame(1, $user->tokens()->count());
    }

    public function test_deleting_a_user_through_the_api_revokes_their_tokens(): void
    {
        $admin = $this->user('admin');
        $victim = $this->user();
        $victim->createToken('admin-token', ['admin']);

        $this->actingAs($admin, 'sanctum')->deleteJson('/api/v1/admin/users/'.$victim->id)->assertOk();

        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $victim->id, 'tokenable_type' => User::class]);
    }

    public function test_changing_roles_through_the_api_revokes_tokens(): void
    {
        $admin = $this->user('admin');
        $staff = $this->user();
        $role = Role::create(['name' => 'R '.uniqid(), 'slug' => 'r-'.uniqid()]);
        $staff->createToken('admin-token', ['admin']);

        $this->actingAs($admin, 'sanctum')->putJson('/api/v1/admin/users/'.$staff->id, [
            'name' => $staff->name, 'email' => $staff->email, 'role' => 'manager', 'role_ids' => [$role->id],
        ])->assertOk();

        $this->assertSame(0, $staff->tokens()->count());
    }
}
