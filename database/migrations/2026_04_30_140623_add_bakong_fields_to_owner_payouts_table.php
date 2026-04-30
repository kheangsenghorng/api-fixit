<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('owner_payouts', function (Blueprint $table) {
            $table->string('transaction_reference')->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('transaction_reference');
        });
    }

    public function down(): void
    {
        Schema::table('owner_payouts', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_reference',
                'paid_at',
            ]);
        });
    }
};