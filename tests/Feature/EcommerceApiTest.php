<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\HomepageSection;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use Tests\TestCase;

class EcommerceApiTest extends TestCase
{
    public function test_storage_serves_uploaded_file(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->get('/storage/uploads/2-quarter-pant-02-1794820446.jpg');
        $response->assertStatus(200);
    }

    public function test_admin_bulk_upload_multiple_media_files(): void
    {
        $admin = User::where('role', 'admin')->first();

        $file1 = \Illuminate\Http\UploadedFile::fake()->image('look1.jpg', 600, 800);
        $file2 = \Illuminate\Http\UploadedFile::fake()->image('look2.jpg', 600, 800);
        $file3 = \Illuminate\Http\UploadedFile::fake()->image('look3.jpg', 600, 800);

        $res = $this->actingAs($admin, 'sanctum')->post('/api/v1/admin/media/upload', [
            'files' => [$file1, $file2, $file3],
        ]);

        $res->assertStatus(201)
            ->assertJsonPath('success', true);

        $data = $res->json('data');
        $this->assertCount(3, $data);
    }

    public function test_admin_can_create_and_publish_product(): void
    {
        $admin = User::where('role', 'admin')->first();
        $category = Category::whereNotNull('parent_id')->first();
        $uniqueName = '2 Quarter Pant Test ' . time();

        $productData = [
            'name' => $uniqueName,
            'category_id' => $category->id,
            'price' => 45.00,
            'sale_price' => 39.99,
            'stock' => 25,
            'featured_image' => url('/storage/uploads/2-quarter-pant-02-1794820446.jpg'),
            'short_description' => 'Lightweight tailored quarter pant',
            'description' => 'Crafted from premium blended fibers with a relaxed contemporary silhouette.',
            'is_featured' => true,
            'is_bestseller' => false,
            'is_new' => true,
            'status' => 'published',
        ];

        $res = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/products', $productData);

        $res->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', $uniqueName);

        $this->assertDatabaseHas('products', [
            'name' => $uniqueName,
            'status' => 'published',
        ]);
    }

    public function test_public_homepage_endpoint_returns_sections(): void
    {
        $response = $this->getJson('/api/v1/public/homepage');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'sections',
                    'featured_categories',
                    'new_arrivals',
                    'best_sellers',
                    'testimonials',
                    'social_gallery',
                ]
            ]);

        $sections = $response->json('data.sections');
        $this->assertNotEmpty($sections);
    }

    public function test_public_categories_endpoint_returns_hierarchy(): void
    {
        $response = $this->getJson('/api/v1/public/categories');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $categories = $response->json('data');
        $this->assertNotEmpty($categories);

        // Check for top-level slugs: men, women, kids, winter
        $slugs = array_column($categories, 'slug');
        $this->assertContains('men', $slugs);
        $this->assertContains('women', $slugs);
        $this->assertContains('kids', $slugs);
        $this->assertContains('winter', $slugs);
    }

    public function test_public_products_endpoint_returns_paginated_list(): void
    {
        $response = $this->getJson('/api/v1/public/products');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'last_page', 'per_page', 'total']
            ]);

        $this->assertGreaterThan(0, $response->json('meta.total'));
    }

    public function test_public_product_detail_endpoint(): void
    {
        $product = Product::with('variants')->first();
        $this->assertNotNull($product);

        $response = $this->getJson("/api/v1/public/products/{$product->slug}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.product.slug', $product->slug);
    }

    public function test_cart_workflow_add_update_view(): void
    {
        // 1. Initial cart retrieval
        $cartRes = $this->getJson('/api/v1/public/cart');
        $cartRes->assertStatus(200);

        // 2. Add an item
        $variant = ProductVariant::first();
        $this->assertNotNull($variant);

        $addRes = $this->postJson('/api/v1/public/cart/add', [
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $addRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $items = $addRes->json('data.items');
        $this->assertNotEmpty($items);
        $cartItemId = $items[0]['id'];

        // 3. Update quantity
        $updateRes = $this->putJson("/api/v1/public/cart/items/{$cartItemId}", [
            'quantity' => 3,
        ]);
        $updateRes->assertStatus(200)
            ->assertJsonPath('data.items.0.quantity', 3);
    }

    public function test_checkout_places_order_and_deducts_inventory(): void
    {
        $variant = ProductVariant::where('stock', '>', 5)->first();
        $this->assertNotNull($variant);
        $initialStock = $variant->stock;

        // Add to cart
        $this->postJson('/api/v1/public/cart/add', [
            'product_id' => $variant->product_id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        // Checkout payload
        $checkoutData = [
            'first_name' => 'Claire',
            'last_name' => 'Delacroix',
            'email' => 'claire.delacroix@example.com',
            'phone' => '+1 (555) 234-5678',
            'address_line_1' => '742 Evergreen Terrace',
            'city' => 'Springfield',
            'state' => 'OR',
            'postal_code' => '97477',
            'country' => 'United States',
            'payment_method' => 'card',
        ];

        $checkoutRes = $this->postJson('/api/v1/public/checkout', $checkoutData);

        $checkoutRes->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['order_id', 'order_number', 'total', 'customer_email', 'payment_method']
            ]);

        $orderNumber = $checkoutRes->json('data.order_number');
        $this->assertNotEmpty($orderNumber);

        // Verify stock deducted
        $variant->refresh();
        $this->assertEquals($initialStock - 2, $variant->stock);

        // Verify order tracking
        $trackRes = $this->postJson('/api/v1/public/track-order', [
            'order_number' => $orderNumber,
            'email' => 'claire.delacroix@example.com',
        ]);
        $trackRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.order_number', $orderNumber);
    }

    public function test_admin_authentication_and_dashboard(): void
    {
        // Login as admin
        $loginRes = $this->postJson('/api/v1/admin/login', [
            'email' => 'admin@cloth.test',
            'password' => 'password',
        ]);

        $loginRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['token', 'user']);

        $token = $loginRes->json('token');

        // Access Dashboard
        $dashRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/admin/dashboard');

        $dashRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'stats' => ['total_sales', 'total_orders', 'total_customers', 'total_products', 'low_stock_count'],
                    'recent_orders',
                    'sales_chart'
                ]
            ]);
    }

    public function test_admin_generic_resource_registry_crud_operations(): void
    {
        $admin = User::where('role', 'admin')->first();
        $this->assertNotNull($admin);

        // 1. Index categories via generic ResourceController
        $indexRes = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/resources/categories');

        $indexRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'config' => ['label', 'singular'],
                'data'
            ]);

        // 2. Store new category
        $slug = 'haute-couture-test-' . time();
        $storeRes = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/v1/admin/resources/categories', [
                'name' => 'Haute Couture Test',
                'slug' => $slug,
                'description' => 'Test couture collection',
                'is_active' => true,
            ]);

        $storeRes->assertStatus(201)
            ->assertJsonPath('success', true);

        $categoryId = $storeRes->json('data.id');
        $this->assertNotNull($categoryId);

        // 3. Show category
        $showRes = $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/admin/resources/categories/{$categoryId}");

        $showRes->assertStatus(200)
            ->assertJsonPath('data.name', 'Haute Couture Test');

        // 4. Update category
        $updateRes = $this->actingAs($admin, 'sanctum')
            ->putJson("/api/v1/admin/resources/categories/{$categoryId}", [
                'name' => 'Haute Couture Updated',
                'slug' => $slug,
                'is_active' => false,
            ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('data.name', 'Haute Couture Updated')
            ->assertJsonPath('data.is_active', false);

        // 5. Delete category
        $deleteRes = $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/admin/resources/categories/{$categoryId}");

        $deleteRes->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }

    public function test_admin_homepage_builder_sections_endpoint(): void
    {
        $admin = User::where('role', 'admin')->first();

        $response = $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/admin/homepage-builder');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $sections = $response->json('data');
        $this->assertCount(13, $sections);

        $sectionTypes = array_column($sections, 'type');
        $this->assertContains('hero', $sectionTypes);
        $this->assertContains('category_grid', $sectionTypes);
        $this->assertContains('editorial', $sectionTypes);
        $this->assertContains('brand_story', $sectionTypes);
        $this->assertContains('testimonials', $sectionTypes);
        $this->assertContains('instagram_gallery', $sectionTypes);
        $this->assertContains('newsletter', $sectionTypes);
    }
}
