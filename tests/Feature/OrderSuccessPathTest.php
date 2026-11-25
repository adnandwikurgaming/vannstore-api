<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Jalur sukses endpoint pesanan: user yang berhak harus lolos policy
 * dan mendapat respons 2xx, bukan hanya kasus yang ditolak.
 */
class OrderSuccessPathTest extends TestCase
{
    use RefreshDatabase;

    private function checkout(User $buyer, Product $product, int $qty = 1)
    {
        Sanctum::actingAs($buyer);

        return $this->postJson('/api/v1/orders', [
            'items' => [['product_id' => $product->id, 'qty' => $qty]],
            'shipping_address' => 'Jl. Merdeka No. 10, Bandung',
        ]);
    }

    public function test_pembeli_berhasil_checkout(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->checkout(User::factory()->pembeli()->create(), $product, 2)
            ->assertCreated()
            ->assertJsonPath('data.0.status', 'menunggu_konfirmasi')
            ->assertJsonPath('data.0.total', $product->price * 2);

        $this->assertSame(3, $product->fresh()->stock);
    }

    public function test_pembeli_bisa_melihat_detail_pesanan_sendiri(): void
    {
        $buyer = User::factory()->pembeli()->create();
        $orderId = $this->checkout($buyer, Product::factory()->create())->json('data.0.id');

        $this->getJson("/api/v1/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('data.id', $orderId);
    }

    public function test_penjual_bisa_memproses_pesanan_tokonya(): void
    {
        $product = Product::factory()->create();
        $orderId = $this->checkout(User::factory()->pembeli()->create(), $product)->json('data.0.id');

        Sanctum::actingAs($product->seller);

        $this->patchJson("/api/v1/seller/orders/{$orderId}/status", ['status' => 'diproses'])
            ->assertOk()
            ->assertJsonPath('data.status', 'diproses');
    }
}
