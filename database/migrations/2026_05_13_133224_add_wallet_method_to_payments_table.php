<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE payments 
            MODIFY method ENUM(
                'bank_transfer',
                'card',
                'cash',
                'bakong',
                'khqr',
                'wallet'
            ) NOT NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE payments 
            MODIFY method ENUM(
                'bank_transfer',
                'card',
                'cash',
                'bakong',
                'khqr'
            ) NOT NULL
        ");
    }
};