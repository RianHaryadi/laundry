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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->onDelete('cascade');

            $table->foreignId('service_id')
                ->constrained('services')
                ->onDelete('cascade');

            // Per item
            $table->integer('quantity')->nullable();

            // Per kg
            $table->decimal('weight', 8, 2)->nullable();

            // Harga final (qty/weight × harga service)
            $table->integer('price')->default(0);

            $table->decimal('price_per_kg', 15, 2)->nullable()->default(0.00)->after('pricing_type');
            $table->decimal('price_per_unit', 15, 2)->nullable()->default(0.00)->after('price_per_kg');

            $table->string('storage_location', 255)->nullable()->after('subtotal');
            $table->json('photo_proof')->nullable()->change();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
