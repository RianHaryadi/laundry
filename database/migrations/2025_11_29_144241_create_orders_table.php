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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->unsignedBigInteger('customer_id')->nullable()->change();
            $table->foreignId('outlet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('courier_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Service Speed (Kecepatan Layanan)
            $table->enum('service_speed', [
                'regular',
                'express', 
                'same_day'
            ])->default('regular')->comment('Service speed/priority');
            
            // Delivery Method
            $table->enum('delivery_method', [
                'walk_in',
                'pickup',
                'delivery',
                'pickup_delivery'
            ])->default('walk_in')->comment('How customer receives service');
            
            // Order Status
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'ready',
                'picked_up',
                'in_delivery',
                'completed',
                'cancelled'
            ])->default('pending');
            
            // Payment Status
            $table->enum('payment_status', [
                'pending',
                'paid',
                'partial',
                'failed',
                'refunded'
            ])->default('pending');
            
            // Pricing & Weight
            $table->decimal('total_weight', 8, 2)->nullable()->comment('Weight in kg');
            $table->decimal('total_price', 12, 2)->comment('Total price in Rupiah');
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('pickup_delivery_fee', 12, 2)->default(0);
            $table->integer('final_price')->default(0)->change()->comment('Final price after discount and fees');
            $table->string('discount_type')->nullable();

            // Base price (NO AFTER HERE)
            $table->integer('base_price')->nullable();
            
            // Scheduling
            $table->dateTime('pickup_time')->nullable();
            $table->dateTime('delivery_time')->nullable();
            
            // Additional Info
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
