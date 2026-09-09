<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Banner;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $banners = [
            [
                'title' => 'Summer Kurti Collection 2026',
                'subtitle' => 'Handcrafted block prints & graceful Anarkalis designed for the modern Indian woman.',
                'badge' => 'New Collection',
                'image' => 'images/banners/image.png',
                'link' => '/kurtis',
                'button_text' => 'Explore Kurtis',
                'position' => 'hero',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'title' => 'Effortless Maxi Dresses',
                'subtitle' => 'Flowing silhouettes and botanical florals for every occasion from brunch to beach.',
                'badge' => 'Trending Now',
                'image' => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1600&q=80',
                'link' => '/maxi',
                'button_text' => 'Shop Maxi Dresses',
                'position' => 'hero',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'title' => 'Cozy Night Essentials',
                'subtitle' => 'Pure modal cotton pyjama sets crafted for deep, comfortable sleep.',
                'badge' => 'Just Arrived',
                'image' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?auto=format&fit=crop&w=1600&q=80',
                'link' => '/nightwear',
                'button_text' => 'Shop Nightwear',
                'position' => 'hero',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'title' => 'Flat 20% Off - Use Code SAVE20',
                'subtitle' => 'On orders above Rs. 500. Limited time offer on selected styles.',
                'badge' => 'Special Offer',
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1200&q=80',
                'link' => '/shop',
                'button_text' => 'Shop Now',
                'position' => 'promo',
                'is_active' => true,
                'sort_order' => 1,
            ],
        ];

        foreach ($banners as $banner) {
            Banner::updateOrCreate(
                [
                    'title' => $banner['title'],
                    'position' => $banner['position'],
                ],
                $banner
            );
        }
    }
}
