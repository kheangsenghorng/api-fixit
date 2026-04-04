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
        Schema::create('service_booking_providers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('service_booking_id')
                ->constrained('service_bookings')
                ->onDelete('cascade');

            $table->foreignId('provider_id')
                ->constrained('providers')
                ->onDelete('cascade');

            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('owners')
                ->nullOnDelete();

            $table->enum('role', ['main', 'helper'])->default('helper');

            $table->enum('status', [
                'assigned',
                'accepted',
                'on_the_way',
                'arrived',
                'working',
                'completed',
                'declined'
            ])->default('assigned');

            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->unique(['service_booking_id', 'provider_id'], 'sbp_booking_provider_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_booking_providers');
    }
};