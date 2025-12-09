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
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('invoice_type', ['booking', 'subscription'])->default('booking')->after('invoice_number');
            $table->foreignId('subscription_payment_id')->nullable()->after('payment_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->nullable()->after('subscription_payment_id')->constrained()->onDelete('cascade');
            $table->string('payment_method', 50)->nullable()->after('status');
            $table->string('paid_by')->nullable()->after('payment_method');
            $table->timestamp('paid_at')->nullable()->after('paid_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['subscription_payment_id']);
            $table->dropForeign(['organization_id']);
            $table->dropColumn([
                'invoice_type',
                'subscription_payment_id',
                'organization_id',
                'payment_method',
                'paid_by',
                'paid_at'
            ]);
        });
    }
};
