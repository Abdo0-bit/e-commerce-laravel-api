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
        Schema::table('orders', function (Blueprint $table) {
            // Update payment_method enum to include stripe
            $table->enum('payment_method', ['cod', 'stripe'])->default('cod')->change();

            // Add Stripe-specific columns
            $table->string('stripe_payment_intent_id')->nullable()->after('payment_method');
            $table->string('stripe_client_secret')->nullable()->after('stripe_payment_intent_id');
            $table->json('stripe_payment_metadata')->nullable()->after('stripe_client_secret');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Remove Stripe-specific columns
            $table->dropColumn(['stripe_payment_intent_id', 'stripe_client_secret', 'stripe_payment_metadata']);

            // Revert payment_method enum to original state
            $table->enum('payment_method', ['cod'])->default('cod')->change();
        });
    }
};
