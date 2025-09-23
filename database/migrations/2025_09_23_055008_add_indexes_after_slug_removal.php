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
            // Add index for is_active column (replacing the old composite index with slug)
            $table->index('is_active');
            // Add index for name column for better search performance
            $table->index('name');
        });

        Schema::table('products', function (Blueprint $table) {
            // Add index for name column for better search performance  
            $table->index('name');
            // Note: products already has indexes for ['is_active', 'category_id'] and 'price'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
