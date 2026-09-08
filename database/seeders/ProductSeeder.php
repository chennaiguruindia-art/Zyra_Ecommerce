<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Size;
use App\Models\Color;
use App\Helpers\ProductData;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = array_slice(ProductData::getProducts(), 0, 2);

        // Build lookup maps
        $categoryMap = Category::all()->keyBy('slug');
        $subcategoryMap = Subcategory::all()->groupBy('name');
        $sizeMap = Size::all()->keyBy('name');
        $colorMap = Color::all()->keyBy('name');

        // Category slug map (name -> slug)
        $catNameToSlug = [
            'Tops' => 'tops',
            'Leggings' => 'leggings',
            'Kurtis' => 'kurtis',
            'Maxi' => 'maxi',
            'Nightwear' => 'nightwear',
        ];

        foreach ($products as $p) {
            $catSlug = $catNameToSlug[$p['category']] ?? Str::slug($p['category']);
            $cat = $categoryMap[$catSlug] ?? null;
            if (!$cat) continue;

            $subName = $p['subcategory'] ?? null;
            $sub = null;
            if ($subName && isset($subcategoryMap[$subName])) {
                $sub = $subcategoryMap[$subName]->first();
            }

            // Create product
            $product = Product::create([
                'category_id'    => $cat->id,
                'subcategory_id' => $sub?->id,
                'name'           => $p['name'],
                'slug'           => Str::slug($p['name']) . '-' . $p['id'],
                'sku'            => $p['sku'] ?? ('ZYR-' . $p['id']),
                'price'          => $p['price'],
                'old_price'      => $p['old_price'] ?? null,
                'discount'       => $p['discount'] ?? null,
                'stock_units'    => (int)($p['id'] % 7 === 0 ? 4 : ($p['id'] % 3 === 0 ? 9 : 25 + ($p['id'] * 3) % 40)),
                'in_stock'       => $p['stock'] ?? true,
                'image'          => $p['image'],
                'material'       => $p['material'] ?? null,
                'fit'            => $p['fit'] ?? null,
                'care'           => $p['care'] ?? null,
                'description'    => $p['description'] ?? null,
                'badge'          => $p['badge'] ?? null,
                'is_featured'    => $p['featured'] ?? false,
                'is_best_seller' => $p['best_seller'] ?? false,
                'is_trending'    => $p['trending'] ?? false,
                'rating'         => $p['rating'] ?? 4.5,
                'reviews_count'  => $p['reviews'] ?? 0,
            ]);

            // Save gallery images
            if (!empty($p['images'])) {
                foreach ($p['images'] as $idx => $imgUrl) {
                    ProductImage::create([
                        'product_id' => $product->id,
                        'image_path' => $imgUrl,
                        'is_cover'   => $idx === 0,
                        'sort_order' => $idx,
                    ]);
                }
            } else {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $p['image'],
                    'is_cover'   => true,
                    'sort_order' => 0,
                ]);
            }

            // Attach sizes
            if (!empty($p['sizes'])) {
                $sizeIds = [];
                foreach ($p['sizes'] as $sz) {
                    if (isset($sizeMap[$sz])) $sizeIds[] = $sizeMap[$sz]->id;
                }
                $product->sizes()->sync($sizeIds);
            }

            // Attach colors
            if (!empty($p['colors'])) {
                $colorIds = [];
                foreach ($p['colors'] as $cl) {
                    if (isset($colorMap[$cl])) $colorIds[] = $colorMap[$cl]->id;
                }
                $product->colors()->sync($colorIds);
            }
        }
    }
}
