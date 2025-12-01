<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('machine_id')->constrained()->onDelete('cascade');
            $table->foreignId('technician_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type')->default('preventive');
            $table->string('status')->default('scheduled');
            $table->string('priority')->default('medium');
            $table->text('description');
            $table->text('issues_found')->nullable();
            $table->text('actions_taken')->nullable();
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->decimal('cost', 15, 2)->nullable();
            $table->text('cost_breakdown')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->text('materials_used')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('status');
            $table->index('type');
            $table->index('priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
    }
};