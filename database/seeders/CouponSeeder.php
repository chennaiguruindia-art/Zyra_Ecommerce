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
                'code' => 'FIRSTORDER',
                'discount_type' => 'fixed',
                'discount_value' => 100,
                'min_order_amount' => 0,
                'before_purchase_amount' => 0,
                'max_discount' => null,
                'status' => true,
            ],
            [
                'code' => 'ZYRA500',
                'discount_type' => 'fixed',
                'discount_value' => 500,
                'min_order_amount' => 0,
                'before_purchase_amount' => 5000,
                'max_discount' => null,
                'status' => true,
            ],
            [
                'code' => 'ZYRA300',
                'discount_type' => 'fixed',
                'discount_value' => 250,
                'min_order_amount' => 0,
                'before_purchase_amount' => 3000,
                'max_discount' => null,
                'status' => true,
            ],
        ];

        Coupon::query()->whereNotIn('code', array_column($coupons, 'code'))->delete();

        foreach ($coupons as $c) {
            Coupon::updateOrCreate(['code' => $c['code']], $c);
        }
    }
}
