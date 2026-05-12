<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->enum('method', ['wallet', 'aba', 'bakong', 'cash'])
                ->nullable()
                ->after('type');

            $table->string('transaction_ref')
                ->nullable()
                ->after('method');

            $table->string('external_transaction_id')
                ->nullable()
                ->after('transaction_ref');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'method',
                'transaction_ref',
                'external_transaction_id',
            ]);
        });
    }
};