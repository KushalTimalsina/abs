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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', ['admin', 'team_member', 'frontdesk', 'customer'])->default('customer')->after('email');
            $table->string('phone')->nullable()->after('email_verified_at');
            $table->string('avatar')->nullable()->after('phone');
            $table->json('settings')->nullable()->after('avatar'); // notification preferences, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['user_type', 'phone', 'avatar', 'settings']);
        });
    }
};
