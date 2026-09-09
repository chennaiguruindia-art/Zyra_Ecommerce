<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Size;
use App\Models\Color;

class SizeAndColorSeeder extends Seeder
{
    public function run(): void
    {
        $sizes = [
            ['name' => 'XS', 'sort_order' => 1],
            ['name' => 'S',  'sort_order' => 2],
            ['name' => 'M',  'sort_order' => 3],
            ['name' => 'L',  'sort_order' => 4],
            ['name' => 'XL', 'sort_order' => 5],
            ['name' => 'XXL','sort_order' => 6],
        ];
        foreach ($sizes as $s) Size::firstOrCreate($s);

        $colors = [
            ['name' => 'Pink',    'hex_code' => '#F4A7B9'],
            ['name' => 'White',   'hex_code' => '#FFFFFF'],
            ['name' => 'Black',   'hex_code' => '#1A1A1A'],
            ['name' => 'Blue',    'hex_code' => '#6C9BCF'],
            ['name' => 'Green',   'hex_code' => '#27AE60'],
            ['name' => 'Red',     'hex_code' => '#C0392B'],
            ['name' => 'Yellow',  'hex_code' => '#F1C40F'],
            ['name' => 'Beige',   'hex_code' => '#F5F0E6'],
            ['name' => 'Gold',    'hex_code' => '#D4AF37'],
            ['name' => 'Lavender','hex_code' => '#B57EDC'],
            ['name' => 'Maroon',  'hex_code' => '#800000'],
            ['name' => 'Olive',   'hex_code' => '#808000'],
        ];
        foreach ($colors as $c) Color::firstOrCreate($c);
    }
}
