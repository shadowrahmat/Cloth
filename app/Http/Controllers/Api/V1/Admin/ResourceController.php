<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\EnsurePermission;
use App\Models\ActivityLog;
use App\Support\ResourceRegistry;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ResourceController extends Controller
{
    /**
     * Registry resources are guarded by the module their `permission` belongs to.
     * A resource whose permission is not listed here is admin-only.
     */
    private const PERMISSION_MODULES = [
        'manage_categories' => 'categories',
        'manage_brands' => 'products',
        'manage_collections' => 'products',
        'manage_coupons' => 'orders',
        'manage_orders' => 'orders',
        'manage_shipping' => 'settings',
        'manage_settings' => 'settings',
        'manage_testimonials' => 'cms',
        'manage_cms' => 'cms',
        'manage_pages' => 'cms',
        'manage_inquiries' => 'customers',
        'manage_newsletter' => 'customers',
    ];

    protected function getResourceConfig(string $resource, Request $request): array
    {
        $config = ResourceRegistry::get($resource);
        if (!$config) {
            abort(404, "Resource '{$resource}' is not registered.");
        }

        $this->authorizeResource($request, $config);

        return $config;
    }

    private function authorizeResource(Request $request, array $config): void
    {
        $user = $request->user();
        $module = self::PERMISSION_MODULES[$config['permission'] ?? ''] ?? null;
        $allowed = $user && ($user->isAdmin() || ($module && $user->hasPermission(EnsurePermission::slugFor($module, $request->method()))));

        if (!$allowed) {
            abort(403, 'Access denied. You do not have permission to do this.');
        }
    }

    public function index(Request $request, string $resource): JsonResponse
    {
        $config = $this->getResourceConfig($resource, $request);
        $modelClass = $config['model'];
        $query = $modelClass::query();

        // Eager loading
        if (!empty($config['with'])) {
            $query->with($config['with']);
        }

        // Relationship counts
        if (!empty($config['with_count'])) {
            $query->withCount($config['with_count']);
        }

        // Search
        if ($search = $request->input('search')) {
            $searchable = $config['searchable'] ?? [];
            if (!empty($searchable)) {
                $query->where(function ($q) use ($searchable, $search) {
                    foreach ($searchable as $field) {
                        $q->orWhere($field, 'like', "%{$search}%");
                    }
                });
            }
        }

        // Filters
        if (!empty($config['filters'])) {
            foreach ($config['filters'] as $filterKey => $filterConfig) {
                if ($request->has($filterKey) && $request->input($filterKey) !== '' && $request->input($filterKey) !== null) {
                    $val = $request->input($filterKey);
                    if ($filterConfig['type'] === 'boolean') {
                        $val = filter_var($val, FILTER_VALIDATE_BOOLEAN);
                    }
                    $query->where($filterKey, $val);
                }
            }
        }

        // Sorting
        $sortBy = $request->input('sort_by');
        $sortDirection = strtolower($request->input('sort_direction', 'asc')) === 'desc' ? 'desc' : 'asc';
        if ($sortBy) {
            $query->orderBy($sortBy, $sortDirection);
        } elseif (!empty($config['order_by'])) {
            foreach ($config['order_by'] as $col => $dir) {
                $query->orderBy($col, $dir);
            }
        } else {
            $query->latest();
        }

        $perPage = (int) $request->input('per_page', 15);
        if ($perPage === -1) {
            $items = $query->get();
            return response()->json([
                'success' => true,
                'data' => $items,
                'config' => [
                    'label' => $config['label'],
                    'singular' => $config['singular'],
                ],
            ]);
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
            'config' => [
                'label' => $config['label'],
                'singular' => $config['singular'],
            ],
        ]);
    }

    public function store(Request $request, string $resource): JsonResponse
    {
        $config = $this->getResourceConfig($resource, $request);
        $rules = $config['rules'] ?? [];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $modelClass = $config['model'];

        // Auto-slug handling
        if (!empty($config['slug_field']) && !empty($config['slug_source'])) {
            $slugField = $config['slug_field'];
            $slugSource = $config['slug_source'];
            if (empty($data[$slugField]) && !empty($data[$slugSource])) {
                $data[$slugField] = Str::slug($data[$slugSource]);
            }
        }

        DB::beginTransaction();
        try {
            // Check for relationship syncs e.g. product_ids for collections
            $relationSync = [];
            if ($resource === 'collections' && isset($data['product_ids'])) {
                $relationSync['products'] = $data['product_ids'];
                unset($data['product_ids']);
            }

            $record = $modelClass::create($data);

            if (!empty($relationSync['products'])) {
                $record->products()->sync($relationSync['products']);
            }

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'create',
                'model_type' => $modelClass,
                'model_id' => $record->id,
                'description' => "Created {$config['singular']} #{$record->id}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if (!empty($config['with'])) {
                $record->load($config['with']);
            }

            return response()->json([
                'success' => true,
                'message' => "{$config['singular']} created successfully.",
                'data' => $record,
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(Request $request, string $resource, string|int $id): JsonResponse
    {
        $config = $this->getResourceConfig($resource, $request);
        $modelClass = $config['model'];

        $query = $modelClass::where('id', $id);
        if (!empty($config['with'])) {
            $query->with($config['with']);
        }

        $record = $query->first();
        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => "{$config['singular']} not found.",
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $record,
        ]);
    }

    public function update(Request $request, string $resource, string|int $id): JsonResponse
    {
        $config = $this->getResourceConfig($resource, $request);
        $modelClass = $config['model'];

        $record = $modelClass::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => "{$config['singular']} not found.",
            ], 404);
        }

        $rules = $config['rules'] ?? [];
        // Adjust unique rules for current ID
        foreach ($rules as $field => $fieldRules) {
            if (is_string($fieldRules) && str_contains($fieldRules, 'unique:')) {
                $rules[$field] = $fieldRules . ',' . $id;
            }
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();

        DB::beginTransaction();
        try {
            if ($resource === 'collections' && array_key_exists('product_ids', $data)) {
                $record->products()->sync($data['product_ids'] ?? []);
                unset($data['product_ids']);
            }

            $record->update($data);

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'update',
                'model_type' => $modelClass,
                'model_id' => $record->id,
                'description' => "Updated {$config['singular']} #{$record->id}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            if (!empty($config['with'])) {
                $record->load($config['with']);
            }

            return response()->json([
                'success' => true,
                'message' => "{$config['singular']} updated successfully.",
                'data' => $record,
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update record: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, string $resource, string|int $id): JsonResponse
    {
        $config = $this->getResourceConfig($resource, $request);
        $modelClass = $config['model'];

        $record = $modelClass::find($id);
        if (!$record) {
            return response()->json([
                'success' => false,
                'message' => "{$config['singular']} not found.",
            ], 404);
        }

        DB::beginTransaction();
        try {
            $record->delete();

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'delete',
                'model_type' => $modelClass,
                'model_id' => $id,
                'description' => "Deleted {$config['singular']} #{$id}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$config['singular']} deleted successfully.",
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete record: ' . $e->getMessage(),
            ], 500);
        }
    }
}
