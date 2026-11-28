<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CouponUsageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        ShippingMethod::create([
            'name' => 'Standard',
            'code' => 'standard',
            'rate' => 60,
            'is_active' => true,
        ]);
    }

    public function test_coupon_atomic_limit_rejects_when_limit_exhausted(): void
    {
        $product = Product::create([
            'name' => 'Dress',
            'slug' => 'dress',
            'price' => 1000,
            'stock' => 50,
            'track_inventory' => true,
            'status' => 'published',
        ]);

        $coupon = Coupon::create([
            'code' => 'LIMITED1',
            'type' => 'fixed',
            'value' => 100,
            'usage_limit' => 1,
            'used_count' => 1,
            'is_active' => true,
        ]);

        $session = (string) Str::uuid();
        $cart = Cart::create(['session_id' => $session]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000,
        ]);

        $response = $this->withHeader('X-Session-Id', $session)->postJson('/api/v1/public/checkout', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'address_line_1' => 'Dhaka',
            'city' => 'Dhaka',
            'postal_code' => '1200',
            'payment_method' => 'cod',
            'shipping_method' => 'standard',
            'coupon_code' => 'LIMITED1',
        ]);

        $response->assertStatus(422);
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_coupon_per_user_limit_enforced(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
        ]);

        $product = Product::create([
            'name' => 'Shoes',
            'slug' => 'shoes',
            'price' => 2000,
            'stock' => 10,
            'track_inventory' => true,
            'status' => 'published',
        ]);

        $coupon = Coupon::create([
            'code' => 'ONCEPERUSER',
            'type' => 'percentage',
            'value' => 10,
            'usage_limit' => 100,
            'per_user_limit' => 1,
            'used_count' => 1,
            'is_active' => true,
        ]);

        // Prior order exists for this user with this coupon
        Order::create([
            'order_number' => 'ORD-TEST-1',
            'user_id' => $user->id,
            'customer_name' => 'Existing Customer',
            'customer_email' => $user->email,
            'shipping_address' => ['address' => 'Dhaka'],
            'billing_address' => ['address' => 'Dhaka'],
            'coupon_code' => 'ONCEPERUSER',
            'subtotal' => 2000,
            'discount' => 200,
            'shipping_cost' => 60,
            'tax' => 0,
            'total' => 1860,
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'order_status' => 'pending',
        ]);

        $session = (string) Str::uuid();
        $cart = Cart::create([
            'user_id' => $user->id,
            'session_id' => $session,
        ]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 2000,
        ]);

        // Attempt checkout as the same user
        $response = $this->actingAs($user, 'sanctum')
            ->withHeader('X-Session-Id', $session)
            ->postJson('/api/v1/public/checkout', [
                'first_name' => 'Customer',
                'last_name' => 'One',
                'email' => 'customer@example.com',
                'address_line_1' => 'Dhaka',
                'city' => 'Dhaka',
                'postal_code' => '1200',
                'payment_method' => 'cod',
                'shipping_method' => 'standard',
                'coupon_code' => 'ONCEPERUSER',
            ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'The applied coupon is invalid, expired, or has reached its usage limit.',
        ]);
    }
}
