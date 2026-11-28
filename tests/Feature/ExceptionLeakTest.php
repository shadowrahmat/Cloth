<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ExceptionLeakTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_returns_clean_422_on_business_exception_and_does_not_leak_internals(): void
    {
        $session = (string) Str::uuid();
        $cart = Cart::create(['session_id' => $session]);

        $product = Product::create([
            'name' => 'Out of Stock Silk Scarf',
            'slug' => 'out-of-stock-silk-scarf',
            'price' => 1500,
            'stock' => 0,
            'track_inventory' => true,
            'status' => 'published',
        ]);

        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1500,
        ]);

        ShippingMethod::create([
            'name' => 'Standard',
            'code' => 'standard',
            'rate' => 60,
            'is_active' => true,
        ]);

        $response = $this->withHeader('X-Session-Id', $session)->postJson('/api/v1/public/checkout', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'address_line_1' => 'Dhaka',
            'city' => 'Dhaka',
            'postal_code' => '1200',
            'payment_method' => 'cod',
            'shipping_method' => 'standard',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => "Insufficient stock for 'Out of Stock Silk Scarf' (Requested: 1, Available: 0).",
        ]);
    }

    public function test_resource_controller_returns_generic_error_on_database_exception(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Attempt to create a coupon with invalid data format or triggering failure
        // Specifically, sending null or triggering internal error if unhandled
        $response = $this->actingAs($admin)->postJson('/api/v1/admin/resources/coupons', [
            'code' => 'TESTCOUPON',
            'discount_type' => 'percentage',
            'discount_value' => 20,
        ]);

        // Even on success or failure, verify it never leaks SQL or internal traces
        $content = $response->getContent();
        $this->assertStringNotContainsString('SQLSTATE', $content);
        $this->assertStringNotContainsString('QueryException', $content);
    }
}
