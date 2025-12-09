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
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->enum('gateway_name', ['esewa', 'khalti', 'stripe', 'bank_transfer', 'cash']);
            $table->text('credentials')->nullable(); // encrypted JSON for API keys (only for online gateways)
            $table->json('settings')->nullable(); // gateway-specific settings (bank details, instructions, etc.)
            $table->boolean('is_active')->default(false);
            $table->boolean('is_test_mode')->default(false);
            $table->timestamps();
            
            $table->unique(['organization_id', 'gateway_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_gateways');
    }
};
