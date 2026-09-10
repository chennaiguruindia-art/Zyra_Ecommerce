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

    public static function faqSchema(array $faqs): string
    {
        $mainEntity = [];
        foreach ($faqs as $faq) {
            if (empty($faq['question']) || empty($faq['answer'])) {
                continue;
            }
            $mainEntity[] = [
                '@type' => 'Question',
                'name' => trim(strip_tags($faq['question'])),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(strip_tags($faq['answer'])),
                ],
            ];
        }

        if (empty($mainEntity)) {
            return '';
        }

        return self::jsonld([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $mainEntity,
        ]);
    }

    public static function itemListSchema(string $name, array $items): string
    {
        $elements = [];
        foreach (array_values($items) as $idx => $item) {
            $elements[] = [
                '@type' => 'ListItem',
                'position' => $idx + 1,
                'name' => $item['name'] ?? 'Product',
                'url' => isset($item['url']) ? $item['url'] : self::url('/product/' . ($item['id'] ?? '')),
                ...(isset($item['image']) ? ['image' => $item['image']] : []),
            ];
        }

        return self::jsonld([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => $name,
            'itemListElement' => $elements,
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
            'priceValidUntil' => date('Y-12-31', strtotime('+1 year')),
            'url' => self::url('/product/' . ($product['id'] ?? 0)),
            'availability' => isset($product['in_stock']) && $product['in_stock']
                ? 'https://schema.org/InStock'
                : 'https://schema.org/OutOfStock',
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => [
                '@type' => 'Organization',
                'name' => self::siteName(),
            ],
            'shippingDetails' => [
                '@type' => 'OfferShippingDetails',
                'shippingRate' => [
                    '@type' => 'MonetaryAmount',
                    'value' => '0',
                    'currency' => 'INR',
                ],
                'shippingDestination' => [
                    '@type' => 'DefinedRegion',
                    'addressCountry' => 'IN',
                ],
                'deliveryTime' => [
                    '@type' => 'ShippingDeliveryTime',
                    'handlingTime' => [
                        '@type' => 'QuantitativeValue',
                        'minValue' => 0,
                        'maxValue' => 1,
                        'unitCode' => 'd',
                    ],
                    'transitTime' => [
                        '@type' => 'QuantitativeValue',
                        'minValue' => 2,
                        'maxValue' => 5,
                        'unitCode' => 'd',
                    ],
                ],
            ],
            'hasMerchantReturnPolicy' => [
                '@type' => 'MerchantReturnPolicy',
                'applicableCountry' => 'IN',
                'returnPolicyCategory' => 'https://schema.org/MerchantReturnFiniteReturnWindow',
                'merchantReturnDays' => 7,
                'returnMethod' => 'https://schema.org/ReturnByMail',
                'returnFees' => 'https://schema.org/FreeReturn',
            ],
        ];

        $aggregate = [];
        if (isset($product['rating']) && $product['rating'] > 0) {
            $aggregate = [
                'aggregateRating' => [
                    '@type' => 'AggregateRating',
                    'ratingValue' => (float) $product['rating'],
                    'reviewCount' => (int) ($product['reviews_count'] ?? $product['reviews'] ?? 10),
                    'bestRating' => '5',
                    'worstRating' => '1',
                ],
            ];
        }

        return self::jsonld(array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product['name'] ?? '',
            'image' => $img,
            'description' => Str::limit($product['description'] ?? 'Modern fashion style from ZYRA Lifestyle.', 250),
            'brand' => [
                '@type' => 'Brand',
                'name' => self::siteName(),
            ],
            'sku' => $product['sku'] ?? ('ZYR-' . ($product['id'] ?? '1')),
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