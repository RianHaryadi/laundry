<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            
            // Basic Information
            $table->string('code')->unique();
            $table->text('description')->nullable();
            
            // Discount Settings
            $table->enum('discount_type', ['percentage', 'fixed', 'free_shipping'])->default('percentage');
            $table->decimal('discount_value', 10, 2)->default(0);
            $table->decimal('max_discount', 10, 2)->nullable();
            
            // Usage Restrictions
            $table->decimal('min_order', 10, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('max_uses_per_user')->default(1);
            $table->integer('used_count')->default(0);
            
            // Validity Period
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            
            // Status & Settings
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(true);
            $table->boolean('first_order_only')->default(false);
            $table->boolean('exclude_discounted_items')->default(false);
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('code');
            $table->index('is_active');
            $table->index('expires_at');
            $table->index('discount_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};