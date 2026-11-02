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
        Schema::table('products', function (Blueprint $table) {
            $table->index(['is_visible', 'category_id'], 'idx_products_visible_category');
            $table->index('is_featured', 'idx_products_featured');
            $table->index('is_best_seller', 'idx_products_best_seller');
            $table->index('is_trending', 'idx_products_trending');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('order_status', 'idx_orders_status');
            $table->index('payment_status', 'idx_orders_payment_status');
            $table->index('created_at', 'idx_orders_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_visible_category');
            $table->dropIndex('idx_products_featured');
            $table->dropIndex('idx_products_best_seller');
            $table->dropIndex('idx_products_trending');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('idx_orders_status');
            $table->dropIndex('idx_orders_payment_status');
            $table->dropIndex('idx_orders_created_at');
        });
    }
};
