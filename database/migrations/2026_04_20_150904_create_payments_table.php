<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('service_booking_id')->constrained('service_bookings')->cascadeOnDelete();

            $table->foreignId('coupons_id')->nullable()->constrained('coupons')->nullOnDelete();

            $table->string('transaction_id')->nullable()->unique();
            $table->decimal('original_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);

            $table->enum('method', [
                'bank_transfer',
                'card',
                'cash',
                'bakong',
                'khqr',
            ]);

            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'refunded',
                'cancelled'
            ])->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};