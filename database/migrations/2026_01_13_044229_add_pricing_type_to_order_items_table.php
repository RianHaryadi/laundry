<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            // ✅ TAMBAHKAN kolom pricing_type setelah service_id
            $table->enum('pricing_type', ['kg', 'unit'])
                  ->after('service_id')
                  ->default('kg');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('pricing_type');
        });
    }
};