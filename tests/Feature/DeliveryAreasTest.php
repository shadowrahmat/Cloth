<?php

namespace Tests\Feature;

use App\Models\ShippingMethod;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DeliveryAreasTest extends TestCase
{
    use DatabaseTransactions;

    public function test_migration_offers_inside_and_outside_dhaka_and_hides_demo_methods(): void
    {
        $migration = require database_path('migrations/2026_11_20_090000_add_dhaka_delivery_areas.php');
        $migration->up();

        $methods = $this->getJson('/api/v1/public/shipping-methods')->assertOk()->json('data');

        $this->assertSame(['inside_dhaka', 'outside_dhaka'], array_column($methods, 'code'));   // cheapest first = default
        $this->assertEquals(60, $methods[0]['rate']);
        $this->assertEquals(120, $methods[1]['rate']);
        $this->assertFalse((bool) ShippingMethod::where('code', 'standard')->value('is_active'));

        // Rolling back restores the old state
        $migration->down();
        $this->assertNull(ShippingMethod::where('code', 'inside_dhaka')->first());
    }
}
