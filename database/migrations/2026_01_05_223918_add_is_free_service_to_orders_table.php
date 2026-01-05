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
    $table->boolean('is_free_service')->default(false)->after('discount_type');
    
    // Remove unused columns
    if (Schema::hasColumn('orders', 'coupon_earned')) {
        $table->dropColumn('coupon_earned');
    }
    if (Schema::hasColumn('orders', 'reward_points')) {
        $table->dropColumn('reward_points');
    }
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
};
