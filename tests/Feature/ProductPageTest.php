<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use DatabaseTransactions;

    private function makeProduct(array $attrs = []): Product
    {
        $category = Category::create(['name' => 'Test Cat ' . Str::random(5), 'slug' => 'test-cat-' . Str::lower(Str::random(8)), 'is_active' => true]);

        return Product::create(array_merge([
            'name' => 'Test Pant',
            'slug' => 'test-pant-' . Str::lower(Str::random(8)),
            'price' => 650,
            'sale_price' => 600,
            'category_id' => $category->id,
            'stock' => 50,
            'status' => 'published',
        ], $attrs));
    }

    private function addToCart(array $payload): array
    {
        return $this->withHeaders(['X-Session-ID' => 'test-' . Str::random(12)])
            ->postJson('/api/v1/public/cart/add', $payload)
            ->assertOk()
            ->json('data.items');
    }

    public function test_variant_repeating_the_regular_price_keeps_the_sale_price(): void
    {
        $product = $this->makeProduct();
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'T-30-' . Str::random(4), 'title' => 'Size 30', 'price' => 650, 'stock' => 5, 'size' => '30', 'is_active' => true]);

        $items = $this->addToCart(['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 2]);

        $this->assertEquals(600, $items[0]['price']);   // was charged 650 before the fix
    }

    public function test_variant_with_its_own_price_still_uses_that_price(): void
    {
        $product = $this->makeProduct();
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'T-XL-' . Str::random(4), 'title' => 'Size XXL', 'price' => 720, 'stock' => 5, 'size' => 'XXL', 'is_active' => true]);

        $items = $this->addToCart(['product_id' => $product->id, 'variant_id' => $variant->id, 'quantity' => 1]);

        $this->assertEquals(720, $items[0]['price']);
    }

    public function test_related_products_are_topped_up_to_four_for_small_categories(): void
    {
        $product = $this->makeProduct();   // alone in its own category

        $related = $this->getJson('/api/v1/public/products/' . $product->slug)->assertOk()->json('data.related_products');

        $ids = array_column($related, 'id');
        $this->assertCount(min(4, Product::where('status', 'published')->count() - 1), $related);
        $this->assertNotContains($product->id, $ids);
        $this->assertSame($ids, array_values(array_unique($ids)));
    }
}
