<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->boolean('is_member_reward')->default(false)->after('is_public');
            $table->foreignId('outlet_id')->nullable()->after('is_member_reward')
                ->constrained('outlets')->nullOnDelete();
            
            $table->index('is_member_reward');
        });
    }

    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropForeign(['outlet_id']);
            $table->dropColumn(['is_member_reward', 'outlet_id']);
        });
    }
};