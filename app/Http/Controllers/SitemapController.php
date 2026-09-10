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
        $sitemapUrl = Seo::url('/sitemap.xml');

        $content = implode(PHP_EOL, [
            'User-agent: *',
            'Allow: /',
            'Allow: /images/',
            'Allow: /storage/',
            'Allow: /uploads/',
            'Allow: /media/',
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
            'Disallow: /search',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /forgot-password',
            'Disallow: /reset-password',
            '',
            'User-agent: Googlebot-Image',
            'Allow: /',
            'Allow: /storage/',
            'Allow: /uploads/',
            'Allow: /media/',
            'Allow: /images/',
            '',
            'Sitemap: ' . $sitemapUrl,
        ]);

        return response($content)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function index(): Response
    {
        $staticPages = [
            ['url' => Seo::url('/'), 'priority' => '1.0', 'changefreq' => 'daily'],
            ['url' => Seo::url('/shop'), 'priority' => '0.9', 'changefreq' => 'daily'],
            ['url' => Seo::url('/faq'), 'priority' => '0.7', 'changefreq' => 'weekly'],
            ['url' => Seo::url('/about'), 'priority' => '0.6', 'changefreq' => 'monthly'],
            ['url' => Seo::url('/contact'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        // Specific category shortcuts
        $categoryShortcuts = ['tops', 'leggings', 'kurtis', 'maxi', 'nightwear'];
        $shortcutUrls = array_map(fn ($slug) => [
            'url' => Seo::url('/' . $slug),
            'priority' => '0.8',
            'changefreq' => 'weekly',
        ], $categoryShortcuts);

        $categories = Category::query()
            ->active()
            ->get()
            ->map(fn (Category $category) => [
                'url' => Seo::url('/category/' . $category->slug),
                'priority' => '0.8',
                'changefreq' => 'weekly',
                'lastmod' => $category->updated_at?->toIso8601String(),
            ])
            ->all();

        $products = Product::query()
            ->where('in_stock', true)
            ->with(['category', 'images'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function (Product $product) {
                $img = $product->image;
                if ($img && !preg_match('/^https?:\/\//', $img)) {
                    $img = Seo::asset('storage/' . ltrim($img, '/'));
                }

                return [
                    'url' => Seo::url('/product/' . $product->id),
                    'priority' => '0.8',
                    'changefreq' => 'weekly',
                    'lastmod' => $product->updated_at?->toIso8601String(),
                    'image' => $img,
                    'image_title' => $product->name . ' - ZYRA Lifestyle',
                ];
            })
            ->all();

        // Deduplicate URLs
        $allUrls = [];
        foreach (array_merge($staticPages, $shortcutUrls, $categories) as $item) {
            $allUrls[$item['url']] = $item;
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

        foreach ($allUrls as $u) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($u['url'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= '    <changefreq>' . $u['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $u['priority'] . "</priority>\n";
            if (!empty($u['lastmod'])) {
                $xml .= '    <lastmod>' . $u['lastmod'] . "</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }

        foreach ($products as $p) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($p['url'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= '    <changefreq>' . $p['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $p['priority'] . "</priority>\n";
            if (!empty($p['lastmod'])) {
                $xml .= '    <lastmod>' . $p['lastmod'] . "</lastmod>\n";
            }
            if (!empty($p['image'])) {
                $xml .= "    <image:image>\n";
                $xml .= '      <image:loc>' . htmlspecialchars($p['image'], ENT_XML1, 'UTF-8') . "</image:loc>\n";
                if (!empty($p['image_title'])) {
                    $xml .= '      <image:title>' . htmlspecialchars($p['image_title'], ENT_XML1, 'UTF-8') . "</image:title>\n";
                }
                $xml .= "    </image:image>\n";
            }
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}