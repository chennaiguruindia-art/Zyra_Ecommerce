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
        Schema::table('coupons', function (Blueprint $table) {
            // Loyalty gate: when > 0, the customer's LAST Delivered order must
            // be at least this amount before the coupon becomes usable.
            // Empty/0 = no prior-purchase requirement (works like FIRSTORDER).
            $table->decimal('before_purchase_amount', 10, 2)->default(0)->after('min_order_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn('before_purchase_amount');
        });
    }
};
