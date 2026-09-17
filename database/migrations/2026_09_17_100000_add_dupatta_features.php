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
            $table->boolean('dupatta_enabled')->default(false)->after('is_trending');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->string('dupatta')->nullable()->after('color');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('dupatta_enabled');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('dupatta');
        });
    }
};