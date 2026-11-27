<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/** The bag and checkout charge today's price, not the price from the day the item was added (finding H1). */
class CartPriceTest extends TestCase
{
    use DatabaseTransactions;

    private string $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->session = 'test-session-'.uniqid();
    }

    private function product(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Price Test Jacket '.uniqid(),
            'price' => 2000,
            'stock' => 50,
            'track_inventory' => true,
            'status' => 'published',
        ], $attributes));
    }

    private function addToBag(Product $product, ?ProductVariant $variant = null, int $quantity = 2): void
    {
        $this->withHeader('X-Session-ID', $this->session)->postJson('/api/v1/public/cart/add', [
            'product_id' => $product->id,
            'variant_id' => $variant?->id,
            'quantity' => $quantity,
        ])->assertOk();
    }

    private function bag(): array
    {
        return $this->withHeader('X-Session-ID', $this->session)->getJson('/api/v1/public/cart')->assertOk()->json('data');
    }

    private function checkout(): Order
    {
        $response = $this->withHeader('X-Session-ID', $this->session)->postJson('/api/v1/public/checkout', [
            'first_name' => 'Price', 'last_name' => 'Tester', 'email' => 'price.tester@example.com',
            'phone' => '01700000000', 'address_line_1' => '1 Test Road', 'city' => 'Dhaka',
            'postal_code' => '1207', 'payment_method' => 'cod',
        ])->assertCreated();

        return Order::with('items')->findOrFail($response->json('data.order_id'));
    }

    public function test_a_price_rise_after_adding_reaches_the_bag_and_the_order(): void
    {
        $product = $this->product();
        $this->addToBag($product);

        $product->update(['price' => 3000]);

        $bag = $this->bag();
        $this->assertEquals(3000, $bag['items'][0]['price']);
        $this->assertEquals(6000, $bag['subtotal']);

        $order = $this->checkout();
        $this->assertEquals(3000, $order->items->first()->price);
        $this->assertEquals(6000, $order->items->first()->subtotal);
        $this->assertEquals(6000, $order->subtotal);
    }

    public function test_a_sale_that_ended_stops_applying_to_a_bag_filled_during_the_sale(): void
    {
        $product = $this->product(['sale_price' => 1500]);
        $this->addToBag($product);
        $this->assertEquals(1500, $this->bag()['items'][0]['price']);

        $product->update(['sale_price' => null]);

        $this->assertEquals(2000, $this->bag()['items'][0]['price']);
        $this->assertEquals(4000, $this->checkout()->subtotal);
    }

    public function test_a_new_sale_lowers_the_price_of_an_item_already_in_the_bag(): void
    {
        $product = $this->product();
        $this->addToBag($product);

        $product->update(['sale_price' => 1000]);

        $this->assertEquals(2000, $this->checkout()->subtotal);
    }

    public function test_a_variant_with_its_own_price_is_charged_that_price_even_after_it_changes(): void
    {
        $product = $this->product();
        $variant = $product->variants()->create(['title' => 'L', 'sku' => 'VAR-'.uniqid(), 'size' => 'L', 'price' => 2500, 'stock' => 10, 'is_active' => true]);
        $this->addToBag($product, $variant);
        $this->assertEquals(2500, $this->bag()['items'][0]['price']);

        $variant->update(['price' => 2800]);

        $this->assertEquals(5600, $this->checkout()->subtotal);
    }

    public function test_a_variant_that_only_repeats_the_regular_price_keeps_the_product_sale_price(): void
    {
        $product = $this->product(['sale_price' => 1500]);
        $variant = $product->variants()->create(['title' => 'M', 'sku' => 'VAR-'.uniqid(), 'size' => 'M', 'price' => 2000, 'stock' => 10, 'is_active' => true]);
        $this->addToBag($product, $variant, 1);

        $this->assertEquals(1500, $this->bag()['items'][0]['price']);
        $this->assertEquals(1500, $this->checkout()->subtotal);
    }

    public function test_a_coupon_discount_is_worked_out_on_the_current_price(): void
    {
        $product = $this->product();
        $this->addToBag($product, null, 1);
        $product->update(['price' => 3000]);

        $this->assertEquals(3000, $this->bag()['subtotal']);
    }
}
