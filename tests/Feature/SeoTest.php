<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_robots_txt_is_accessible_and_allows_image_indexing(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $this->assertStringContainsString('Sitemap:', $response->getContent());
        $this->assertStringContainsString('Allow: /storage/', $response->getContent());
        $this->assertStringContainsString('Allow: /images/', $response->getContent());
        $this->assertStringContainsString('Disallow: /search', $response->getContent());
        $this->assertStringContainsString('Disallow: /seller', $response->getContent());
    }

    public function test_faq_page_renders_faqpage_schema(): void
    {
        $response = $this->get('/faq');

        $response->assertStatus(200);
        $this->assertStringContainsString('ZYRA Lifestyle', $response->getContent());
        $this->assertStringContainsString('"@type": "FAQPage"', $response->getContent());
    }

    public function test_search_page_has_noindex_directive(): void
    {
        $response = $this->get('/search');

        $response->assertStatus(200);
        $this->assertStringContainsString('noindex, follow', $response->getContent());
    }

    public function test_cart_page_has_noindex_nofollow_directive(): void
    {
        $response = $this->get('/cart');

        $response->assertStatus(200);
        $this->assertStringContainsString('noindex, nofollow', $response->getContent());
    }
}
