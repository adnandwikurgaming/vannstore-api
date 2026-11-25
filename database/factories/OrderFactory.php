<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Pesanan tanpa item. Untuk pesanan lengkap + potong stok, pakai OrderService.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'VS-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
            'buyer_id' => User::factory()->pembeli(),
            'seller_id' => User::factory()->penjual(),
            'status' => OrderStatus::MenungguKonfirmasi,
            'total' => fake()->numberBetween(10, 500) * 1000,
            'shipping_address' => fake()->address(),
            'notes' => null,
        ];
    }

    public function status(OrderStatus $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }
}
