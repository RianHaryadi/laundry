<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('coupon_used')->default(false)->after('status');
            $table->integer('coupon_count')->default(0)->after('coupon_used');
            $table->text('notes')->nullable()->after('paid_at');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['coupon_used', 'coupon_count', 'notes']);
        });
    }
};