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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
        
            // Unique coupon code or UUID
            $table->string('unique_id')->unique();
        
            // Nullable owner reference
            // NULL means global/admin coupon
            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        
            // Discount percentage
            $table->enum('discount_type', ['fixed', 'percent'])->default('percent');
            $table->decimal('discount_value', 10, 2);
        
            // Expiration date
            $table->date('expires_at')->nullable();
        
            // Total max uses for this coupon
            $table->unsignedInteger('max_uses')->default(1);
        
            // Max uses per user
            $table->unsignedInteger('max_uses_per_user')->default(1);
        
            // Coupon status
            $table->enum('status', ['active', 'expired', 'disabled'])
                ->default('active');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
