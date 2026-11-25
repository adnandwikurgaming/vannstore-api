<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_akses_tanpa_token_akan_ditolak_401()
    {
        // Akses endpoint yang butuh login tanpa header Authorization
        $response = $this->getJson('/api/v1/orders/me');
        
        $response->assertStatus(401);
    }

    public function test_mengakses_data_yang_tidak_ada_menghasilkan_404()
    {
        $user = User::factory()->pembeli()->create();

        // Coba akses ID pesanan yang tidak ada (misal ID 9999)
        $response = $this->actingAs($user)->getJson('/api/v1/orders/9999');

        // Pastikan format JSON-nya seragam sesuai dokumen (Anggota 3)
        $response->assertStatus(404);
        $response->assertJsonPath('message', 'Data tidak ditemukan');
    }

    public function test_pembeli_tidak_boleh_melihat_pesanan_milik_pembeli_lain_403()
    {
        // TAMBAHKAN BARIS INI DI PALING ATAS:
        

        // 1. Buat 2 pembeli dan 1 penjual
        $pembeliA = User::factory()->pembeli()->create();
        $pembeliB = User::factory()->pembeli()->create();
        $penjual = User::factory()->penjual()->create();
        
        $kategori = Category::factory()->create();
        $produk = Product::factory()->create(['seller_id' => $penjual->id, 'category_id' => $kategori->id, 'stock' => 5]);

        // 2. Pembeli B membuat pesanan (checkout)
        $responseCheckout = $this->actingAs($pembeliB)->postJson('/api/v1/orders', [
            'items' => [
                ['product_id' => $produk->id, 'qty' => 1]
            ],
            'shipping_address' => 'Jl. Rahasia Pembeli B'
        ]);
        
        // Ambil ID pesanan yang baru dibuat
        $orderId = $responseCheckout->json('data.0.id') ?? $responseCheckout->json('0.id');

        // 3. Pembeli A mencoba mengintip pesanan milik Pembeli B
        $response = $this->actingAs($pembeliA)->getJson('/api/v1/orders/' . $orderId);

        // 4. Pastikan ditolak dengan 403 Forbidden
        $response->assertStatus(403);
        
        // Opsional: cek pesan error-nya
        // $response->assertJsonPath('message', 'Anda tidak memiliki akses');
    }
}