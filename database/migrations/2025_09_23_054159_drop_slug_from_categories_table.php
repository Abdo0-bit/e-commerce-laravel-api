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
        Schema::table('categories', function (Blueprint $table) {
            // Drop the composite index first (includes slug)
            $table->dropIndex(['is_active', 'slug']);
            // Drop the unique constraint and column
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Re-add the slug column
            $table->string('slug')->unique();
            // Re-add the composite index
            $table->index(['is_active', 'slug']);
        });
    }
};
