<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Cek apakah kolom sudah ada sebelum menambahkan
            if (!Schema::hasColumn('services', 'duration_hours')) {
                $table->integer('duration_hours')->default(24)->after('price_per_kg');
            }
            if (!Schema::hasColumn('services', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('duration_hours');
            }
        });

        // Tidak perlu update price_per_kg dari base_price karena base_price tidak ada
        // price_per_kg sudah langsung ada di tabel services
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (Schema::hasColumn('services', 'duration_hours')) {
                $table->dropColumn('duration_hours');
            }
            if (Schema::hasColumn('services', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};