<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Tests\TestCase;

class ApiErrorHandlingTest extends TestCase
{
    public function test_unknown_api_route_returns_standard_404_response(): void
    {
        $response = $this->getJson('/api/v1/route-tidak-tersedia');

        $response->assertStatus(404)
            ->assertExactJson([
                'message' => 'Data tidak ditemukan',
            ]);
    }

    public function test_seller_endpoint_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/seller/orders');

        $response->assertStatus(401)
            ->assertExactJson([
                'message' => 'Unauthenticated.',
            ]);
    }


    public function test_non_seller_cannot_access_seller_endpoint(): void
    {
        $buyer = new User();
        $buyer->id = 1;
        $buyer->role = UserRole::Pembeli;

        $this->actingAs($buyer, 'sanctum');

        $response = $this->getJson('/api/v1/seller/orders');

        $response->assertStatus(403)
            ->assertExactJson([
                'message' => 'Anda tidak memiliki akses',
            ]);
    }

}
