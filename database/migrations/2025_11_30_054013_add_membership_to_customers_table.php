<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('membership_type')->default('regular')->after('address'); 
            $table->timestamp('membership_started_at')->nullable()->after('membership_type');
            $table->timestamp('membership_expires_at')->nullable()->after('membership_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['membership_type', 'membership_started_at', 'membership_expires_at']);
        });
    }
};