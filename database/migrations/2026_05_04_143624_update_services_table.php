<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Remove columns if they already exist but are not in your diagram
            if (Schema::hasColumn('services', 'base_price')) {
                $table->dropColumn('base_price');
            }

            if (Schema::hasColumn('services', 'duration')) {
                $table->dropColumn('duration');
            }

            // Add images column if it does not exist
            if (!Schema::hasColumn('services', 'images')) {
                $table->json('images')->nullable();
            }

            // Add status column if it does not exist
            if (!Schema::hasColumn('services', 'status')) {
                $table->enum('status', ['draft', 'active', 'paused'])
                    ->default('draft');
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            if (!Schema::hasColumn('services', 'base_price')) {
                $table->decimal('base_price', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('services', 'duration')) {
                $table->integer('duration')->nullable();
            }

            if (Schema::hasColumn('services', 'images')) {
                $table->dropColumn('images');
            }

            if (Schema::hasColumn('services', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};