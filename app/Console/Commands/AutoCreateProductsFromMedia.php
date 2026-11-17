<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\MediaFile;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class AutoCreateProductsFromMedia extends Command
{
    protected $signature = 'cloth:auto-create-products';
    protected $description = 'Automatically create and publish catalog products from uploaded media library assets';

    public function handle(): int
    {
        $this->info('Scanning uploaded media files...');
        $mediaFiles = MediaFile::orderBy('id', 'asc')->get();

        if ($mediaFiles->isEmpty()) {
            $this->warn('No media files found in the media library.');
            return 0;
        }

        // Group media files by base product name
        $groups = [];
        foreach ($mediaFiles as $media) {
            $rawName = $media->name;
            // Clean up suffixes like (1), (2), (size_M), (price_499), etc.
            $baseName = preg_replace('/\s*\(\d+\)$/', '', $rawName);
            $baseName = trim($baseName);

            // Group key
            if (stripos($baseName, '2 Quarter') !== false) {
                $groupKey = '2 Quarter Tailored Casual Pant';
                $categorySlug = 'men-trousers';
                $price = 450;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'baggy pant') !== false) {
                $groupKey = 'Men Baggy Fit Casual Pant';
                $categorySlug = 'men-trousers';
                $price = 650;
                $sizes = ['28', '30', '32', '34'];
            } elseif (stripos($baseName, 'boxy fit') !== false) {
                $groupKey = 'Boxy Fit Heavyweight Cotton T-Shirt';
                $categorySlug = 'men-t-shirts';
                $price = 790;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'Drop shoulder') !== false) {
                $groupKey = 'Drop Shoulder Oversized T-Shirt';
                $categorySlug = 'men-t-shirts';
                $price = 490;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'Chery Fabric') !== false) {
                $groupKey = 'Chery Fabric Textured T-Shirt';
                $categorySlug = 'men-t-shirts';
                $price = 790;
                $sizes = ['M', 'L', 'XL', '2XL', '3XL'];
            } elseif (stripos($baseName, 'Jafran') !== false) {
                $groupKey = 'Jafran Fabrics Premium Casual Shirt';
                $categorySlug = 'men-shirts';
                $price = 690;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'Price (690)') !== false || stripos($baseName, 'Price (690') !== false) {
                $groupKey = 'Signature Casual Print T-Shirt';
                $categorySlug = 'men-t-shirts';
                $price = 690;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, '950') !== false) {
                $groupKey = 'Premium Denim & Chino Pants';
                $categorySlug = 'men-trousers';
                $price = 950;
                $sizes = ['28', '30', '32', '34', '36'];
            } elseif (stripos($baseName, '700') !== false) {
                $groupKey = 'Smart Casual Printed Shirt';
                $categorySlug = 'men-shirts';
                $price = 700;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, '1499') !== false) {
                $groupKey = 'Exclusive Heritage Panjabi Collection';
                $categorySlug = 'men-blazers';
                $price = 1499;
                $sizes = ['40', '42', '44'];
            } elseif (stripos($baseName, 'Polo t-shirt') !== false) {
                $groupKey = 'Classic Pique Polo T-Shirt';
                $categorySlug = 'men-t-shirts';
                $price = 650;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'Price_650') !== false || stripos($baseName, 'price_650') !== false || stripos($baseName, '(Price_650)') !== false) {
                $groupKey = 'Essential Daily Comfort T-Shirt';
                $categorySlug = 'men-t-shirts';
                $price = 650;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'Price_400') !== false || stripos($baseName, 'price_400') !== false || stripos($baseName, '(Price_400)') !== false) {
                $groupKey = 'Basic Crewneck Summer Tee';
                $categorySlug = 'men-t-shirts';
                $price = 400;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'adidas') !== false) {
                $groupKey = 'Adidas Athletic Performance Trouser';
                $categorySlug = 'men-trousers';
                $price = 590;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'nike') !== false) {
                $groupKey = 'Nike Dry-Fit Sport Trouser';
                $categorySlug = 'men-trousers';
                $price = 590;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 't90') !== false) {
                $groupKey = 'T90 Football Training Trouser';
                $categorySlug = 'men-trousers';
                $price = 590;
                $sizes = ['M', 'L', 'XL'];
            } elseif (stripos($baseName, 'T-shirt (price_750') !== false) {
                $groupKey = 'Premium Combed Cotton T-Shirt';
                $categorySlug = 'men-t-shirts';
                $price = 750;
                $sizes = ['M', 'L', 'XL', '2XL'];
            } elseif (stripos($baseName, 'Wallet') !== false) {
                $groupKey = 'Genuine Leather Slim Bifold Wallet';
                $categorySlug = 'winter-accessories';
                $price = 499;
                $sizes = ['Standard'];
            } else {
                continue;
            }

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'title' => $groupKey,
                    'category_slug' => $categorySlug,
                    'price' => $price,
                    'sizes' => $sizes,
                    'images' => [],
                ];
            }

            $groups[$groupKey]['images'][] = $media->url;
        }

        $this->info("Identified " . count($groups) . " distinct product collections to create.");

        $createdCount = 0;
        foreach ($groups as $name => $data) {
            $category = Category::where('slug', $data['category_slug'])->first();
            $categoryId = $category ? $category->id : null;

            $slug = Str::slug($name);
            $featuredImage = $data['images'][0] ?? null;
            $gallery = array_slice($data['images'], 1);

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'sku' => 'SKU-' . strtoupper(Str::random(8)),
                    'price' => $data['price'],
                    'sale_price' => $data['price'] > 500 ? $data['price'] - 50 : null,
                    'stock' => 50,
                    'category_id' => $categoryId,
                    'featured_image' => $featuredImage,
                    'gallery' => $gallery,
                    'short_description' => "High-quality {$name} manufactured with premium stitching and comfort.",
                    'description' => "Designed for effortless everyday wear and durability. Crafted with breathable fabrics and modern tailoring.",
                    'is_featured' => true,
                    'is_bestseller' => ($createdCount % 2 === 0),
                    'is_new' => true,
                    'status' => 'published',
                ]
            );

            // Sync size variants
            $product->variants()->delete();
            foreach ($data['sizes'] as $idx => $size) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $product->sku . '-' . $size,
                    'title' => "Size {$size}",
                    'size' => $size,
                    'price' => $product->price,
                    'stock' => 15,
                    'image' => $featuredImage,
                    'is_active' => true,
                ]);
            }

            $createdCount++;
            $this->line("✓ Created Product: <info>{$name}</info> (\${$data['price']} | " . count($data['images']) . " photos)");
        }

        $this->info("Successfully created and published {$createdCount} products from your uploaded media!");
        return 0;
    }
}
