<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trackings', function (Blueprint $table) {
            // Tambah kolom baru
            $table->enum('type', ['pickup', 'delivery'])->default('pickup')->after('courier_id');
            $table->dateTime('scheduled_time')->nullable()->after('status');
            $table->dateTime('actual_time')->nullable()->after('scheduled_time');
            $table->text('pickup_address')->nullable()->after('actual_time');
            $table->text('delivery_address')->nullable()->after('pickup_address');
            $table->text('notes')->nullable()->after('longitude');
            $table->text('photo')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('trackings', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'scheduled_time',
                'actual_time',
                'pickup_address',
                'delivery_address',
                'notes',
                'photo'
            ]);
        });
    }
};