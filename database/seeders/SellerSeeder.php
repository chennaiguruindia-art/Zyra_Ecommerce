<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'order@shopwithzyra.in'],
            [
                'name'          => 'ZYRA Seller',
                'role'          => 'seller',
                'password'      => Hash::make('Zyra@9876'),
                'phone_number'  => '9876543210',
                'address'       => 'ZYRA Office',
                'nearby_area'   => 'MG Road',
                'pincode'       => '560001',
                'state'         => 'Karnataka',
                'district'      => 'Bengaluru Urban',
            ]
        );
    }
}
