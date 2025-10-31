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
            // Update payment_status enum to include Stripe statuses
            $table->enum('payment_status', ['unpaid', 'paid', 'failed', 'requires_action', 'processing'])->default('unpaid')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Revert payment_status enum to original state
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->change();
        });
    }
};
