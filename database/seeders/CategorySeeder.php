<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Tops',
                'slug' => 'tops',
                'description' => 'Chic crop tops, casual shirts, office wear & statement party tops.',
                'image' => 'https://images.unsplash.com/photo-1534126511673-b6899657816a?auto=format&fit=crop&w=800&q=80',
                'sort_order' => 1,
                'subcategories' => ['Peplum Tops', 'Tunics', 'Short Kurtis'],
            ],
            [
                'name' => 'Leggings',
                'slug' => 'leggings',
                'description' => 'Ultra-stretchable, breathable 4-way cotton, ankle-length & festive churidars.',
                'image' => 'https://images.unsplash.com/photo-1506619216599-9d16d0903dfd?auto=format&fit=crop&w=800&q=80',
                'sort_order' => 2,
                'subcategories' => ['Straight Pants', 'Palazzo', 'Leggings (Straight Fit & Ankle Fit)'],
            ],
            [
                'name' => 'Kurtis',
                'slug' => 'kurtis',
                'description' => 'Handcrafted block prints, graceful Anarkalis, and everyday office straight kurtis.',
                'image' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=800&q=80',
                'sort_order' => 3,
                'subcategories' => ['3 Pcs Set', '2 Pcs Set', 'Anarkali', 'Office Wear', 'Daily Wear', 'Festive Wear'],
            ],
            [
                'name' => 'Maxi Dresses',
                'slug' => 'maxi',
                'description' => 'Flowing tiered silhouettes, romantic bohemian florals, and evening party maxis.',
                'image' => 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=800&q=80',
                'sort_order' => 4,
                'subcategories' => ['Anarkali'],
            ],
            [
                'name' => 'Nightwear',
                'slug' => 'nightwear',
                'description' => 'Pure modal cotton PJ sets, cozy button-down night suits, and satin slips.',
                'image' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?auto=format&fit=crop&w=800&q=80',
                'sort_order' => 5,
                'subcategories' => ['Night Suits', 'Night Dresses', 'Cotton Nightwear', 'Printed Nightwear', 'Lounge Wear'],
            ],
            [
                'name' => 'Co-ords',
                'slug' => 'co-ords',
                'description' => 'Effortless matching sets designed for polished everyday style.',
                'image' => 'images/categories/co-ords.jpg',
                'sort_order' => 6,
                'subcategories' => ['Co-rds'],
            ],
        ];

        foreach ($categories as $catData) {
            $subcategoryNames = $catData['subcategories'];
            unset($catData['subcategories']);
            $cat = Category::updateOrCreate(['slug' => $catData['slug']], $catData);

            $newSlugs = [];
            foreach ($subcategoryNames as $i => $subName) {
                $newSlugs[] = \Illuminate\Support\Str::slug($subName);
                Subcategory::updateOrCreate(
                    ['category_id' => $cat->id, 'slug' => \Illuminate\Support\Str::slug($subName)],
                    ['name' => $subName, 'sort_order' => $i + 1]
                );
            }

            Subcategory::where('category_id', $cat->id)
                ->whereNotIn('slug', $newSlugs)
                ->delete();
        }
    }
}
