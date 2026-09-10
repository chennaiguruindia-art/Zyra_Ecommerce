<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_seller_page(): void
    {
        $response = $this->get('/seller');

        $response->assertRedirect('/login');
    }

    public function test_guest_is_redirected_to_login_when_accessing_seller_subroutes(): void
    {
        $response = $this->get('/seller/products');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_seller_can_access_seller_dashboard(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
        ]);

        $response = $this->actingAs($seller)->get('/seller');

        $response->assertStatus(200);
    }

    public function test_authenticated_customer_cannot_access_seller_dashboard(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $response = $this->actingAs($customer)->get('/seller');

        $response->assertStatus(403);
    }

    public function test_seller_login_redirects_to_intended_seller_page(): void
    {
        $seller = User::factory()->create([
            'role' => 'seller',
            'password' => bcrypt('password123'),
        ]);

        // Attempt to visit protected seller URL as guest
        $this->get('/seller');

        // Log in
        $response = $this->post('/login', [
            'login' => $seller->email,
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($seller);
        $response->assertRedirect('/seller');
    }
}