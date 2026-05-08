<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            // Remove old owner_id
            if (Schema::hasColumn('service_bookings', 'owner_id')) {
                $table->dropConstrainedForeignId('owner_id');
            }

            // Remove old status
            if (Schema::hasColumn('service_bookings', 'status')) {
                $table->dropColumn('status');
            }

            // Drop address columns from service_bookings
            foreach ([
                'street_number',
                'house_number',
                'address',
                'latitude',
                'longitude',
                'map_url',
            ] as $column) {
                if (Schema::hasColumn('service_bookings', $column)) {
                    $table->dropColumn($column);
                }
            }

            // Add address_id instead
            if (!Schema::hasColumn('service_bookings', 'address_id')) {
                $table->foreignId('address_id')
                    ->nullable()
                    ->after('package_id')
                    ->constrained('user_addresses')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('service_bookings', 'booking_hours')) {
                $table->string('booking_hours')
                    ->nullable()
                    ->after('booking_date');
            }

            if (!Schema::hasColumn('service_bookings', 'notes')) {
                $table->text('notes')
                    ->nullable()
                    ->after('quantity');
            }

            if (!Schema::hasColumn('service_bookings', 'booking_status')) {
                $table->enum('booking_status', [
                    'pending',
                    'confirmed',
                    'in_progress',
                    'awaiting_customer_confirmation',
                    'completed',
                    'cancelled',
                    'disputed'
                ])->default('pending')->after('notes');
            }

            if (!Schema::hasColumn('service_bookings', 'customer_status')) {
                $table->enum('customer_status', [
                    'pending',
                    'completed',
                    'disputed'
                ])->default('pending')->after('booking_status');
            }

            if (!Schema::hasColumn('service_bookings', 'provider_completed_at')) {
                $table->timestamp('provider_completed_at')
                    ->nullable()
                    ->after('customer_status');
            }

            if (!Schema::hasColumn('service_bookings', 'customer_completed_at')) {
                $table->timestamp('customer_completed_at')
                    ->nullable()
                    ->after('provider_completed_at');
            }

            if (!Schema::hasColumn('service_bookings', 'auto_complete_at')) {
                $table->timestamp('auto_complete_at')
                    ->nullable()
                    ->after('customer_completed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('service_bookings', 'address_id')) {
                $table->dropConstrainedForeignId('address_id');
            }

            if (Schema::hasColumn('service_bookings', 'booking_hours')) {
                $table->dropColumn('booking_hours');
            }

            if (Schema::hasColumn('service_bookings', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('service_bookings', 'booking_status')) {
                $table->dropColumn('booking_status');
            }

            if (Schema::hasColumn('service_bookings', 'customer_status')) {
                $table->dropColumn('customer_status');
            }

            if (Schema::hasColumn('service_bookings', 'provider_completed_at')) {
                $table->dropColumn('provider_completed_at');
            }

            if (Schema::hasColumn('service_bookings', 'customer_completed_at')) {
                $table->dropColumn('customer_completed_at');
            }

            if (Schema::hasColumn('service_bookings', 'auto_complete_at')) {
                $table->dropColumn('auto_complete_at');
            }

            // Add old columns back
            $table->string('street_number')->nullable()->after('service_id');
            $table->string('house_number')->nullable()->after('street_number');
            $table->text('address')->nullable()->after('house_number');
            $table->decimal('latitude', 10, 7)->nullable()->after('address');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->text('map_url')->nullable()->after('longitude');

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