<?php

namespace App\Policies;

use App\Models\SellerOrder;
use App\Models\User;

class SellerOrderPolicy
{
    public function view(User $user, SellerOrder $sellerOrder): bool
    {
        if ($user->isAdmin() || $user->isSupport()) {
            return true;
        }

        return $user->isSeller() && $user->shop && $user->shop->id === $sellerOrder->shop_id;
    }

    public function update(User $user, SellerOrder $sellerOrder): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSeller() && $user->shop && $user->shop->id === $sellerOrder->shop_id;
    }

    public function dispatch(User $user, SellerOrder $sellerOrder): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSeller() && $user->shop && $user->shop->id === $sellerOrder->shop_id;
    }

    public function cancel(User $user, SellerOrder $sellerOrder): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isSeller() && $user->shop && $user->shop->id === $sellerOrder->shop_id;
    }
}
