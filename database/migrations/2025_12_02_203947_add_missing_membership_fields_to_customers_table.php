<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Kolom yang diminta sistem tapi belum ada
            if (!Schema::hasColumn('customers', 'is_member')) {
                $table->boolean('is_member')->default(false)->after('address');
            }

            if (!Schema::hasColumn('customers', 'available_coupons')) {
                $table->integer('available_coupons')->default(0)->after('is_member');
            }
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'is_member')) {
                $table->dropColumn('is_member');
            }

            if (Schema::hasColumn('customers', 'available_coupons')) {
                $table->dropColumn('available_coupons');
            }
        });
    }
};
