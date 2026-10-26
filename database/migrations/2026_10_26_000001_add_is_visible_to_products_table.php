<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Product visibility toggle controlled from the seller panel.
            // Hidden products stay in the DB but are excluded everywhere on
            // the buyer-facing storefront via the ProductVisibleScope.
            $table->boolean('is_visible')->default(true)->after('in_stock');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_visible');
        });
    }
};
