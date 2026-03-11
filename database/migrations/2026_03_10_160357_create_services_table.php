<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id(); // ServicesID

            $table->foreignId('owner_id')
                ->constrained('owners')
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->foreignId('type_id')
                ->constrained('types')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();

            $table->enum('status', ['draft', 'active', 'paused'])
                  ->default('draft');

            $table->decimal('base_price', 10, 2);
            $table->integer('duration'); // minutes or hours

            // images (max 4)
            $table->json('images')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};