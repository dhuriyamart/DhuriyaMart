<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use App\Services\ShippingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class SellerOrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService,
        protected ShippingService $shippingService
    ) {}

    public function index(Request $request): View
    {
        $shop = $request->user()->shop;

        $query = $shop->sellerOrders()
            ->with(['order.user', 'items.variant', 'shipment']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('seller.orders.index', compact('shop', 'orders'));
    }

    public function show(Request $request, int $id): View
    {
        $shop = $request->user()->shop;

        $sellerOrder = $shop->sellerOrders()
            ->with([
                'order.user',
                'order.shippingAddress',
                'items.variant',
                'shipment',
                'statusHistories',
            ])
            ->findOrFail($id);

        return view('seller.orders.show', compact('shop', 'sellerOrder'));
    }

    public function dispatch(Request $request, int $id): RedirectResponse
    {
        $shop = $request->user()->shop;
        $sellerOrder = $shop->sellerOrders()->findOrFail($id);

        $validated = $request->validate([
            'courier_name' => 'required|string|max:100',
            'tracking_number' => 'required|string|max:100',
            'tracking_url' => 'nullable|url|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $this->shippingService->createShipment(
                $sellerOrder,
                $validated['courier_name'],
                $validated['tracking_number'],
                $validated['tracking_url'] ?? null,
                $validated['notes'] ?? null
            );

            $this->orderService->updateSellerOrderStatus(
                $sellerOrder,
                'dispatched',
                "Dispatched via {$validated['courier_name']} (AWB: {$validated['tracking_number']})",
                $request->user()
            );

            return back()->with('success', 'Order marked as dispatched and tracking information saved.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(Request $request, int $id): RedirectResponse
    {
        $shop = $request->user()->shop;
        $sellerOrder = $shop->sellerOrders()->findOrFail($id);

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->orderService->cancelSellerOrder($sellerOrder, $validated['reason'], $request->user());

            return back()->with('success', 'Seller order cancelled and customer inventory restored.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
