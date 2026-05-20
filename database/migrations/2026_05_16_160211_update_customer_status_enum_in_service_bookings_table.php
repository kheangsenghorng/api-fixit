<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE service_bookings 
            MODIFY customer_status ENUM('pending', 'confirmed', 'completed', 'cancelled', 'refunded') 
            DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE service_bookings 
            MODIFY customer_status ENUM('pending', 'confirmed', 'completed', 'cancelled') 
            DEFAULT 'pending'
        ");
    }
};