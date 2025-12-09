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
        // Update existing 'blocked' status to 'unavailable'
        DB::table('slots')->where('status', 'blocked')->update(['status' => 'unavailable']);
        
        // Modify the enum to include new values
        DB::statement("ALTER TABLE slots MODIFY COLUMN status ENUM('available', 'booked', 'rescheduled', 'unavailable') NOT NULL DEFAULT 'available'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old enum
        DB::statement("ALTER TABLE slots MODIFY COLUMN status ENUM('available', 'booked', 'blocked') NOT NULL DEFAULT 'available'");
        
        // Update 'unavailable' back to 'blocked'
        DB::table('slots')->where('status', 'unavailable')->update(['status' => 'blocked']);
    }
};
