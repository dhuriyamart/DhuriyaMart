<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSellerApproved
{
    /**
     * Ensure that the seller has an approved shop before managing products or dispatching.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->guest(route('login'));
        }

        // Super Admin & Admin can bypass
        if ($user->isAdmin()) {
            return $next($request);
        }

        $shop = $user->shop;

        if (!$shop) {
            return redirect()->route('seller.onboarding')
                ->with('warning', 'Please complete your shop profile and KYC onboarding to get started.');
        }

        if ($shop->status === 'suspended') {
            abort(403, 'Your shop has been suspended by the platform administration.');
        }

        if ($shop->status !== 'approved') {
            return redirect()->route('seller.dashboard')
                ->with('error', 'Your shop verification is currently ' . $shop->status . '. You can manage products and inventory once approved by administration.');
        }

        return $next($request);
    }
}

