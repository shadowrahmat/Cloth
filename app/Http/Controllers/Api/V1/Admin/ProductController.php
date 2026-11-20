<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'brand', 'variants']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($request->has('is_featured') && $request->input('is_featured') !== '') {
            $query->where('is_featured', filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($request->has('is_bestseller') && $request->input('is_bestseller') !== '') {
            $query->where('is_bestseller', filter_var($request->input('is_bestseller'), FILTER_VALIDATE_BOOLEAN));
        }

        if ($stockStatus = $request->input('stock_status')) {
            if ($stockStatus === 'in_stock') {
                $query->where('stock', '>', 5);
            } elseif ($stockStatus === 'low_stock') {
                $query->whereBetween('stock', [1, 5]);
            } elseif ($stockStatus === 'out_of_stock') {
                $query->where('stock', '<=', 0);
            }
        }

        $sortBy = in_array($request->input('sort_by'), self::SORTABLE, true) ? $request->input('sort_by') : 'created_at';
        $sortDir = $request->input('sort_direction') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $sortDir)->orderByDesc('id');

        $perPage = max(1, min(100, (int) $request->input('per_page', 12)));
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => collect($products->items())->each->makeVisible('cost_price')->all(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'counts' => $this->counts(),
            ],
        ]);
    }

    private const SORTABLE = ['created_at', 'updated_at', 'name', 'price', 'stock', 'sku', 'status'];

    /** Numbers for the status/stock filter chips on the products page. */
    private function counts(): array
    {
        return [
            'all' => Product::count(),
            'published' => Product::where('status', 'published')->count(),
            'draft' => Product::where('status', 'draft')->count(),
            'archived' => Product::where('status', 'archived')->count(),
            'low_stock' => Product::whereBetween('stock', [1, 5])->count(),
            'out_of_stock' => Product::where('stock', '<=', 0)->count(),
        ];
    }

    private function rules(?int $id = null): array
    {
        $ignore = $id ? ',' . $id : '';

        return [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|alpha_dash:ascii|unique:products,slug' . $ignore,
            'sku' => 'nullable|string|max:100|unique:products,sku' . $ignore,
            'short_description' => 'nullable|string|max:500',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0|lt:price',
            'compare_at_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'featured_image' => 'nullable|string',
            'gallery' => 'nullable|array',
            'gallery.*' => 'nullable|string',
            'stock' => 'nullable|integer|min:0',
            'low_stock_threshold' => 'nullable|integer',
            'track_inventory' => 'nullable|boolean',
            'is_featured' => 'nullable|boolean',
            'is_bestseller' => 'nullable|boolean',
            'is_new' => 'nullable|boolean',
            'status' => 'nullable|in:published,draft,archived',
            'attributes' => 'nullable|array',
            'fabric_care' => 'nullable|string',
            'shipping_info' => 'nullable|string',
            'returns_info' => 'nullable|string',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'variants' => 'nullable|array|max:200',
            'variants.*.id' => 'nullable|integer',
            'variants.*.size' => 'nullable|string|max:40',
            'variants.*.color' => 'nullable|string|max:60',
            'variants.*.color_code' => 'nullable|string|max:20',
            'variants.*.sku' => 'nullable|string|max:100',
            'variants.*.price' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'nullable|integer|min:0',
            'variants.*.image' => 'nullable|string',
            'variants.*.is_active' => 'nullable|boolean',
        ];
    }

    private function messages(): array
    {
        return [
            'name.required' => 'পণ্যের নাম দিন।',
            'price.required' => 'দাম দিন।',
            'sale_price.lt' => 'সেল প্রাইস আসল দামের চেয়ে কম হতে হবে।',
            'slug.unique' => 'এই লিংক (slug) অন্য একটি পণ্যে ব্যবহার হচ্ছে।',
            'slug.alpha_dash' => 'লিংকে শুধু ইংরেজি অক্ষর, সংখ্যা ও - ব্যবহার করুন।',
            'sku.unique' => 'এই SKU অন্য একটি পণ্যে ব্যবহার হচ্ছে।',
            'variants.*.stock.min' => 'স্টক ০ বা তার বেশি হতে হবে।',
        ];
    }

    /**
     * Save the size/colour rows of a product.
     *
     * Rows that come back with an id are updated in place, so carts, orders and
     * stock history that point at a variant keep pointing at it. Rows that are
     * no longer sent are removed; rows without an id are created.
     */
    private function syncVariants(Product $product, array $rows): int
    {
        $keepIds = collect($rows)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();
        $product->variants()->whereNotIn('id', $keepIds ?: [0])->delete();

        $existing = $product->variants()->get()->keyBy('id');
        $usedSkus = [];
        $sum = 0;

        foreach ($rows as $v) {
            $size = trim((string) ($v['size'] ?? '')) ?: null;
            $color = trim((string) ($v['color'] ?? '')) ?: null;
            $stock = max(0, (int) ($v['stock'] ?? 0));
            $sum += $stock;

            $sku = trim((string) ($v['sku'] ?? ''));
            $variant = isset($v['id']) ? $existing->get((int) $v['id']) : null;
            if ($sku === '' || in_array($sku, $usedSkus, true)
                || ProductVariant::where('sku', $sku)->when($variant, fn ($q) => $q->where('id', '!=', $variant->id))->exists()) {
                $sku = $variant && ! in_array($variant->sku, $usedSkus, true)
                    ? $variant->sku
                    : $product->sku . '-' . Str::upper(Str::slug(implode('-', array_filter([$size, $color])) ?: 'V')) . '-' . Str::upper(Str::random(3));
            }
            $usedSkus[] = $sku;

            $price = isset($v['price']) && $v['price'] !== '' && $v['price'] !== null ? (float) $v['price'] : (float) $product->price;

            $attrs = [
                'sku' => $sku,
                'title' => implode(' / ', array_filter([$size, $color])) ?: 'Standard',
                'price' => $price,
                'stock' => $stock,
                'size' => $size,
                'color' => $color,
                'color_code' => $color ? ($v['color_code'] ?? null) : null,
                'image' => $v['image'] ?? ($variant?->image ?: $product->featured_image),
                'is_active' => $v['is_active'] ?? true,
            ];

            $variant ? $variant->update($attrs) : $product->variants()->create($attrs);
        }

        return $sum;
    }

    private function log(Request $request, string $action, ?int $modelId, string $description): void
    {
        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'model_type' => Product::class,
            'model_id' => $modelId,
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate($this->rules(), $this->messages());

        if (empty($validated['slug'])) {
            $validated['slug'] = $this->uniqueSlug($validated['name']);
        }
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

        DB::beginTransaction();
        try {
            $variantsData = $validated['variants'] ?? [];
            unset($validated['variants']);

            $product = Product::create($validated);

            $totalStock = (int) $product->stock;
            if (! empty($variantsData)) {
                $totalStock = $this->syncVariants($product, $variantsData);
                $product->update(['stock' => $totalStock]);
            }

            InventoryMovement::create([
                'product_id' => $product->id,
                'type' => 'initial',
                'quantity' => $totalStock,
                'previous_stock' => 0,
                'current_stock' => $totalStock,
                'notes' => 'Initial stock on product creation',
                'created_by' => $request->user()?->id,
            ]);

            $this->log($request, 'create_product', $product->id, "Created product '{$product->name}' (SKU: {$product->sku})");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'পণ্য যোগ হয়েছে',
                'data' => $product->load(['category', 'brand', 'variants'])->makeVisible('cost_price'),
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'পণ্য সেভ করা যায়নি: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(string|int $id): JsonResponse
    {
        $product = Product::with(['category', 'brand', 'collections', 'variants', 'reviews'])->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product->makeVisible('cost_price')]);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validated = $request->validate($this->rules((int) $product->id), $this->messages());
        if (array_key_exists('slug', $validated) && empty($validated['slug'])) {
            unset($validated['slug']);
        }
        if (array_key_exists('sku', $validated) && empty($validated['sku'])) {
            unset($validated['sku']);
        }

        DB::beginTransaction();
        try {
            $variantsData = $validated['variants'] ?? null;
            unset($validated['variants']);

            $product->update($validated);

            if ($variantsData !== null) {
                $sum = $this->syncVariants($product, $variantsData);
                if (! empty($variantsData)) {
                    $product->update(['stock' => $sum]);
                }
            }

            $this->log($request, 'update_product', $product->id, "Updated product '{$product->name}'");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'পণ্য আপডেট হয়েছে',
                'data' => $product->fresh(['category', 'brand', 'variants'])->makeVisible('cost_price'),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            report($e);

            return response()->json([
                'success' => false,
                'message' => 'পণ্য সেভ করা যায়নি: ' . $e->getMessage(),
            ], 500);
        }
    }

    /** Copy a product (with its sizes/colours) as a draft, so a similar item takes seconds to add. */
    public function duplicate(Request $request, string|int $id): JsonResponse
    {
        $source = Product::with('variants')->findOrFail($id);

        $copy = DB::transaction(function () use ($source) {
            $copy = $source->replicate(['slug', 'sku']);
            $copy->name = $source->name . ' (কপি)';
            $copy->slug = $this->uniqueSlug($source->slug . '-copy');
            $copy->sku = $source->sku . '-C' . strtoupper(Str::random(3));
            $copy->status = 'draft';
            $copy->save();

            foreach ($source->variants as $v) {
                $nv = $v->replicate(['sku']);
                $nv->product_id = $copy->id;
                $nv->sku = $copy->sku . '-' . strtoupper(Str::random(4));
                $nv->save();
            }

            return $copy;
        });

        $this->log($request, 'create_product', $copy->id, "Duplicated product '{$source->name}' as draft");

        return response()->json([
            'success' => true,
            'message' => 'কপি তৈরি হয়েছে (ড্রাফট হিসেবে)',
            'data' => $copy->load(['category', 'brand', 'variants'])->makeVisible('cost_price'),
        ], 201);
    }

    /** Publish / unpublish / archive / delete several products at once. */
    public function bulk(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => 'required|array|min:1|max:200',
            'ids.*' => 'integer',
            'action' => 'required|in:published,draft,archived,delete',
        ]);

        $query = Product::whereIn('id', $data['ids']);
        $count = $data['action'] === 'delete' ? $query->get()->each->delete()->count() : $query->update(['status' => $data['action']]);

        $this->log($request, 'bulk_products', null, "Bulk '{$data['action']}' on {$count} products");

        $labels = ['published' => 'প্রকাশ করা হয়েছে', 'draft' => 'ড্রাফট করা হয়েছে', 'archived' => 'আর্কাইভ করা হয়েছে', 'delete' => 'মুছে ফেলা হয়েছে'];

        return response()->json([
            'success' => true,
            'message' => "{$count}টি পণ্য {$labels[$data['action']]}",
            'data' => ['count' => $count],
        ]);
    }

    private function uniqueSlug(string $base): string
    {
        $base = Str::slug($base) ?: 'product';
        $slug = $base;
        $i = 2;
        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function destroy(Request $request, string|int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $product->delete();

        ActivityLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'delete_product',
            'model_type' => Product::class,
            'model_id' => $id,
            'description' => "Deleted product #{$id}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Product deleted successfully',
        ]);
    }

    public function export(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products Catalog');

        $headers = ['ID', 'Name', 'SKU', 'Slug', 'Category', 'Price', 'Sale Price', 'Stock', 'Status', 'Is Featured'];
        $sheet->fromArray([$headers], null, 'A1');

        $products = Product::with('category')->get();
        $data = [];
        foreach ($products as $p) {
            $data[] = [
                $p->id,
                $p->name,
                $p->sku,
                $p->slug,
                $p->category?->name ?? 'Uncategorized',
                (float) $p->price,
                $p->sale_price ? (float) $p->sale_price : '',
                (int) $p->stock,
                $p->status,
                $p->is_featured ? 'Yes' : 'No',
            ];
        }

        if (!empty($data)) {
            $sheet->fromArray($data, null, 'A2');
        }

        $writer = new Csv($spreadsheet);
        $fileName = 'catalog-products-' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx,xls|max:10240',
        ]);

        $file = $request->file('file');

        try {
            $spreadsheet = IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            if (count($rows) <= 1) {
                return response()->json(['success' => false, 'message' => 'Uploaded spreadsheet has no data rows'], 422);
            }

            $importedCount = 0;
            $updatedCount = 0;

            DB::beginTransaction();

            // Skip header (row 0)
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                if (empty($row[1])) continue; // Product name is empty

                $name = trim($row[1]);
                $sku = !empty($row[2]) ? trim($row[2]) : 'SKU-' . strtoupper(Str::random(8));
                $slug = !empty($row[3]) ? Str::slug($row[3]) : Str::slug($name);
                $catName = !empty($row[4]) ? trim($row[4]) : null;
                $price = !empty($row[5]) ? (float) $row[5] : 0.00;
                $salePrice = !empty($row[6]) ? (float) $row[6] : null;
                $stock = isset($row[7]) && $row[7] !== '' ? (int) $row[7] : 10;
                $status = !empty($row[8]) && in_array(strtolower($row[8]), ['published', 'draft', 'archived']) ? strtolower($row[8]) : 'published';
                $isFeatured = !empty($row[9]) && in_array(strtolower($row[9]), ['1', 'true', 'yes']);

                $categoryId = null;
                if ($catName) {
                    $cat = Category::firstOrCreate(['name' => $catName], ['slug' => Str::slug($catName)]);
                    $categoryId = $cat->id;
                }

                $existing = Product::where('sku', $sku)->orWhere('slug', $slug)->first();
                if ($existing) {
                    $existing->update([
                        'name' => $name,
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'stock' => $stock,
                        'category_id' => $categoryId ?? $existing->category_id,
                        'status' => $status,
                        'is_featured' => $isFeatured,
                    ]);
                    $updatedCount++;
                } else {
                    Product::create([
                        'name' => $name,
                        'sku' => $sku,
                        'slug' => $slug,
                        'category_id' => $categoryId,
                        'price' => $price,
                        'sale_price' => $salePrice,
                        'stock' => $stock,
                        'status' => $status,
                        'is_featured' => $isFeatured,
                    ]);
                    $importedCount++;
                }
            }

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'import_products',
                'model_type' => Product::class,
                'model_id' => null,
                'description' => "Bulk imported {$importedCount} new products, updated {$updatedCount} products from file",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$importedCount} new products and updated {$updatedCount} existing products",
                'data' => [
                    'imported' => $importedCount,
                    'updated' => $updatedCount,
                ],
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to parse import file: ' . $e->getMessage(),
            ], 500);
        }
    }
}
