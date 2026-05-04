<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id(); // UniqueID

            $table->foreignId('service_id')
                ->constrained('services')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->decimal('min_area_m2', 8, 2)->nullable();
            $table->decimal('max_area_m2', 8, 2)->nullable();

            $table->integer('floor_number')->nullable();
            $table->integer('bedrooms')->nullable();

            $table->decimal('duration_hours', 5, 2)->nullable();
            $table->integer('workers_count')->nullable();

            $table->decimal('price', 10, 2);

            $table->enum('billing_type', [
                'one_time',
                'weekly',
                'monthly',
            ])->default('one_time');

            $table->enum('status', [
                'draft',
                'active',
                'paused',
            ])->default('draft');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_packages');
    }
};