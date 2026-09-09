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
            $table->string('shiprocket_order_id')->nullable()->after('razorpay_status')->index();
            $table->string('shipment_id')->nullable()->after('shiprocket_order_id')->index();
            $table->string('awb_code')->nullable()->after('shipment_id')->index();
            $table->string('courier_name')->nullable()->after('awb_code');
            $table->string('shipping_status')->nullable()->after('courier_name');
            $table->string('label_url')->nullable()->after('shipping_status');
            $table->timestamp('shiprocket_pushed_at')->nullable()->after('label_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'shiprocket_order_id',
                'shipment_id',
                'awb_code',
                'courier_name',
                'shipping_status',
                'label_url',
                'shiprocket_pushed_at',
            ]);
        });
    }
};