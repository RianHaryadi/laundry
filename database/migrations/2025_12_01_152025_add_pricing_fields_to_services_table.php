<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->enum('pricing_type', ['kg', 'unit', 'both'])->default('kg')->after('price_per_kg');
            $table->decimal('price_per_unit', 10, 2)->nullable()->after('pricing_type');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['pricing_type', 'price_per_unit']);
        });
    }
};