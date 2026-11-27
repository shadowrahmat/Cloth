<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Support\SiteConfig;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminStoreEssentialsTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    private function checkoutPayload(string $method): array
    {
        return [
            'first_name' => 'Rahim', 'last_name' => 'Uddin', 'email' => 'rahim@example.com', 'phone' => '01700000000',
            'address_line_1' => 'House 1, Road 2', 'city' => 'Dhaka', 'state' => 'Dhaka', 'postal_code' => '1212',
            'country' => 'Bangladesh', 'payment_method' => $method,
        ];
    }

    // ------------------------------------------------------------ payment

    public function test_checkout_rejects_unconnected_online_payment_and_never_marks_orders_paid(): void
    {
        $session = (string) Str::uuid();
        $variant = ProductVariant::where('stock', '>', 2)->firstOrFail();
        $this->withHeader('X-Session-ID', $session)
            ->postJson('/api/v1/public/cart/add', ['product_id' => $variant->product_id, 'variant_id' => $variant->id, 'quantity' => 1])
            ->assertOk();

        foreach (['card', 'stripe'] as $fake) {
            $this->withHeader('X-Session-ID', $session)
                ->postJson('/api/v1/public/checkout', $this->checkoutPayload($fake))
                ->assertStatus(422)
                ->assertJsonValidationErrors('payment_method');
        }

        $res = $this->withHeader('X-Session-ID', $session)
            ->postJson('/api/v1/public/checkout', $this->checkoutPayload('cod'))
            ->assertStatus(201);

        $this->assertSame('pending', Order::findOrFail($res->json('data.order_id'))->payment_status);
    }

    // ------------------------------------------------------------ reviews

    public function test_new_reviews_wait_for_approval_before_they_are_shown(): void
    {
        $product = Product::where('status', 'published')->firstOrFail();

        $res = $this->postJson("/api/v1/public/products/{$product->id}/reviews", [
            'author_name' => 'Test Buyer', 'rating' => 2, 'review' => 'Spam or honest — either way an admin checks first.',
        ])->assertStatus(201);

        $review = ProductReview::findOrFail($res->json('data.id'));
        $this->assertFalse((bool) $review->is_approved);

        $shown = $this->getJson("/api/v1/public/products/{$product->slug}")->json('data.product.reviews');
        $this->assertNotContains($review->id, array_column($shown, 'id'));

        $review->update(['is_approved' => true]);
        $shown = $this->getJson("/api/v1/public/products/{$product->slug}")->json('data.product.reviews');
        $this->assertContains($review->id, array_column($shown, 'id'));
    }

    // ------------------------------------------------------ delivery charges

    public function test_admin_can_change_delivery_charges_used_by_checkout(): void
    {
        $method = ShippingMethod::where('is_active', true)->orderBy('rate')->firstOrFail();

        $this->actingAs($this->admin(), 'sanctum')
            ->putJson("/api/v1/admin/resources/shipping-methods/{$method->id}", [
                'name' => $method->name, 'code' => $method->code, 'rate' => 70,
                'estimated_delivery' => '১ দিন', 'free_threshold' => 3000, 'is_active' => true,
            ])
            ->assertOk();

        $public = collect($this->getJson('/api/v1/public/shipping-methods')->json('data'))->firstWhere('code', $method->code);
        $this->assertEquals(70, $public['rate']);
        $this->assertSame('১ দিন', $public['estimated_delivery']);
        $this->assertEquals(3000, $public['free_threshold']);
    }

    // ----------------------------------------------------------- size charts

    public function test_size_charts_are_saved_from_admin_and_served_to_the_storefront(): void
    {
        $config = SiteConfig::defaults();
        $config['size_charts'] = [
            ['name' => 'প্যান্ট', 'categories' => ['jeans-pant'], 'note' => 'কোমরের মাপ ইঞ্চিতে।',
             'columns' => ['সাইজ', 'কোমর'], 'rows' => [['30', '30'], ['32', '32']]],
            ['name' => 'সাধারণ', 'categories' => [], 'columns' => ['সাইজ', 'বুক'], 'rows' => [['M', '38']]],
        ];

        $this->actingAs($this->admin(), 'sanctum')->putJson('/api/v1/admin/appearance', $config)->assertOk();

        $charts = $this->getJson('/api/v1/public/site')->json('data.size_charts');
        $this->assertCount(2, $charts);
        $this->assertSame(['jeans-pant'], $charts[0]['categories']);
        $this->assertSame([['30', '30'], ['32', '32']], $charts[0]['rows']);
    }

    public function test_size_chart_needs_a_name_and_two_named_columns(): void
    {
        $config = SiteConfig::defaults();
        $config['size_charts'] = [['name' => '', 'columns' => ['সাইজ', ''], 'rows' => []]];

        $this->actingAs($this->admin(), 'sanctum')->putJson('/api/v1/admin/appearance', $config)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['size_charts.0.name', 'size_charts.0.columns.1']);
    }
}
