<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Upgrade contact_messages with B2B Wholesale and Attachment Support
        Schema::table('contact_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('contact_messages', 'type')) {
                $table->string('type')->default('general')->after('email'); // general, wholesale, inquiry
            }
            if (!Schema::hasColumn('contact_messages', 'company')) {
                $table->string('company')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('contact_messages', 'status')) {
                $table->string('status')->default('new')->after('is_read'); // new, in_progress, resolved, archived
            }
            if (!Schema::hasColumn('contact_messages', 'attachment_path')) {
                $table->string('attachment_path')->nullable()->after('message');
            }
            if (!Schema::hasColumn('contact_messages', 'internal_notes')) {
                $table->text('internal_notes')->nullable()->after('attachment_path');
            }
            if (!Schema::hasColumn('contact_messages', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->after('internal_notes')->constrained('users')->nullOnDelete();
            }
        });

        // 2. Returns & Exchanges Management
        if (!Schema::hasTable('return_requests')) {
            Schema::create('return_requests', function (Blueprint $table) {
                $table->id();
                $table->string('return_number')->unique();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('type')->default('return'); // return, exchange
                $table->string('reason');
                $table->string('condition')->nullable(); // unopened, defective, wrong_item, size_issue
                $table->string('status')->default('pending'); // pending, approved, rejected, completed
                $table->decimal('refund_amount', 10, 2)->default(0);
                $table->string('replacement_product')->nullable();
                $table->json('images')->nullable();
                $table->text('customer_notes')->nullable();
                $table->text('admin_notes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('return_items')) {
            Schema::create('return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('return_request_id')->constrained()->cascadeOnDelete();
                $table->foreignId('order_item_id')->constrained()->cascadeOnDelete();
                $table->integer('quantity')->default(1);
                $table->decimal('unit_price', 10, 2);
                $table->timestamps();
            });
        }

        // 3. Courier Logistics & Consignments (Steadfast Courier API)
        if (!Schema::hasTable('shipments')) {
            Schema::create('shipments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('courier_name')->default('steadfast'); // steadfast, redx, pathao, custom
                $table->string('consignment_id')->nullable()->index();
                $table->string('tracking_code')->nullable()->index();
                $table->string('status')->default('pending'); // pending, consignment_created, in_transit, delivered, returned, failed
                $table->string('recipient_name');
                $table->string('recipient_phone');
                $table->text('recipient_address');
                $table->decimal('cod_amount', 10, 2)->default(0);
                $table->json('courier_response')->nullable();
                $table->timestamp('dispatched_at')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. Blog & Editorial Engine
        if (!Schema::hasTable('blog_categories')) {
            Schema::create('blog_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('blog_posts')) {
            Schema::create('blog_posts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('slug')->unique();
                $table->foreignId('category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
                $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('featured_image')->nullable();
                $table->text('excerpt')->nullable();
                $table->longText('content')->nullable();
                $table->string('status')->default('draft'); // draft, published, scheduled
                $table->timestamp('published_at')->nullable();
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->unsignedBigInteger('views_count')->default(0);
                $table->timestamps();
            });
        }

        // 5. Shipping Zones & Rates Matrix
        if (!Schema::hasTable('shipping_zones')) {
            Schema::create('shipping_zones', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // Inside Dhaka, Outside Dhaka, Suburb, International
                $table->string('code')->unique();
                $table->json('locations')->nullable(); // districts / areas
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shipping_rates')) {
            Schema::create('shipping_rates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('zone_id')->constrained('shipping_zones')->cascadeOnDelete();
                $table->string('rate_name'); // Standard Courier, Express Delivery
                $table->decimal('rate', 10, 2)->default(0);
                $table->decimal('free_threshold', 10, 2)->nullable();
                $table->string('estimated_delivery')->nullable(); // 24-48 hours
                $table->boolean('is_delivery_charge_later')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('return_requests');
    }
};
