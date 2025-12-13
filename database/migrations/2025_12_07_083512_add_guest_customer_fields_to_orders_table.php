<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Add customer_type field after customer_id
            $table->enum('customer_type', ['member', 'guest'])
                  ->default('member')
                  ->after('customer_id');
            
            // Add guest information fields
            $table->string('guest_name')->nullable()->after('customer_type');
            $table->string('guest_phone', 20)->nullable()->after('guest_name');
            $table->text('guest_address')->nullable()->after('guest_phone');
        });

        // Update existing records: set customer_type based on customer_id
        DB::statement("
            UPDATE orders 
            SET customer_type = CASE 
                WHEN customer_id IS NOT NULL THEN 'member'
                ELSE 'guest'
            END
            WHERE customer_type IS NULL OR customer_type = 'member'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_type',
                'guest_name',
                'guest_phone',
                'guest_address',
            ]);
        });
    }
};