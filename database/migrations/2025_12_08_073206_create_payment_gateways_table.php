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
            $table->enum('gateway_type', ['esewa', 'khalti', 'stripe', 'cash'])->default('cash');
            $table->text('credentials')->nullable(); // encrypted JSON for API keys
            $table->json('settings')->nullable(); // gateway-specific settings
            $table->boolean('is_active')->default(false);
            $table->boolean('is_test_mode')->default(true);
            $table->timestamps();
            
            $table->unique(['organization_id', 'gateway_type']);
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
