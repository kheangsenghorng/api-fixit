<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_task_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('package_id')
                ->constrained('service_packages')
                ->cascadeOnDelete();

            $table->foreignId('task_group_id')
                ->constrained('task_groups')
                ->cascadeOnDelete();

            $table->integer('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['package_id', 'task_group_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_task_groups');
    }
};