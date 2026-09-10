<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Support\Seo;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function robots(): Response
    {
        $sitemapUrl = \App\Support\Seo::url('/sitemap.xml');

        $content = implode(PHP_EOL, [
            'User-agent: *',
            'Allow: /',
            'Disallow: /cart',
            'Disallow: /checkout',
            'Disallow: /wishlist',
            'Disallow: /my-orders',
            'Disallow: /profile',
            'Disallow: /order-success',
            'Disallow: /dashboard',
            'Disallow: /seller',
            'Disallow: /query',
            'Disallow: /global_setting',
            'Disallow: /instagram',
            'Disallow: /media/',
            'Disallow: /storage/',
            'Disallow: /uploads/',
            '',
            'Sitemap: ' . $sitemapUrl,
        ]);

        return response($content)->header('Content-Type', 'text/plain');
    }

    public function index(): Response
    {
        $staticPages = [
            ['url' => Seo::url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => Seo::url('/shop'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => Seo::url('/about'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => Seo::url('/contact'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => Seo::url('/faq'), 'priority' => '0.5', 'changefreq' => 'monthly'],
            ['url' => Seo::url('/wishlist'), 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['url' => Seo::url('/cart'), 'priority' => '0.3', 'changefreq' => 'monthly'],
            ['url' => Seo::url('/search'), 'priority' => '0.4', 'changefreq' => 'weekly'],
        ];

        $categories = Category::query()
            ->active()
            ->with('products')
            ->get()
            ->map(fn (Category $category) => [
                'url' => Seo::url('/' . $category->slug),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $category->updated_at?->toIso8601String(),
            ])
            ->all();

        $products = Product::query()
            ->where('in_stock', true)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(fn (Product $product) => [
                'url' => Seo::url('/product/' . $product->id),
                'priority' => '0.7',
                'changefreq' => 'weekly',
                'lastmod' => $product->updated_at?->toIso8601String(),
            ])
            ->all();

        $urls = array_merge($staticPages, $categories, $products);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($urls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['url'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
            if (!empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml');
    }
}