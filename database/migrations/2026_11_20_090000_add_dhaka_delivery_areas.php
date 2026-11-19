<?php

use App\Models\ShippingMethod;
use Illuminate\Database\Migrations\Migration;

/**
 * Real delivery areas for Bangladesh, used by the cart drawer's "ডেলিভারি এরিয়া"
 * picker and by checkout. The old English demo methods (standard / express)
 * are switched off, not deleted, so past orders keep their reference.
 * Free delivery above ৳2500 matches the site's announced promise.
 */
return new class extends Migration
{
    private const AREAS = [
        'inside_dhaka' => ['name' => 'ঢাকার ভেতরে', 'rate' => 60, 'estimated_delivery' => '১–২ দিন', 'description' => 'ঢাকা সিটি কর্পোরেশনের ভেতরে হোম ডেলিভারি'],
        'outside_dhaka' => ['name' => 'ঢাকার বাইরে', 'rate' => 120, 'estimated_delivery' => '৩–৫ দিন', 'description' => 'সারাদেশে কুরিয়ারে হোম ডেলিভারি'],
    ];

    public function up(): void
    {
        foreach (self::AREAS as $code => $area) {
            ShippingMethod::updateOrCreate(['code' => $code], $area + ['free_threshold' => 2500, 'is_active' => true]);
        }
        ShippingMethod::whereIn('code', ['standard', 'express'])->update(['is_active' => false]);
    }

    public function down(): void
    {
        ShippingMethod::whereIn('code', array_keys(self::AREAS))->delete();
        ShippingMethod::whereIn('code', ['standard', 'express'])->update(['is_active' => true]);
    }
};
