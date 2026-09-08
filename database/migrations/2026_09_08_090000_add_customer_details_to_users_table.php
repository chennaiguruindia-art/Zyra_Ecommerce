<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('address')->nullable()->after('email');
            $table->string('nearby_area')->nullable()->after('address');
            $table->string('pincode', 6)->nullable()->after('nearby_area');
            $table->string('state')->nullable()->after('pincode');
            $table->string('phone_number', 10)->nullable()->unique()->after('state');
            $table->string('district')->nullable()->after('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['phone_number']);
            $table->dropColumn([
                'address',
                'nearby_area',
                'pincode',
                'state',
                'phone_number',
                'district',
            ]);
        });
    }
};