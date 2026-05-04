<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_included_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_id')
                ->constrained('service_packages')
                ->cascadeOnDelete();

            $table->foreignId('included_item_id')
                ->constrained('included_items')
                ->cascadeOnDelete();

            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['package_id', 'included_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_included_items');
    }
};