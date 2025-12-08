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
        Schema::create('payment_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('gateway', ['esewa', 'khalti', 'bank_transfer', 'stripe'])->unique();
            $table->boolean('is_active')->default(false);
            $table->string('qr_code_path')->nullable(); // For eSewa, Khalti
            $table->json('account_details')->nullable(); // Merchant IDs, bank details, etc.
            $table->text('instructions')->nullable(); // Payment instructions
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_settings');
    }
};
