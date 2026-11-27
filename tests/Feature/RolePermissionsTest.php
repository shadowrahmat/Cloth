<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** Admin routes enforce the permissions of the user's roles (finding H4). */
class RolePermissionsTest extends TestCase
{
    use DatabaseTransactions;

    private function staff(string $role = 'manager', ?array $permissionSlugs = null): User
    {
        $user = User::create([
            'name' => 'Staff',
            'email' => 'staff-'.uniqid().'@example.test',
            'password' => 'secret123',
            'role' => $role,
            'is_active' => true,
        ]);

        if ($permissionSlugs !== null) {
            $custom = Role::create(['name' => 'Custom '.uniqid(), 'slug' => 'custom-'.uniqid()]);
            $custom->permissions()->sync(Permission::whereIn('slug', $permissionSlugs)->pluck('id'));
            $user->roles()->attach($custom);
        }

        return $user;
    }

    public function test_manager_without_an_assigned_role_gets_the_default_manager_permissions(): void
    {
        $this->actingAs($this->staff(), 'sanctum');

        $this->getJson('/api/v1/admin/orders')->assertOk();
        $this->getJson('/api/v1/admin/products')->assertOk();
        $this->getJson('/api/v1/admin/media')->assertOk();
        $this->getJson('/api/v1/admin/resources/coupons')->assertOk();
    }

    public function test_default_manager_cannot_touch_settings_or_audit_logs(): void
    {
        $this->actingAs($this->staff(), 'sanctum');

        $this->getJson('/api/v1/admin/settings')->assertStatus(403);
        $this->postJson('/api/v1/admin/settings', ['store_name' => 'x'])->assertStatus(403);
        $this->getJson('/api/v1/admin/audit-logs')->assertStatus(403);
        $this->getJson('/api/v1/admin/email-templates')->assertStatus(403);
        $this->getJson('/api/v1/admin/resources/shipping-methods')->assertStatus(403);
    }

    public function test_a_custom_role_limits_the_manager_to_exactly_its_permissions(): void
    {
        $this->actingAs($this->staff('manager', ['view_orders']), 'sanctum');

        $this->getJson('/api/v1/admin/orders')->assertOk();
        // read-only: no status changes, and nothing outside orders
        $this->putJson('/api/v1/admin/orders/1/status', ['order_status' => 'confirmed'])->assertStatus(403);
        $this->getJson('/api/v1/admin/products')->assertStatus(403);
        $this->getJson('/api/v1/admin/inventory')->assertStatus(403);
    }

    public function test_the_action_follows_the_http_method(): void
    {
        $this->actingAs($this->staff('manager', ['view_products']), 'sanctum');

        $this->getJson('/api/v1/admin/products')->assertOk();
        $this->postJson('/api/v1/admin/products', [])->assertStatus(403);
        $this->deleteJson('/api/v1/admin/products/1')->assertStatus(403);
        $this->postJson('/api/v1/admin/products/bulk', ['ids' => [1], 'action' => 'delete'])->assertStatus(403);
        $this->postJson('/api/v1/admin/products/bulk', ['ids' => [1], 'action' => 'draft'])->assertStatus(403);
    }

    public function test_bulk_delete_needs_delete_permission_but_status_change_needs_only_update(): void
    {
        $this->actingAs($this->staff('manager', ['update_products']), 'sanctum');

        $this->postJson('/api/v1/admin/products/bulk', ['ids' => [999999], 'action' => 'delete'])->assertStatus(403);
        $this->postJson('/api/v1/admin/products/bulk', ['ids' => [999999], 'action' => 'draft'])->assertOk();
    }

    public function test_registry_resources_are_guarded_by_their_module(): void
    {
        $this->actingAs($this->staff('manager', ['view_cms']), 'sanctum');

        $this->getJson('/api/v1/admin/resources/testimonials')->assertOk();
        $this->postJson('/api/v1/admin/resources/testimonials', [])->assertStatus(403);
        $this->getJson('/api/v1/admin/resources/coupons')->assertStatus(403);
        $this->getJson('/api/v1/admin/categories')->assertStatus(403);
    }

    public function test_login_reports_the_permissions_the_server_enforces(): void
    {
        $manager = $this->staff('manager', ['view_orders', 'view_media']);

        $this->actingAs($manager, 'sanctum');
        $this->getJson('/api/v1/admin/me')->assertOk()
            ->assertJsonPath('user.permissions', ['view_orders', 'view_media']);
    }

    public function test_admin_passes_every_permission_check(): void
    {
        $this->actingAs($this->staff('admin'), 'sanctum');

        $this->getJson('/api/v1/admin/settings')->assertOk();
        $this->getJson('/api/v1/admin/audit-logs')->assertOk();
        $this->getJson('/api/v1/admin/resources/shipping-methods')->assertOk();
    }
}
