<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Cek dan tambah kolom hanya jika belum ada
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('total_price');
            }
            
            if (!Schema::hasColumn('orders', 'final_price')) {
                $table->decimal('final_price', 10, 2)->default(0)->after('discount_amount');
            }
            
            if (!Schema::hasColumn('orders', 'discount_type')) {
                $table->string('discount_type')->nullable()->after('final_price');
            }
            
            if (!Schema::hasColumn('orders', 'pickup_delivery_fee')) {
                $table->decimal('pickup_delivery_fee', 10, 2)->default(0)->after('discount_type');
            }
        });

        // // Update existing orders: set final_price = total_price untuk data lama
        // \DB::statement('UPDATE orders SET final_price = total_price WHERE final_price = 0 OR final_price IS NULL');
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $columns = ['discount_amount', 'final_price', 'discount_type', 'pickup_delivery_fee'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};