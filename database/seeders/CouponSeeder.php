<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Coupon;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            [
                'code' => 'WELCOME10',
                'discount_type' => 'percent',
                'discount_value' => 10,
                'min_order_amount' => 0,
                'max_discount' => null,
                'status' => true,
            ],
            [
                'code' => 'SAVE20',
                'discount_type' => 'percent',
                'discount_value' => 20,
                'min_order_amount' => 500,
                'max_discount' => 500,
                'status' => true,
            ],
            [
                'code' => 'FASHION15',
                'discount_type' => 'percent',
                'discount_value' => 15,
                'min_order_amount' => 299,
                'max_discount' => null,
                'status' => true,
            ],
        ];
        foreach ($coupons as $c) Coupon::firstOrCreate(['code' => $c['code']], $c);
    }
}
