// database/migrations/xxxx_xx_xx_add_membership_level_to_customers_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('membership_level', ['regular', 'silver', 'gold', 'vip'])
                  ->default('regular')
                  ->after('phone'); // sesuaikan posisinya
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('membership_level');
        });
    }
};