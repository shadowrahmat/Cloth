<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customer Addresses
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('shipping'); // shipping, billing
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->string('address_line_1');
            $table->string('address_line_2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->string('country')->default('US');
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Shipping Methods
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->decimal('rate', 10, 2)->default(0);
            $table->decimal('free_threshold', 10, 2)->nullable();
            $table->string('estimated_delivery')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Orders
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone')->nullable();
            $table->json('shipping_address');
            $table->json('billing_address')->nullable();
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('coupon_code')->nullable();
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('payment_method')->default('cod'); // cod, stripe, card
            $table->string('payment_status')->default('unpaid'); // unpaid, pending, paid, failed, refunded
            $table->string('payment_id')->nullable();
            $table->string('order_status')->default('pending'); // pending, confirmed, processing, shipped, delivered, cancelled
            $table->string('shipping_method')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('order_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Order Items
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('product_name');
            $table->string('sku')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('quantity');
            $table->decimal('subtotal', 10, 2);
            $table->string('color')->nullable();
            $table->string('size')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });

        // Payments
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('payment_method');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('USD');
            $table->string('status')->default('pending'); // pending, successful, failed
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('customer_addresses');
    }
};
