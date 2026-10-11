<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\ProductVariant;
use App\Models\ReturnItem;
use App\Models\ReturnRequest;
use App\Models\Wishlist;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AccountController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function dashboard(Request $request): View
    {
        $user = $request->user();
        $recentOrders = $user->orders()->with(['sellerOrders.shop', 'items'])->latest()->take(5)->get();
        $totalOrdersCount = $user->orders()->count();
        $wishlistCount = $user->wishlists()->count();
        $defaultAddress = $user->defaultAddress();

        return view('customer.dashboard', compact(
            'user',
            'recentOrders',
            'totalOrdersCount',
            'wishlistCount',
            'defaultAddress'
        ));
    }

    public function orders(Request $request): View
    {
        $orders = $request->user()->orders()
            ->with(['sellerOrders.shop', 'items.variant'])
            ->latest()
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }

    public function orderDetails(Request $request, int $id): View
    {
        $order = $request->user()->orders()
            ->with([
                'sellerOrders.shop',
                'sellerOrders.items.variant',
                'sellerOrders.shipment',
                'addresses',
                'payments',
                'statusHistories',
            ])
            ->findOrFail($id);

        return view('customer.order-details', compact('order'));
    }

    public function invoice(Request $request, int $id): View
    {
        $order = $request->user()->orders()
            ->with(['sellerOrders.shop', 'sellerOrders.items', 'addresses', 'payments'])
            ->findOrFail($id);

        return view('customer.invoice', compact('order'));
    }

    public function cancelOrder(Request $request, int $id): RedirectResponse
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $order = $request->user()->orders()->findOrFail($id);

        try {
            $this->orderService->cancelOrder($order, $request->input('reason'), $request->user());
            return back()->with('success', 'Your order cancellation request has been processed.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function returnOrder(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'seller_order_id' => 'required|exists:seller_orders,id',
            'reason' => 'required|string|max:200',
            'customer_note' => 'nullable|string|max:500',
            'order_item_id' => 'required|array',
            'order_item_id.*' => 'exists:order_items,id',
        ]);

        $order = $request->user()->orders()->findOrFail($id);
        $sellerOrder = $order->sellerOrders()->findOrFail($request->input('seller_order_id'));

        $returnReq = ReturnRequest::create([
            'order_id' => $order->id,
            'seller_order_id' => $sellerOrder->id,
            'user_id' => $request->user()->id,
            'status' => 'requested',
            'reason' => $request->input('reason'),
            'customer_note' => $request->input('customer_note'),
        ]);

        foreach ($request->input('order_item_id') as $itemId) {
            $item = $sellerOrder->items()->find($itemId);
            if ($item) {
                ReturnItem::create([
                    'return_request_id' => $returnReq->id,
                    'order_item_id' => $item->id,
                    'quantity' => $item->quantity,
                    'reason' => $request->input('reason'),
                ]);
            }
        }

        return back()->with('success', 'Your return request has been submitted for seller review.');
    }

    public function addresses(Request $request): View
    {
        $addresses = $request->user()->addresses()->latest()->get();
        return view('customer.addresses', compact('addresses'));
    }

    public function saveAddress(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'address_id' => 'nullable|exists:addresses,id',
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'postal_code' => 'required|string|max:20',
            'country' => 'nullable|string|max:50',
            'is_default' => 'nullable|boolean',
        ]);

        $user = $request->user();

        if (!empty($validated['is_default'])) {
            $user->addresses()->update(['is_default' => false]);
        }

        if (!empty($validated['address_id'])) {
            $address = $user->addresses()->findOrFail($validated['address_id']);
            $address->update($validated);
        } else {
            $validated['user_id'] = $user->id;
            $validated['is_default'] = $validated['is_default'] ?? ($user->addresses()->count() === 0);
            Address::create($validated);
        }

        return back()->with('success', 'Address saved successfully.');
    }

    public function deleteAddress(Request $request, int $id): RedirectResponse
    {
        $request->user()->addresses()->where('id', $id)->delete();
        return back()->with('success', 'Address deleted.');
    }

    public function wishlist(Request $request): View
    {
        $wishlists = $request->user()->wishlists()
            ->with(['variant.product.shop', 'variant.product.images'])
            ->latest()
            ->paginate(12);

        return view('customer.wishlist', compact('wishlists'));
    }

    public function toggleWishlist(Request $request, int $variantId): RedirectResponse
    {
        $user = $request->user();
        $variant = ProductVariant::findOrFail($variantId);

        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_variant_id', $variantId)
            ->first();

        if ($existing) {
            $existing->delete();
            return back()->with('info', 'Item removed from your Wishlist.');
        }

        Wishlist::create([
            'user_id' => $user->id,
            'product_variant_id' => $variantId,
        ]);

        return back()->with('success', 'Item added to your Wishlist!');
    }

    public function storeReview(Request $request, int $productId): RedirectResponse
    {
        $product = Product::findOrFail($productId);
        $user = $request->user();

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:150',
            'comment' => 'required|string|max:1000',
        ]);

        // Check if user has bought this product
        $hasPurchased = $user->orders()
            ->whereHas('items', fn($q) => $q->where('product_id', $productId))
            ->exists();

        ProductReview::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $user->id],
            [
                'rating' => $validated['rating'],
                'title' => $validated['title'] ?? null,
                'comment' => $validated['comment'],
                'status' => 'approved',
                'is_verified_purchase' => $hasPurchased,
            ]
        );

        return back()->with('success', 'Thank you! Your verified product review has been submitted.');
    }
}

