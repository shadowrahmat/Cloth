<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Admin routes now enforce role permissions, so the permissions and the default
 * "manager" role must exist on every install, not only where the seeder ran.
 *
 * Purely additive: it creates missing rows and grants the manager role what a
 * manager could already do before enforcement (plus media, which product forms
 * need for image uploads). Nothing is removed.
 */
return new class extends Migration
{
    private const MODULES = ['products', 'orders', 'inventory', 'customers', 'categories', 'cms', 'settings', 'media', 'users'];

    private const ACTIONS = ['view', 'create', 'update', 'delete'];

    /** What a manager gets by default: shop work, content and media; no settings or staff management. */
    private const MANAGER = [
        'products' => self::ACTIONS,
        'orders' => self::ACTIONS,
        'inventory' => self::ACTIONS,
        'customers' => self::ACTIONS,
        'categories' => self::ACTIONS,
        'cms' => self::ACTIONS,
        'media' => ['view', 'create', 'update'],
    ];

    public function up(): void
    {
        $now = now();

        foreach (['admin' => ['Super Administrator', 'Unrestricted access to all modules and configurations'],
            'manager' => ['Store Manager', 'Can manage products, orders, inventory, and content']] as $slug => [$name, $description]) {
            DB::table('roles')->insertOrIgnore(['slug' => $slug, 'name' => $name, 'description' => $description, 'created_at' => $now, 'updated_at' => $now]);
        }

        foreach (self::MODULES as $module) {
            foreach (self::ACTIONS as $action) {
                DB::table('permissions')->insertOrIgnore([
                    'slug' => "{$action}_{$module}",
                    'name' => ucfirst($action).' '.ucfirst($module),
                    'module' => $module,
                    'description' => "Permission to {$action} {$module}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        $this->grant('admin', DB::table('permissions')->pluck('slug')->all());

        $manager = [];
        foreach (self::MANAGER as $module => $actions) {
            foreach ($actions as $action) {
                $manager[] = "{$action}_{$module}";
            }
        }
        $this->grant('manager', $manager);
    }

    public function down(): void
    {
        // Intentionally empty: roles and permissions may have been edited since, and removing them would lock staff out.
    }

    /** @param  list<string>  $slugs */
    private function grant(string $roleSlug, array $slugs): void
    {
        $roleId = DB::table('roles')->where('slug', $roleSlug)->value('id');
        $now = now();

        foreach (DB::table('permissions')->whereIn('slug', $slugs)->pluck('id') as $permissionId) {
            DB::table('permission_role')->insertOrIgnore(['permission_id' => $permissionId, 'role_id' => $roleId, 'created_at' => $now, 'updated_at' => $now]);
        }
    }
};
