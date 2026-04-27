<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            if (!Schema::hasColumn('providers', 'rating')) {
                $table->decimal('rating', 2, 1)->default(0.0)->after('status');
            }

            if (!Schema::hasColumn('providers', 'total_jobs')) {
                $table->unsignedInteger('total_jobs')->default(0)->after('rating');
            }

            if (!Schema::hasColumn('providers', 'customer_comment')) {
                $table->text('customer_comment')->nullable()->after('total_jobs');
            }
        });
    }

    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            if (Schema::hasColumn('providers', 'customer_comment')) {
                $table->dropColumn('customer_comment');
            }

            if (Schema::hasColumn('providers', 'total_jobs')) {
                $table->dropColumn('total_jobs');
            }

            if (Schema::hasColumn('providers', 'rating')) {
                $table->dropColumn('rating');
            }
        });
    }
};