<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-size stock on the product_size pivot.
     * NULL means the size inherits the product-level stock_units (legacy behaviour).
     */
    public function up(): void
    {
        Schema::table('product_size', function (Blueprint $table) {
            $table->unsignedInteger('stock')->nullable()->after('size_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_size', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};