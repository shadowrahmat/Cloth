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

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 12);
        $products = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug',
            'sku' => 'nullable|string|max:100|unique:products,sku',
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'featured_image' => 'nullable|string',
            'gallery' => 'nullable|array',
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
            'seo_description' => 'nullable|string',
            'variants' => 'nullable|array',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }
        if (empty($validated['sku'])) {
            $validated['sku'] = 'SKU-' . strtoupper(Str::random(8));
        }

        DB::beginTransaction();
        try {
            $variantsData = $validated['variants'] ?? [];
            unset($validated['variants']);

            $product = Product::create($validated);

            // Handle variants
            $totalStock = $product->stock;
            if (!empty($variantsData)) {
                $variantStockSum = 0;
                foreach ($variantsData as $v) {
                    $vSku = !empty($v['sku']) ? $v['sku'] : $product->sku . '-' . strtoupper(Str::random(4));
                    $vStock = isset($v['stock']) ? (int) $v['stock'] : 10;
                    $variantStockSum += $vStock;

                    $product->variants()->create([
                        'sku' => $vSku,
                        'title' => $v['title'] ?? 'Standard',
                        'price' => $v['price'] ?? $product->price,
                        'compare_at_price' => $v['compare_at_price'] ?? $product->compare_at_price,
                        'stock' => $vStock,
                        'color' => $v['color'] ?? null,
                        'color_code' => $v['color_code'] ?? null,
                        'size' => $v['size'] ?? null,
                        'image' => $v['image'] ?? $product->featured_image,
                        'is_active' => $v['is_active'] ?? true,
                    ]);
                }
                if ($variantStockSum > 0) {
                    $product->update(['stock' => $variantStockSum]);
                    $totalStock = $variantStockSum;
                }
            }

            // Inventory Movement log
            InventoryMovement::create([
                'product_id' => $product->id,
                'type' => 'initial',
                'quantity' => $totalStock,
                'previous_stock' => 0,
                'current_stock' => $totalStock,
                'notes' => 'Initial stock on product creation',
                'created_by' => $request->user()?->id,
            ]);

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'create_product',
                'model_type' => Product::class,
                'model_id' => $product->id,
                'description' => "Created product '{$product->name}' (SKU: {$product->sku})",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product->load(['category', 'brand', 'variants']),
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create product: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function show(string|int $id): JsonResponse
    {
        $product = Product::with(['category', 'brand', 'collections', 'variants', 'reviews'])->find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        return response()->json(['success' => true, 'data' => $product]);
    }

    public function update(Request $request, string|int $id): JsonResponse
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:products,slug,' . $id,
            'sku' => 'nullable|string|max:100|unique:products,sku,' . $id,
            'short_description' => 'nullable|string',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'featured_image' => 'nullable|string',
            'gallery' => 'nullable|array',
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
            'seo_description' => 'nullable|string',
            'variants' => 'nullable|array',
        ]);

        DB::beginTransaction();
        try {
            $variantsData = $validated['variants'] ?? null;
            unset($validated['variants']);

            $product->update($validated);

            if ($variantsData !== null) {
                // Delete removed or recreate
                $product->variants()->delete();
                $variantStockSum = 0;
                foreach ($variantsData as $v) {
                    $vSku = !empty($v['sku']) ? $v['sku'] : $product->sku . '-' . strtoupper(Str::random(4));
                    $vStock = isset($v['stock']) ? (int) $v['stock'] : 10;
                    $variantStockSum += $vStock;

                    $product->variants()->create([
                        'sku' => $vSku,
                        'title' => $v['title'] ?? 'Standard',
                        'price' => $v['price'] ?? $product->price,
                        'compare_at_price' => $v['compare_at_price'] ?? $product->compare_at_price,
                        'stock' => $vStock,
                        'color' => $v['color'] ?? null,
                        'color_code' => $v['color_code'] ?? null,
                        'size' => $v['size'] ?? null,
                        'image' => $v['image'] ?? $product->featured_image,
                        'is_active' => $v['is_active'] ?? true,
                    ]);
                }
                if ($variantStockSum > 0) {
                    $product->update(['stock' => $variantStockSum]);
                }
            }

            ActivityLog::create([
                'user_id' => $request->user()?->id,
                'action' => 'update_product',
                'model_type' => Product::class,
                'model_id' => $product->id,
                'description' => "Updated product '{$product->name}'",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product->load(['category', 'brand', 'variants']),
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update product: ' . $e->getMessage(),
            ], 500);
        }
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
