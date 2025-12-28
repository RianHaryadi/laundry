<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->date('expense_date');
            $table->foreignId('expense_category_id')->constrained()->onDelete('cascade');
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['Cash', 'Transfer', 'E-wallet'])->default('Cash');
            $table->string('receipt_file')->nullable();
            $table->foreignId('outlet_id')->nullable()->constrained()->onDelete('cascade');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};