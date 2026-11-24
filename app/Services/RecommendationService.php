<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Smart Product Recommendation (KF-13, KNF-08, US-06).
 * Skor kategori = jumlah dilihat + 3 × jumlah dibeli (90 hari terakhir).
 * Tanpa riwayat → fallback produk populer, lalu produk terbaru.
 */
class RecommendationService
{
    public const LIMIT = 10;

    private const PURCHASE_WEIGHT = 3;

    private const TOP_CATEGORIES = 3;

    private const HISTORY_DAYS = 90;

    /** @return Collection<int, Product> */
    public function forUser(User $user, int $limit = self::LIMIT): Collection
    {
        $categoryIds = $this->topCategoryIds($user);

        $result = collect();
        if ($categoryIds->isNotEmpty()) {
            $result = $this->available($user)
                ->whereIn('category_id', $categoryIds)
                ->get()
                // urutkan sesuai peringkat kategori, lalu produk terbaru
                ->sortBy([
                    fn (Product $a, Product $b) => $categoryIds->search($a->category_id) <=> $categoryIds->search($b->category_id),
                    fn (Product $a, Product $b) => $b->created_at <=> $a->created_at,
                ])
                ->take($limit)
                ->values();
        }

        if ($result->count() < $limit) {
            $result = $result->concat($this->fallback($user, $limit - $result->count(), $result->pluck('id')));
        }

        return (new EloquentCollection($result->all()))->load('category', 'seller');
    }

    /** @return Collection<int, int> id kategori, skor tertinggi lebih dulu */
    private function topCategoryIds(User $user): Collection
    {
        $since = now()->subDays(self::HISTORY_DAYS);

        $views = DB::table('product_views')
            ->join('products', 'products.id', '=', 'product_views.product_id')
            ->where('product_views.user_id', $user->id)
            ->where('product_views.viewed_at', '>=', $since)
            ->selectRaw('products.category_id, COUNT(*) as score')
            ->groupBy('products.category_id');

        $purchases = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.buyer_id', $user->id)
            ->where('orders.created_at', '>=', $since)
            ->selectRaw('products.category_id, COUNT(*) * '.self::PURCHASE_WEIGHT.' as score')
            ->groupBy('products.category_id');

        return DB::query()
            ->fromSub($views->unionAll($purchases), 'scores')
            ->selectRaw('category_id, SUM(score) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->limit(self::TOP_CATEGORIES)
            ->pluck('category_id')
            ->map(fn ($id) => (int) $id);
    }

    /** @param  Collection<int, int>  $excludeIds */
    private function fallback(User $user, int $limit, Collection $excludeIds): Collection
    {
        // Populer = paling banyak terjual, lalu terbaru.
        return $this->available($user)
            ->whereNotIn('products.id', $excludeIds)
            ->withSum('orderItems as sold', 'qty')
            ->orderByDesc('sold')
            ->latest()
            ->limit($limit)
            ->get();
    }

    /** Produk aktif, stok ada, dan bukan milik user sendiri. */
    private function available(User $user)
    {
        return Product::query()
            ->active()
            ->inStock()
            ->where('seller_id', '!=', $user->id);
    }
}
