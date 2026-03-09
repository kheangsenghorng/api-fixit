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
            $table->decimal('lat', 10, 7)->nullable()->after('address');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->string('map_url')->nullable()->after('lng');
        });
    }
    
    public function down(): void
    {
        Schema::table('owners', function (Blueprint $table) {
            $table->dropColumn(['lat','lng','map_url']);
        });
    }
    
};
