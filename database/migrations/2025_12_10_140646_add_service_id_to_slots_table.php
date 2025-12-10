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
        Schema::table('slots', function (Blueprint $table) {
            $table->foreignId('service_id')->nullable()->after('organization_id')->constrained()->onDelete('cascade');
            $table->integer('max_bookings')->default(1)->after('status');
            $table->integer('current_bookings')->default(0)->after('max_bookings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slots', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id', 'max_bookings', 'current_bookings']);
        });
    }
};
