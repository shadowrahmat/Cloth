<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\ReturnRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminConsoleTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    private function makeOrder(array $attrs = []): Order
    {
        return Order::create(array_merge([
            'order_number' => 'CMD-'.Str::upper(Str::random(8)),
            'customer_name' => 'Palette Tester',
            'customer_email' => 'palette@example.test',
            'customer_phone' => '01700000000',
            'shipping_address' => ['address_line_1' => '1 Road', 'city' => 'Dhaka'],
            'subtotal' => 500,
            'total' => 500,
            'order_status' => 'pending',
        ], $attrs));
    }

    public function test_search_finds_orders_by_number_and_customer(): void
    {
        $order = $this->makeOrder();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/v1/admin/search?q='.$order->order_number)
            ->assertOk()
            ->assertJsonPath('data.orders.0.order_number', $order->order_number);

        $names = collect($this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/v1/admin/search?q=Palette Tester')
            ->json('data.orders'))->pluck('order_number');
        $this->assertContains($order->order_number, $names);
    }

    public function test_search_ignores_too_short_queries(): void
    {
        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/v1/admin/search?q=a')
            ->assertOk()
            ->assertExactJson(['success' => true, 'data' => ['orders' => [], 'products' => [], 'customers' => []]]);
    }

    public function test_search_treats_wildcards_literally(): void
    {
        // "%%" must not match every order.
        $this->makeOrder();

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/v1/admin/search?q='.urlencode('%%'))
            ->assertOk()
            ->assertJsonCount(0, 'data.orders');
    }

    public function test_search_is_admin_only(): void
    {
        $customer = User::where('role', 'customer')->firstOrFail();

        $this->actingAs($customer, 'sanctum')->getJson('/api/v1/admin/search?q=test')->assertForbidden();
    }

    public function test_badges_count_pending_returns(): void
    {
        $before = $this->actingAs($this->admin(), 'sanctum')->getJson('/api/v1/admin/dashboard/badges')->json('data.pending_returns');

        $order = $this->makeOrder(['order_status' => 'delivered']);
        ReturnRequest::create([
            'return_number' => 'RMA-TEST-'.Str::upper(Str::random(5)),
            'order_id' => $order->id,
            'reason' => 'Size',
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin(), 'sanctum')
            ->getJson('/api/v1/admin/dashboard/badges')
            ->assertOk()
            ->assertJsonPath('data.pending_returns', $before + 1);
    }

    public function test_dashboard_reports_week_over_week_and_excludes_cancelled(): void
    {
        $res = $this->actingAs($this->admin(), 'sanctum')->getJson('/api/v1/admin/dashboard')->assertOk();
        $before = $res->json('data.stats.sales_today');

        $this->makeOrder(['total' => 700]);
        $this->makeOrder(['total' => 900, 'order_status' => 'cancelled']);

        $stats = $this->actingAs($this->admin(), 'sanctum')->getJson('/api/v1/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'stats' => ['sales_today', 'orders_today', 'sales_week', 'sales_week_change', 'orders_week', 'orders_week_change', 'collected'],
                'low_stock_products',
            ]])
            ->json('data.stats');

        $this->assertEquals($before + 700, $stats['sales_today']);
    }
}
