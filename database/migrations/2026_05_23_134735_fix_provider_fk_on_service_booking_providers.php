<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    
    {  Schema::table('service_booking_providers', function (Blueprint $table) {
        $table->foreign('provider_id')
            ->references('providerId')
            ->on('providers')
            ->onDelete('cascade');
    });
    }

    public function down(): void
    {
        Schema::table('service_booking_providers', function (Blueprint $table) {

            $table->dropForeign(['provider_id']);

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('cascade');
        });
    }
};