<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('razorpay_status')->nullable()->after('razorpay_amount');
        });

        Schema::create('razorpay_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('razorpay_order_id')->nullable()->index();
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_signature')->nullable();
            $table->unsignedBigInteger('amount')->nullable();
            $table->string('currency', 10)->default('INR');
            $table->string('status')->nullable()->index();
            $table->string('method')->nullable();
            $table->unsignedBigInteger('fee')->nullable();
            $table->unsignedBigInteger('tax')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_description')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('razorpay_transactions');
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('razorpay_status');
        });
    }
};
