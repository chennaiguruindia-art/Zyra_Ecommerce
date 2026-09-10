<?php

namespace App\Support;

use Illuminate\Support\Str;

class Seo
{
    public static function url(string $path = ''): string
    {
        $base = rtrim((string) config('seo.site_url'), '/');
        if ($path === '' || $path === '/') {
            return $base . '/';
        }
        return $base . '/' . ltrim($path, '/');
    }

    public static function asset(string $path): string
    {
        return self::url($path);
    }

    public static function canonical(): string
    {
        $path = '/' . ltrim(request()->path(), '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return self::url($path);
    }

    public static function siteName(): string
    {
        return (string) config('seo.site_name');
    }

    public static function jsonld(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return '<script type="application/ld+json">' . $json . '</script>';
    }

    public static function organizationSchema(): string
    {
        return self::jsonld([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => self::siteName(),
            'url' => self::url('/'),
            'logo' => config('seo.logo'),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => config('seo.contact.phone'),
                'contactType' => 'customer service',
                'areaServed' => 'IN',
                'availableLanguage' => ['English', 'Hindi'],
            ],
            'sameAs' => array_values((array) config('seo.social')),
        ]);
    }

    public static function websiteSchema(): string
    {
        return self::jsonld([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => self::siteName(),
            'url' => self::url('/'),
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => self::url('/search?q={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ]);
    }

    public static function localBusinessSchema(): string
    {
        return self::jsonld([
            '@context' => 'https://schema.org',
            '@type' => 'ClothingStore',
            'name' => self::siteName(),
            'url' => self::url('/'),
            'image' => config('seo.logo'),
            'telephone' => config('seo.contact.phone'),
            'email' => config('seo.contact.email'),
            'priceRange' => '₹299 - ₹3999',
            'currenciesAccepted' => 'INR',
            'paymentAccepted' => 'UPI, Credit Card, Debit Card, Net Banking, Cash on Delivery',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => '142, 100ft Road, HAL 2nd Stage, Indiranagar',
                'addressLocality' => 'Bengaluru',
                'addressRegion' => 'Karnataka',
                'postalCode' => '560038',
                'addressCountry' => 'IN',
            ],
            'areaServed' => 'IN',
            'sameAs' => array_values((array) config('seo.social')),
        ]);
    }

    public static function breadcrumbSchema(array $items): string
    {
        $list = [];
        foreach (array_values($items) as $i => $item) {
            if (empty($item['name'])) {
                continue;
            }
            $list[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                ...(isset($item['url']) ? ['item' => $item['url']] : []),
            ];
        }
        if (empty($list)) {
            return '';
        }
        return self::jsonld([
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ]);
    }

    public static function productSchema(array $product): string
    {
        $price = (float) ($product['price'] ?? 0);
        $img = $product['image'] ?? config('seo.logo');
        if ($img && !preg_match('/^https?:\/\//', $img)) {
            $img = self::asset('storage/' . ltrim($img, '/'));
        }

        $offers = [
            '@type' => 'Offer',
            'priceCurrency' => 'INR',
            'price' => number_format($price, 2, '.', ''),
            'url' => self::url('/product/' . ($product['id'] ?? 0)),
            'availability' => isset($product['in_stock']) && $product['in_stock']
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
        ];

        $aggregate = [];
        if (isset($product['rating']) && $product['rating'] > 0) {
            $aggregate = [
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => (float) $product['rating'],
                    'reviewCount' => (int) ($product['reviews_count'] ?? 0),
                ],
            ];
        }

        return self::jsonld(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'] ?? '',
            'image' => $img,
            'description' => Str::limit($product['description'] ?? '', 250),
            'brand' => ['@type' => 'Brand', 'name' => self::siteShortName()],
            'sku' => $product['sku'] ?? null,
            'category' => $product['category'] ?? null,
            'offers' => $offers,
            ...$aggregate,
        ], fn ($v) => $v !== null));
    }

    public static function siteShortName(): string
    {
        return (string) config('seo.site_short_name');
    }
}