<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Editable store emails (order confirmation, shipped, ...). Missing rows are
        // filled in from App\Support\EmailTemplateDefaults the first time they are read.
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key', 60)->unique();
            $table->string('subject');
            $table->string('heading')->nullable();
            $table->text('body');
            $table->string('button_label', 80)->nullable();
            $table->string('button_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Every email the store tried to send, so the owner can see what went out.
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_key', 60)->nullable()->index();
            $table->string('to_email');
            $table->string('subject');
            $table->string('status', 20); // sent | failed
            $table->text('error')->nullable();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_test')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
    }
};
