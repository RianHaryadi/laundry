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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            // Personal Information
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('email')->unique()->nullable();
            $table->text('address')->nullable();
            
            // Membership Information
            $table->boolean('is_member')->default(false);
            $table->date('member_since')->nullable();
            $table->integer('available_coupons')->default(0); // Jumlah kupon yang dimiliki
            
            // Additional Information
            $table->date('birthday')->nullable();
            $table->foreignId('preferred_outlet_id')->nullable()->constrained('outlets')->nullOnDelete();
            
            // Notification Preferences
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(true);
            
            // Internal Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('is_member');
            $table->index('member_since');
            $table->index('available_coupons');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};