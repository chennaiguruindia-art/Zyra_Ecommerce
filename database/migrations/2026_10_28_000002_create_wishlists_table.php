<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persistent wishlist state — stores which products each visitor has saved.
     * Logged-in users identified by user_id; guests by session_id.
     * On login the guest rows are merged into the user's rows automatically.
     */
    public function up(): void
    {
        Schema::create('wishlists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete()->index();
            $table->string('session_id', 100)->nullable()->index();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // One row per user+product or session+product
            $table->unique(['user_id',    'product_id'], 'wl_user_product');
            $table->unique(['session_id', 'product_id'], 'wl_session_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wishlists');
    }
};
