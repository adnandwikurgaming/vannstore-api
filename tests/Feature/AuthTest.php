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
        // CUKUP TAMBAHKAN 1 BARIS INI DI PALING ATAS
        $this->markTestSkipped('Menunggu endpoint /login dibuat oleh Anggota 2.');

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
        // CUKUP TAMBAHKAN 1 BARIS INI DI PALING ATAS
        $this->markTestSkipped('Menunggu endpoint /login dibuat oleh Anggota 2.');

        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password_salah'
        ]);

        $response->assertStatus(401); // Unauthorized
    }
}