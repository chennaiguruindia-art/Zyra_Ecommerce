<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            SizeAndColorSeeder::class,
            CouponSeeder::class,
            BannerSeeder::class,
        ]);
    }
}
