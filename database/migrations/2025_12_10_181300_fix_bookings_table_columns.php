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
        Schema::table('bookings', function (Blueprint $table) {
            // Change time columns to datetime
            $table->dateTime('start_time')->change();
            $table->dateTime('end_time')->change();
            
            // Add customer_name, customer_email, customer_phone if they don't exist
            if (!Schema::hasColumn('bookings', 'customer_name')) {
                $table->string('customer_name')->after('staff_id');
            }
            if (!Schema::hasColumn('bookings', 'customer_email')) {
                $table->string('customer_email')->after('customer_name');
            }
            if (!Schema::hasColumn('bookings', 'customer_phone')) {
                $table->string('customer_phone')->after('customer_email');
            }
            
            // Ensure notes and customer_notes exist
            if (!Schema::hasColumn('bookings', 'notes')) {
                $table->text('notes')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('bookings', 'customer_notes')) {
                $table->text('customer_notes')->nullable()->after('notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert time columns back to time
            $table->time('start_time')->change();
            $table->time('end_time')->change();
        });
    }
};
