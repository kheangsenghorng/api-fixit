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
        Schema::table('owners', function (Blueprint $table) {
            $table->string('telegram_connect_code')->nullable()->unique();
            $table->string('telegram_group_id')->nullable();
            $table->string('telegram_group_name')->nullable();
            $table->boolean('telegram_connected')->default(false);
        });
    }
    
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn([
                'telegram_connect_code',
                'telegram_group_id',
                'telegram_group_name',
                'telegram_connected',
            ]);
        });
    }
};
