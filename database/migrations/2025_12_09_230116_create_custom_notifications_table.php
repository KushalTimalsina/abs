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
        Schema::create('custom_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('sender_type'); // superadmin, organization
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->string('recipient_type'); // organization, user, subscription_plan
            $table->json('recipient_ids')->nullable(); // For bulk/individual
            $table->string('title');
            $table->text('message');
            $table->string('type')->default('info'); // info, warning, success, error
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->unsignedInteger('read_count')->default(0);
            $table->timestamps();
            
            $table->index(['sender_type', 'sender_id']);
            $table->index('sent_at');
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_notifications');
    }
};
