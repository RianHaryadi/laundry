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
            // Add customer_id first (if you need it)
            $table->foreignId('customer_id')->nullable()->after('id');
            
            // Add customer_type field after customer_id
            $table->enum('customer_type', ['member', 'guest'])
                  ->default('member')
                  ->after('customer_id');
            
            // Add guest information fields
            $table->string('guest_name')->nullable()->after('customer_type');
            $table->string('guest_phone', 20)->nullable()->after('guest_name');
            $table->text('guest_address')->nullable()->after('guest_phone');
        });

        // Update existing records: set all existing orders as 'guest' since they don't have customer_id
        DB::table('orders')->update(['customer_type' => 'guest']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'customer_id',
                'customer_type',
                'guest_name',
                'guest_phone',
                'guest_address',
            ]);
        });
    }
};