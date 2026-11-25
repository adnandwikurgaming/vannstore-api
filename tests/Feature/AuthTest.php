<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_bisa_login_dan_mendapatkan_token()
    {

        $user = User::factory()->pembeli()->create([
            'password' => bcrypt('password123')
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'token',
                'user' => ['id', 'name', 'role']
            ]
        ]);
    }

    public function test_login_gagal_jika_password_salah()
    {

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password_salah'
        ]);

        $response->assertStatus(401); // Unauthorized
    }

    public function test_register_penjual_mendapat_token()
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Toko Baru',
            'email' => 'tokobaru@vannstore.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'penjual',
            'store_name' => 'Toko Baru Craft',
        ])
            ->assertCreated()
            ->assertJsonPath('data.user.role', 'penjual')
            ->assertJsonMissingPath('data.user.email');
    }

    public function test_register_sebagai_admin_ditolak()
    {
        $this->postJson('/api/v1/register', [
            'name' => 'Penyusup',
            'email' => 'penyusup@vannstore.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('role');
    }

    public function test_logout_mencabut_token()
    {
        $user = User::factory()->pembeli()->create(['password' => bcrypt('password123')]);
        $token = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password123',
        ])->json('data.token');

        $this->withToken($token)->postJson('/api/v1/logout')->assertNoContent();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}