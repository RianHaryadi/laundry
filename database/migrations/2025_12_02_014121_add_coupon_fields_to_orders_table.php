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
        Schema::table('orders', function (Blueprint $table) {
            
            // 1. Cek & Tambah coupon_id
            if (!Schema::hasColumn('orders', 'coupon_id')) {
                $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            }

            // 2. Cek & Tambah coupon_code
            if (!Schema::hasColumn('orders', 'coupon_code')) {
                $table->string('coupon_code')->nullable();
            }

            // 3. Cek & Tambah discount_amount
            if (!Schema::hasColumn('orders', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Hapus foreign key dulu sebelum kolomnya (jika ada)
            if (Schema::hasColumn('orders', 'coupon_id')) {
                // Kita coba drop foreign key, kadang nama otomatisnya berbeda
                // jadi kita wrap try-catch atau biarkan error jika rollback
                try {
                    $table->dropForeign(['coupon_id']);
                } catch (\Exception $e) {
                    // abaikan jika foreign key tidak ketemu
                }
                $table->dropColumn('coupon_id');
            }

            if (Schema::hasColumn('orders', 'coupon_code')) {
                $table->dropColumn('coupon_code');
            }

            if (Schema::hasColumn('orders', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};