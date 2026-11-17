<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Homepage Sections for Dynamic Homepage Builder
        Schema::create('homepage_sections', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // announcement, hero, category_grid, new_arrivals, editorial, best_sellers, shop_the_look, brand_story, testimonials, instagram_gallery, newsletter, footer, custom
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->json('content')->nullable(); // flexible schema per section type
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Testimonials
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->integer('rating')->default(5);
            $table->text('review');
            $table->string('product_purchased')->nullable();
            $table->string('customer_image')->nullable();
            $table->boolean('is_featured')->default(true);
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Newsletter Subscribers
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscribed_at')->useCurrent();
            $table->timestamps();
        });

        // Instagram / Social Gallery Items
        Schema::create('social_gallery_items', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('image');
            $table->string('link_url')->nullable();
            $table->string('instagram_handle')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Static & Dynamic CMS Pages
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('content')->nullable();
            $table->string('template')->default('default'); // default, about, contact, terms, privacy
            $table->boolean('is_active')->default(true);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();
        });

        // Navigation Menus
        Schema::create('navigation_menus', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('location')->unique(); // header, footer_1, footer_2, mobile
            $table->json('items')->nullable();
            $table->timestamps();
        });

        // Site Settings
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->longText('value')->nullable();
            $table->string('group')->default('general'); // general, store, shipping, payment, seo, custom_code
            $table->string('type')->default('text'); // text, textarea, boolean, json, image
            $table->timestamps();
        });

        // Contact Messages
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // Activity Logs
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->text('description')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('site_settings');
        Schema::dropIfExists('navigation_menus');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('social_gallery_items');
        Schema::dropIfExists('newsletter_subscribers');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('homepage_sections');
    }
};
