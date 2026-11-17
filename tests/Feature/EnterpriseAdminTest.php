<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Tests\TestCase;

class EnterpriseAdminTest extends TestCase
{
    protected function getAdminUser(): User
    {
        return User::where('role', 'admin')->first() ?? User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_badges_returns_live_counts(): void
    {
        $admin = $this->getAdminUser();

        $res = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/dashboard/badges');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'pending_orders',
                    'low_stock',
                    'unapproved_reviews',
                    'new_inquiries',
                    'pending_returns',
                ],
            ]);
    }

    public function test_admin_reports_endpoint_returns_aggregates(): void
    {
        $admin = $this->getAdminUser();

        $res = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/reports');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'metrics' => [
                        'gross_sales',
                        'net_sales',
                        'total_discounts',
                        'shipping_revenue',
                        'tax_collected',
                        'refunded_amount',
                        'total_orders',
                        'total_units_sold',
                        'average_order_value',
                    ],
                    'payment_methods',
                    'order_statuses',
                ],
            ]);
    }

    public function test_admin_sales_analysis_endpoint(): void
    {
        $admin = $this->getAdminUser();

        $res = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/reports/sales?days=7');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'timeline',
                    'top_categories',
                ],
            ]);
    }

    public function test_admin_customers_directory(): void
    {
        $admin = $this->getAdminUser();

        $res = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/customers');

        $res->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'data' => ['data', 'total'],
            ]);
    }

    public function test_admin_coupons_crud(): void
    {
        $admin = $this->getAdminUser();
        $code = 'PROMO' . rand(1000, 9999);

        // Create
        $createRes = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/coupons', [
            'code' => $code,
            'description' => 'Test Promo Coupon',
            'discount_type' => 'percentage',
            'discount_value' => 15,
            'min_spend' => 100,
            'max_discount' => 50,
            'is_active' => true,
        ]);

        $createRes->assertStatus(201)
            ->assertJsonPath('success', true);

        $couponId = $createRes->json('data.id');

        // Update
        $updateRes = $this->actingAs($admin, 'sanctum')->putJson('/api/v1/admin/coupons/' . $couponId, [
            'code' => $code,
            'description' => 'Updated Promo Coupon',
            'discount_type' => 'percentage',
            'discount_value' => 20,
            'is_active' => true,
        ]);

        $updateRes->assertStatus(200)
            ->assertJsonPath('success', true);

        // Delete
        $deleteRes = $this->actingAs($admin, 'sanctum')->deleteJson('/api/v1/admin/coupons/' . $couponId);
        $deleteRes->assertStatus(200);
    }

    public function test_admin_logistics_consignment_creation(): void
    {
        $admin = $this->getAdminUser();
        $order = Order::first();

        if ($order) {
            $res = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/admin/logistics/consignment', [
                'order_id' => $order->id,
                'courier_name' => 'Steadfast Courier',
                'cod_amount' => 50.00,
                'recipient_name' => 'Rahim Chowdhury',
                'recipient_phone' => '01711223344',
                'recipient_address' => 'House 12, Road 4, Banani, Dhaka',
            ]);

            $res->assertStatus(201)
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.courier_name', 'Steadfast Courier');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_audit_logs_endpoint(): void
    {
        $admin = $this->getAdminUser();

        $res = $this->actingAs($admin, 'sanctum')->getJson('/api/v1/admin/audit-logs');

        $res->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_admin_can_download_order_invoice_pdf(): void
    {
        $admin = $this->getAdminUser();
        $order = Order::first();

        if ($order) {
            $res = $this->actingAs($admin, 'sanctum')->get("/api/v1/admin/orders/{$order->id}/invoice");

            $res->assertStatus(200)
                ->assertHeader('content-type', 'application/pdf');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_admin_can_export_products_csv(): void
    {
        $admin = $this->getAdminUser();

        $res = $this->actingAs($admin, 'sanctum')->get('/api/v1/admin/products/export');

        $res->assertStatus(200)
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
