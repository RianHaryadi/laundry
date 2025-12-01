<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('machines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('outlet_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('serial_number')->nullable()->unique();
            
            // Type untuk mesin laundry
            $table->enum('type', [
                'washer', 
                'dryer', 
                'ironer', 
                'boiler', 
                'conveyor', 
                'packing', 
                'other'
            ])->default('other');
            
            // Status mesin
            $table->enum('status', [
                'operational', 
                'maintenance', 
                'broken', 
                'retired'
            ])->default('operational');
            
            // Informasi mesin
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            
            // Purchase & Warranty
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();
            $table->date('warranty_until')->nullable();
            $table->string('supplier')->nullable();
            
            // Maintenance
            $table->date('last_maintenance')->nullable();
            $table->integer('maintenance_interval')->nullable()->comment('in days');
            
            // Additional info
            $table->text('specifications')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('machines');
    }
};