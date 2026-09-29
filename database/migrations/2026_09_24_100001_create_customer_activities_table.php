<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anonymous shopping-behavior events. Deliberately contains NO
     * customer personal data (no name, email, phone, address).
     */
    public function up(): void
    {
        Schema::create('customer_activities', function (Blueprint $table) {
            $table->id();
            $table->string('anonymous_id', 80)->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('product_name', 190);
            $table->unsignedBigInteger('variant_id')->nullable()->index();
            $table->string('size', 40)->nullable()->index();
            $table->string('color', 60)->nullable()->index();
            $table->string('action', 30)->index();
            $table->unsignedInteger('quantity')->nullable();
            $table->timestamps();
            $table->index('created_at');
            $table->index(['product_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_activities');
    }
};