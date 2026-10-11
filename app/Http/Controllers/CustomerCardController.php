<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class CustomerCardController extends Controller
{
    /**
     * Show Customer Digital Village Smart Mart QR Card.
     */
    public function show(Request $request): View
    {
        $user = $request->user();
        $user->ensureCustomerIdentification();

        $recentInStoreOrders = $user->orders()
            ->with(['items', 'store', 'exitVerification'])
            ->whereIn('order_type', ['pos_self_service', 'pos_staff_assisted'])
            ->latest()
            ->take(5)
            ->get();

        return view('account.qr-card', [
            'user' => $user,
            'recentInStoreOrders' => $recentInStoreOrders,
        ]);
    }

    /**
     * Language switcher for Hindi / English rural UX.
     */
    public function setLocale(string $locale): RedirectResponse
    {
        if (in_array($locale, ['en', 'hi'])) {
            Session::put('locale', $locale);
            App::setLocale($locale);
        }

        return back();
    }
}
