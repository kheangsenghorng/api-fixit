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
        Schema::create('payment_splits', function (Blueprint $table) {
            $table->id();
        
            $table->foreignId('payment_id')
                ->constrained('payments')
                ->cascadeOnDelete();
        
            $table->foreignId('owner_id')
                ->constrained('owners')
                ->cascadeOnDelete();
        
            $table->decimal('service_amount', 10, 2);
            $table->decimal('admin_commission', 10, 2);
            $table->decimal('owner_payout', 10, 2);
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_splits');
    }
};
