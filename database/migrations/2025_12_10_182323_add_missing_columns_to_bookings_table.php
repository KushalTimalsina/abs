<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to add columns if they don't exist
        $columns = Schema::getColumnListing('bookings');
        
        if (!in_array('customer_name', $columns)) {
            DB::statement('ALTER TABLE bookings ADD COLUMN customer_name VARCHAR(255) AFTER staff_id');
        }
        
        if (!in_array('customer_email', $columns)) {
            DB::statement('ALTER TABLE bookings ADD COLUMN customer_email VARCHAR(255) AFTER customer_name');
        }
        
        if (!in_array('customer_phone', $columns)) {
            DB::statement('ALTER TABLE bookings ADD COLUMN customer_phone VARCHAR(255) AFTER customer_email');
        }
        
        if (!in_array('notes', $columns)) {
            DB::statement('ALTER TABLE bookings ADD COLUMN notes TEXT NULL AFTER payment_status');
        }
        
        if (!in_array('customer_notes', $columns)) {
            DB::statement('ALTER TABLE bookings ADD COLUMN customer_notes TEXT NULL AFTER notes');
        }
        
        // Change time columns to datetime
        DB::statement('ALTER TABLE bookings MODIFY COLUMN start_time DATETIME');
        DB::statement('ALTER TABLE bookings MODIFY COLUMN end_time DATETIME');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'customer_email', 'customer_phone', 'notes', 'customer_notes']);
        });
        
        // Revert back to time
        DB::statement('ALTER TABLE bookings MODIFY COLUMN start_time TIME');
        DB::statement('ALTER TABLE bookings MODIFY COLUMN end_time TIME');
    }
};
