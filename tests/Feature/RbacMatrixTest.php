<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Support\ResourceRegistry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Routing\Route as LaravelRoute;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Walks every admin route and checks the permission it needs, in both directions:
 * a user holding exactly that permission gets through, and a user holding every
 * other permission is refused. The expectations below are written by hand, apart
 * from routes/api.php, so a route that is added or changed without a matching
 * rule here fails the "unclassified" test instead of silently going unguarded.
 *
 * Ids in URLs are 999999 so authorized calls hit "not found" instead of real rows,
 * and DatabaseTransactions rolls back anything that does get written.
 */
class RbacMatrixTest extends TestCase
{
    use DatabaseTransactions;

    private const ANY = 'any';          // any signed-in admin or manager

    private const ADMIN_ONLY = 'admin'; // administrators only

    /** @var array<string, User> */
    private array $usersByMissingSlug = [];

    /** @return list<array{method: string, uri: string}> */
    private function adminRoutes(): array
    {
        $routes = [];
        /** @var LaravelRoute $route */
        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/v1/admin/')) {
                continue;
            }
            $uri = substr($route->uri(), strlen('api/v1/admin/'));
            if ($uri === 'login') {
                continue;
            }
            foreach ($route->methods() as $method) {
                if ($method !== 'HEAD') {
                    $routes[] = ['method' => $method, 'uri' => $uri];
                }
            }
        }

        return $routes;
    }

    private function module(string $module, string $method): string
    {
        return match ($method) {
            'POST' => "create_{$module}",
            'PUT', 'PATCH' => "update_{$module}",
            'DELETE' => "delete_{$module}",
            default => "view_{$module}",
        };
    }

    /** What the route should require: a permission slug, self::ANY or self::ADMIN_ONLY; null when nobody has classified it. */
    private function expected(string $method, string $uri): ?string
    {
        $first = explode('/', $uri)[0];

        return match (true) {
            in_array($uri, ['me', 'logout', 'dashboard', 'dashboard/badges', 'search'], true) => self::ANY,
            in_array($first, ['users', 'roles', 'email-settings'], true) => self::ADMIN_ONLY,
            $first === 'reports' => 'view_orders',
            in_array($first, ['customers', 'messages', 'newsletter-subscribers'], true) => $this->module('customers', $method),
            in_array($first, ['coupons', 'returns', 'logistics', 'orders'], true) => $this->module('orders', $method),
            $uri === 'products/bulk' => self::ANY, // the permission depends on the action; see test_bulk_*
            in_array($first, ['products', 'reviews'], true) => $this->module('products', $method),
            $first === 'categories' => $this->module('categories', $method),
            $uri === 'inventory/adjust' => 'update_inventory',
            $first === 'inventory' => $this->module('inventory', $method),
            $uri === 'media/{id}/optimize' => 'update_media',
            $first === 'media' => $this->module('media', $method),
            in_array($uri, ['homepage-builder/reorder', 'appearance/reset'], true) => 'update_cms',
            in_array($first, ['blog', 'homepage-builder', 'appearance'], true) => $this->module('cms', $method),
            $first === 'settings' => $method === 'GET' ? 'view_settings' : 'update_settings', // saving settings is an update
            $first === 'email-templates' => $method === 'GET' ? 'view_settings' : 'update_settings',
            in_array($first, ['email-logs', 'audit-logs'], true) => 'view_settings',
            $first === 'resources' => $this->module('cms', $method), // testimonials; see test_registry_*
            default => null,
        };
    }

    private function url(string $uri): string
    {
        $uri = str_replace(['{id}', '{key}', '{resource}'], ['999999', 'order_placed', 'testimonials'], $uri);
        // apiResource routes use {user}, {product}, {blog} ... as the id
        $uri = preg_replace('/\{[a-z_]+\}/', '999999', $uri);

        return '/api/v1/admin/'.$uri;
    }

    private function staff(?Role $role): User
    {
        $user = User::create([
            'name' => 'Staff', 'email' => 'rbac-'.uniqid().'@example.test', 'password' => 'secret123',
            'role' => 'manager', 'is_active' => true,
        ]);
        if ($role) {
            $user->roles()->attach($role);
        }

        return $user;
    }

    private function roleWith(array $slugs): Role
    {
        $role = Role::create(['name' => 'R '.uniqid(), 'slug' => 'r-'.uniqid()]);
        $role->permissions()->sync(Permission::whereIn('slug', $slugs)->pluck('id'));

        return $role;
    }

    private function statusOf(User $user, string $method, string $uri): int
    {
        $this->actingAs($user, 'sanctum');

        return $this->json($method, $this->url($uri), [])->getStatusCode();
    }

    public function test_every_admin_route_is_classified(): void
    {
        $unclassified = [];
        foreach ($this->adminRoutes() as $r) {
            if ($this->expected($r['method'], $r['uri']) === null) {
                $unclassified[] = "{$r['method']} {$r['uri']}";
            }
        }

        $this->assertSame([], $unclassified, 'Admin routes with no RBAC rule in this test: add the permission they should need.');
    }

    public function test_a_staff_member_with_no_permissions_is_refused_everywhere_except_the_open_routes(): void
    {
        $nobody = $this->staff($this->roleWith([]));
        $wrong = [];

        foreach ($this->adminRoutes() as $r) {
            $needs = $this->expected($r['method'], $r['uri']);
            $status = $this->statusOf($nobody, $r['method'], $r['uri']);
            $allowed = $needs === self::ANY;

            if ($allowed === ($status === 403)) {
                $wrong[] = "{$r['method']} {$r['uri']} -> {$status} (needs ".$needs.')';
            }
        }

        $this->assertSame([], $wrong);
    }

    public function test_exactly_the_required_permission_opens_the_route_and_every_other_permission_does_not(): void
    {
        $everything = Permission::pluck('slug')->all();
        $wrong = [];

        foreach ($this->adminRoutes() as $r) {
            $needs = $this->expected($r['method'], $r['uri']);
            if (in_array($needs, [self::ANY, self::ADMIN_ONLY], true)) {
                continue;
            }

            $holder = $this->staff($this->roleWith([$needs]));
            $status = $this->statusOf($holder, $r['method'], $r['uri']);
            if ($status === 403) {
                $wrong[] = "{$r['method']} {$r['uri']}: holding only {$needs} was refused";
            }

            $this->usersByMissingSlug[$needs] ??= $this->staff($this->roleWith(array_values(array_diff($everything, [$needs]))));
            $status = $this->statusOf($this->usersByMissingSlug[$needs], $r['method'], $r['uri']);
            if ($status !== 403) {
                $wrong[] = "{$r['method']} {$r['uri']}: holding everything except {$needs} got {$status}";
            }
        }

        $this->assertSame([], $wrong);
    }

    public function test_admin_only_routes_refuse_a_manager_with_every_permission_and_accept_an_admin(): void
    {
        $manager = $this->staff($this->roleWith(Permission::pluck('slug')->all()));
        $admin = $this->staff(null);
        $admin->forceFill(['role' => 'admin'])->save();
        $wrong = [];

        foreach ($this->adminRoutes() as $r) {
            if ($this->expected($r['method'], $r['uri']) !== self::ADMIN_ONLY) {
                continue;
            }
            if ($this->statusOf($manager, $r['method'], $r['uri']) !== 403) {
                $wrong[] = "{$r['method']} {$r['uri']}: manager was let in";
            }
            if ($this->statusOf($admin->fresh(), $r['method'], $r['uri']) === 403) {
                $wrong[] = "{$r['method']} {$r['uri']}: admin was refused";
            }
        }

        $this->assertSame([], $wrong);
    }

    public function test_an_admin_is_never_refused_by_a_permission_check(): void
    {
        $admin = $this->staff(null);
        $admin->forceFill(['role' => 'admin'])->save();
        $refused = [];

        foreach ($this->adminRoutes() as $r) {
            if ($this->statusOf($admin->fresh(), $r['method'], $r['uri']) === 403) {
                $refused[] = "{$r['method']} {$r['uri']}";
            }
        }

        $this->assertSame([], $refused);
    }

    public function test_bulk_product_action_depends_on_what_it_does(): void
    {
        $body = fn (string $action) => ['ids' => [999999], 'action' => $action];
        $url = '/api/v1/admin/products/bulk';

        $this->actingAs($this->staff($this->roleWith(['view_products', 'create_products', 'delete_products'])), 'sanctum');
        $this->postJson($url, $body('draft'))->assertStatus(403);   // create/delete do not allow a status change
        $this->postJson($url, $body('delete'))->assertOk();

        $this->actingAs($this->staff($this->roleWith(['update_products'])), 'sanctum');
        $this->postJson($url, $body('archived'))->assertOk();
        $this->postJson($url, $body('delete'))->assertStatus(403);
    }

    public function test_every_registry_resource_needs_its_module_permission(): void
    {
        // The registry's own `permission` name decides the module; a name nobody mapped is admin-only.
        $modules = [
            'manage_categories' => 'categories', 'manage_brands' => 'products', 'manage_collections' => 'products',
            'manage_coupons' => 'orders', 'manage_orders' => 'orders', 'manage_shipping' => 'settings',
            'manage_settings' => 'settings', 'manage_testimonials' => 'cms', 'manage_cms' => 'cms',
            'manage_pages' => 'cms', 'manage_inquiries' => 'customers', 'manage_newsletter' => 'customers',
        ];
        $admin = $this->staff(null);
        $admin->forceFill(['role' => 'admin'])->save();
        $wrong = [];

        foreach (ResourceRegistry::all() as $key => $config) {
            $module = $modules[$config['permission'] ?? ''] ?? null;
            $uri = "/api/v1/admin/resources/{$key}";

            if ($module === null) {
                $this->actingAs($this->staff($this->roleWith(Permission::pluck('slug')->all())), 'sanctum');
                if ($this->getJson($uri)->getStatusCode() !== 403) {
                    $wrong[] = "{$key}: unmapped permission '".($config['permission'] ?? '')."' should be admin-only";
                }

                continue;
            }

            $this->actingAs($this->staff($this->roleWith(["view_{$module}"])), 'sanctum');
            if ($this->getJson($uri)->getStatusCode() === 403) {
                $wrong[] = "{$key}: view_{$module} was refused";
            }
            if ($this->postJson($uri, [])->getStatusCode() !== 403) {
                $wrong[] = "{$key}: view_{$module} could create";
            }

            $others = array_diff(Permission::pluck('slug')->all(), ["view_{$module}"]);
            $this->actingAs($this->staff($this->roleWith(array_values($others))), 'sanctum');
            if ($this->getJson($uri)->getStatusCode() !== 403) {
                $wrong[] = "{$key}: could be read without view_{$module}";
            }

            $this->actingAs($admin, 'sanctum');
            if ($this->getJson($uri)->getStatusCode() === 403) {
                $wrong[] = "{$key}: admin was refused";
            }
        }

        $this->assertSame([], $wrong);
    }

    public function test_a_deactivated_staff_member_loses_access_even_with_permissions(): void
    {
        $user = $this->staff($this->roleWith(['view_orders']));
        $this->actingAs($user, 'sanctum');
        $this->getJson('/api/v1/admin/orders')->assertOk();

        $user->update(['is_active' => false]);
        $this->actingAs($user->fresh(), 'sanctum');
        $this->getJson('/api/v1/admin/orders')->assertStatus(403);
    }

    public function test_a_customer_account_holding_staff_permissions_is_still_refused(): void
    {
        $customer = User::create([
            'name' => 'Cust', 'email' => 'cust-'.uniqid().'@example.test', 'password' => 'secret123',
            'role' => 'customer', 'is_active' => true,
        ]);
        $customer->roles()->attach($this->roleWith(Permission::pluck('slug')->all()));

        $this->actingAs($customer, 'sanctum');
        $this->getJson('/api/v1/admin/orders')->assertStatus(403);
        $this->getJson('/api/v1/admin/products')->assertStatus(403);
    }
}
