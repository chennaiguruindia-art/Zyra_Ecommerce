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
                'address'       => '1st Floor, F 200, 1st St, Block F, Annanagar East, Chennai',
                'nearby_area'   => 'Opposite, Annanagar East Metro Station',
                'pincode'       => '600102',
                'state'         => 'Tamil Nadu',
                'district'      => 'Chennai',
            ]
        );
    }
}
