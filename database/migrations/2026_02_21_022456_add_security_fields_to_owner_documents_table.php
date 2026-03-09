<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('owner_documents', function (Blueprint $table) {
            $table->string('disk')->default('private')->after('file_path');
            $table->string('original_name')->nullable()->after('disk');
            $table->string('mime_type')->nullable()->after('original_name');
            $table->unsignedBigInteger('size')->nullable()->after('mime_type');

            $table->string('otp_hash')->nullable()->after('status');
            $table->timestamp('otp_expires_at')->nullable()->after('otp_hash');
            $table->unsignedTinyInteger('otp_attempts')->default(0)->after('otp_expires_at');
            $table->timestamp('otp_verified_at')->nullable()->after('otp_attempts');
            $table->timestamp('otp_last_sent_at')->nullable()->after('otp_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('owner_documents', function (Blueprint $table) {
            $table->dropColumn([
                'disk','original_name','mime_type','size',
                'otp_hash','otp_expires_at','otp_attempts','otp_verified_at','otp_last_sent_at'
            ]);
        });
    }
};