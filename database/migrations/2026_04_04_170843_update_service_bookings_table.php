<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            // remove old columns if you do not need them
            if (Schema::hasColumn('service_bookings', 'owner_id')) {
                $table->dropConstrainedForeignId('owner_id');
            }

            if (Schema::hasColumn('service_bookings', 'status')) {
                $table->dropColumn('status');
            }

            // add new columns
            $table->string('street_number')->nullable()->after('service_id');
            $table->string('house_number')->nullable()->after('street_number');
            $table->string('booking_hours')->nullable()->after('booking_date');
            $table->text('address')->nullable()->after('booking_hours');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('map_url')->nullable()->after('longitude');
            $table->text('notes')->nullable()->after('quantity');

            $table->enum('booking_status', [
                'pending',
                'confirmed',
                'in_progress',
                'awaiting_customer_confirmation',
                'completed',
                'cancelled',
                'disputed'
            ])->default('pending')->after('notes');

            $table->enum('customer_status', [
                'pending',
                'completed',
                'disputed'
            ])->default('pending')->after('booking_status');

            $table->timestamp('customer_completed_at')->nullable()->after('customer_status');
            $table->timestamp('auto_complete_at')->nullable()->after('customer_completed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'street_number',
                'house_number',
                'booking_hours',
                'address',
                'latitude',
                'longitude',
                'map_url',
                'notes',
                'booking_status',
                'customer_status',
                'customer_completed_at',
                'auto_complete_at',
            ]);

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('owners')
                ->nullOnDelete();

            $table->enum('status', [
                'pending',
                'confirmed',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('pending');
        });
    }
};