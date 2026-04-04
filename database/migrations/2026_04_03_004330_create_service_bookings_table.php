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
        Schema::create('service_bookings', function (Blueprint $table) {
            $table->id(); // UniqueID
        
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
        
            $table->foreignId('service_id')
                ->constrained('services')
                ->onDelete('cascade');
        
            $table->foreignId('owner_id')->constrained('owners')->onDelete('cascade');

            $table->date('booking_date');
            $table->integer('quantity')->default(1);
        
            $table->enum('status', [
                'pending',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');
        
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_bookings');
    }
};
