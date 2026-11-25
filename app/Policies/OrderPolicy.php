<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    public function create(User $user): Response
    {
        return $user->hasRole('pembeli')
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses');
    }

    public function view(User $user, Order $order): Response
    {
        $allowed = $user->hasRole('admin')
            || ($user->hasRole('pembeli')
                && $order->buyer_id === $user->id)
            || ($user->hasRole('penjual')
                && $order->seller_id === $user->id);

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses');
    }

    public function updateStatus(User $user, Order $order): Response
    {
        $allowed = $user->hasRole('penjual')
            && $order->seller_id === $user->id;

        return $allowed
            ? Response::allow()
            : Response::deny('Anda tidak memiliki akses');
    }
}
