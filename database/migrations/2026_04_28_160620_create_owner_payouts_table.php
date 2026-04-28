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
        Schema::create('owner_payouts', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('owner_id')
                ->constrained('owners')
                ->cascadeOnDelete();
        
                $table->foreignId('split_id')
                ->constrained('payment_splits')
                ->cascadeOnDelete();
        
            $table->decimal('amount', 10, 2);
        
            $table->enum('method', [
                'bank_transfer',
                'card',
                'cash',
                'bakong',
                'khqr',
            ])->default('bank_transfer');
        
            $table->enum('status', [
                'pending',
                'paid',
            ])->default('pending');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('owner_payouts');
    }
};
