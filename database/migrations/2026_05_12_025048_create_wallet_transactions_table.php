<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id('wallet_transaction_id');

            $table->unsignedBigInteger('wallet_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payment_id')->nullable();
            $table->unsignedBigInteger('service_booking_id')->nullable();

            $table->enum('type', ['credit', 'debit']);

            $table->decimal('amount', 10, 2);
            $table->decimal('balance_before', 10, 2);
            $table->decimal('balance_after', 10, 2);

            $table->text('description')->nullable();

            $table->timestamps();

            $table->foreign('wallet_id')
                ->references('wallet_id')
                ->on('wallets')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->nullOnDelete();

            $table->foreign('service_booking_id')
                ->references('id')
                ->on('service_bookings')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_transactions');
    }
};