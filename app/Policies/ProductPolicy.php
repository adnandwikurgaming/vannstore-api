<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProductPolicy
{
    /**
     * Hanya penjual yang dapat membuat produk.
     */
    public function create(User $user): Response
    {
        return $user->hasRole('penjual')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses');
    }

    /**
     * Hanya penjual pemilik produk yang dapat memperbarui produk.
     */
    public function update(User $user, Product $product): Response
    {
        $allowed = $user->hasRole('penjual')
            && $product->seller_id === $user->id;

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses');
    }

    /**
     * Hanya penjual pemilik produk yang dapat memperbarui stok.
     */
    public function updateStock(User $user, Product $product): Response
    {
        $allowed = $user->hasRole('penjual')
            && $product->seller_id === $user->id;

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses');
    }

    /**
     * Hanya admin yang dapat menonaktifkan produk.
     */
    public function deactivate(User $user): Response
    {
        return $user->hasRole('admin')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses');
    }
}
