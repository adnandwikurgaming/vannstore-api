<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Exceptions\InsufficientStockException;
use App\Exceptions\InvalidStatusTransitionException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Aturan bisnis pemesanan VannStore (UC-01, UC-02).
 */
class OrderService
{
    /**
     * Checkout: validasi stok terbaru di server, pecah pesanan per penjual,
     * simpan pesanan + item, dan potong stok dalam satu transaksi.
     *
     * @param  array{items: list<array{product_id: int, qty: int}>, shipping_address: string, notes?: ?string}  $data
     * @return Collection<int, Order> satu pesanan per penjual
     *
     * @throws InsufficientStockException
     */
    public function checkout(User $buyer, array $data): Collection
    {
        // Gabungkan produk yang sama; simpan indeks pertama untuk key galat "items.N.qty".
        $requested = [];
        foreach ($data['items'] as $index => $item) {
            $id = (int) $item['product_id'];
            $requested[$id] ??= ['qty' => 0, 'index' => $index];
            $requested[$id]['qty'] += (int) $item['qty'];
        }

        return DB::transaction(function () use ($buyer, $data, $requested) {
            // Kunci baris produk agar dua checkout bersamaan tidak sama-sama lolos cek stok.
            $products = Product::whereKey(array_keys($requested))
                ->orderBy('id') // urutan kunci konsisten untuk menghindari deadlock
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $errors = [];
            foreach ($requested as $productId => ['qty' => $qty, 'index' => $index]) {
                $product = $products->get($productId);
                $key = "items.{$index}.qty";

                if (! $product || ! $product->is_active) {
                    $errors["items.{$index}.product_id"][] = 'Produk tidak tersedia.';
                } elseif ($product->seller_id === $buyer->id) {
                    $errors["items.{$index}.product_id"][] = "Tidak dapat memesan produk milik sendiri ({$product->name}).";
                } elseif ($product->stock < $qty) {
                    $errors[$key][] = "Stok {$product->name} tersisa {$product->stock}, diminta {$qty}.";
                }
            }

            if ($errors) {
                throw new InsufficientStockException($errors);
            }

            return $products
                ->groupBy('seller_id')
                ->map(fn (Collection $sellerProducts, int $sellerId) => $this->createOrderForSeller(
                    $buyer, $sellerId, $sellerProducts, $requested, $data,
                ))
                ->values();
        });
    }

    /**
     * Penjual memperbarui status pesanan sesuai alur. Pembatalan mengembalikan stok.
     * Otorisasi kepemilikan pesanan dilakukan di policy sebelum method ini dipanggil.
     *
     * @throws InvalidStatusTransitionException
     */
    public function updateStatus(Order $order, OrderStatus $next): Order
    {
        $previous = DB::transaction(function () use ($order, $next) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            $current = $locked->status;

            if (! $current->canTransitionTo($next)) {
                throw new InvalidStatusTransitionException($current, $next);
            }

            if ($next === OrderStatus::Dibatalkan) {
                $this->restoreStock($locked);
            }

            $locked->update(['status' => $next]);

            return $current;
        });

        $order->refresh()->load('items', 'buyer');
        $order->buyer->notify(new OrderStatusChanged($order, $previous));

        return $order;
    }

    /**
     * @param  Collection<int, Product>  $products
     * @param  array<int, array{qty: int, index: int}>  $requested
     */
    private function createOrderForSeller(User $buyer, int $sellerId, Collection $products, array $requested, array $data): Order
    {
        $lines = $products->map(fn (Product $product) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'price' => $product->price,
            'qty' => $requested[$product->id]['qty'],
            'subtotal' => $product->price * $requested[$product->id]['qty'],
        ]);

        $order = Order::create([
            'code' => $this->generateCode(),
            'buyer_id' => $buyer->id,
            'seller_id' => $sellerId,
            'status' => OrderStatus::MenungguKonfirmasi,
            'total' => $lines->sum('subtotal'),
            'shipping_address' => $data['shipping_address'],
            'notes' => $data['notes'] ?? null,
        ]);

        $order->items()->createMany($lines->all());

        foreach ($products as $product) {
            $product->decrement('stock', $requested[$product->id]['qty']);
        }

        return $order->load('items');
    }

    private function restoreStock(Order $order): void
    {
        $items = $order->items()->get();
        $products = Product::whereKey($items->pluck('product_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $products->get($item->product_id)?->increment('stock', $item->qty);
        }
    }

    private function generateCode(): string
    {
        do {
            $code = 'VS-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Order::where('code', $code)->exists());

        return $code;
    }
}
