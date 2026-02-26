<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owner_documents', function (Blueprint $table) {
            $table->id();

            // FK owner
            $table->foreignId('owner_id')
                  ->constrained('owners')
                  ->cascadeOnDelete();

            // document info
            $table->string('document_type'); // passport, id_card, etc
            $table->string('country', 2);    // KH, US
            $table->string('file_path');

            $table->timestamp('uploaded_at')->useCurrent();

            // Status (from your diagram)
            $table->string('status')->default('pending');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('owner_documents');
    }
};