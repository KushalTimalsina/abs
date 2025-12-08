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
        Schema::create('widget_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->json('theme')->nullable(); // colors, fonts, etc.
            $table->text('custom_css')->nullable();
            $table->text('custom_js')->nullable();
            $table->string('primary_color')->default('#3B82F6');
            $table->string('secondary_color')->default('#10B981');
            $table->boolean('show_logo')->default(true);
            $table->boolean('show_organization_name')->default(true);
            $table->text('welcome_message')->nullable();
            $table->text('embed_code')->nullable(); // generated embed code
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('widget_settings');
    }
};
