<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminAuditLogController;
use App\Http\Controllers\AdminCategoryController;
use App\Http\Controllers\AdminCouponController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminPaymentController;
use App\Http\Controllers\AdminPriceMonitoringController;
use App\Http\Controllers\AdminProductController;
use App\Http\Controllers\AdminPurchaseOrderController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminSellerController;
use App\Http\Controllers\AdminSettingController;
use App\Http\Controllers\AdminSettlementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CashDeskController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerCardController;
use App\Http\Controllers\ExitVerificationController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SellerDashboardController;
use App\Http\Controllers\SellerInventoryController;
use App\Http\Controllers\SellerOrderController;
use App\Http\Controllers\SellerProductController;
use App\Http\Controllers\SellerProfileController;
use App\Http\Controllers\SellerSettlementController;
use App\Http\Controllers\VillageStoreController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Storefront Routes
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [CatalogController::class, 'search'])->name('catalog.search');
Route::get('/categories/{slug}', [CatalogController::class, 'category'])->name('catalog.category');
Route::get('/products/{slug}', [CatalogController::class, 'product'])->name('catalog.product');
Route::get('/shops/{slug}', [CatalogController::class, 'shop'])->name('catalog.shop');
Route::get('/pages/{slug}', [CatalogController::class, 'page'])->name('catalog.page');

// Cart Routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'addItem'])->name('cart.add');
Route::patch('/cart/items/{id}', [CartController::class, 'updateItem'])->name('cart.update');
Route::delete('/cart/items/{id}', [CartController::class, 'removeItem'])->name('cart.remove');
Route::post('/cart/coupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/cart/coupon', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// Checkout Routes
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');
Route::get('/checkout/confirmation/{id}', [CheckoutController::class, 'confirmation'])->name('checkout.confirmation')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/seller/register', [AuthController::class, 'showSellerRegister'])->name('seller.register');
    Route::post('/seller/register', [AuthController::class, 'registerSeller']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Customer Account Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [AccountController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [AccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{id}', [AccountController::class, 'orderDetails'])->name('orders.show');
    Route::get('/orders/{id}/invoice', [AccountController::class, 'invoice'])->name('orders.invoice');
    Route::post('/orders/{id}/cancel', [AccountController::class, 'cancelOrder'])->name('orders.cancel');
    Route::post('/orders/{id}/returns', [AccountController::class, 'returnOrder'])->name('orders.return');
    Route::get('/addresses', [AccountController::class, 'addresses'])->name('addresses');
    Route::post('/addresses', [AccountController::class, 'saveAddress'])->name('addresses.save');
    Route::delete('/addresses/{id}', [AccountController::class, 'deleteAddress'])->name('addresses.delete');
    Route::get('/wishlist', [AccountController::class, 'wishlist'])->name('wishlist');
    Route::post('/wishlist/{variantId}', [AccountController::class, 'toggleWishlist'])->name('wishlist.toggle');
    Route::get('/qr-card', [CustomerCardController::class, 'show'])->name('qr-card');
});

Route::get('/language/{locale}', [CustomerCardController::class, 'setLocale'])->name('locale.set');

// Product Reviews
Route::post('/products/{id}/reviews', [AccountController::class, 'storeReview'])->name('products.reviews.store')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Seller Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:seller'])->prefix('seller')->name('seller.')->group(function () {
    Route::get('/dashboard', [SellerDashboardController::class, 'index'])->name('dashboard');
    Route::get('/onboarding', [SellerProfileController::class, 'index'])->name('onboarding');
    Route::get('/profile', [SellerProfileController::class, 'index'])->name('profile');
    Route::post('/profile', [SellerProfileController::class, 'update'])->name('profile.update');

    // Routes requiring active/approved shop status
    Route::middleware('seller.approved')->group(function () {
        // Products
        Route::get('/products', [SellerProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [SellerProductController::class, 'create'])->name('products.create');
        Route::post('/products', [SellerProductController::class, 'store'])->name('products.store');
        Route::get('/products/{id}/edit', [SellerProductController::class, 'edit'])->name('products.edit');
        Route::put('/products/{id}', [SellerProductController::class, 'update'])->name('products.update');
        Route::delete('/products/{id}', [SellerProductController::class, 'destroy'])->name('products.destroy');

        // Inventory
        Route::get('/inventory', [SellerInventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory/{variantId}', [SellerInventoryController::class, 'update'])->name('inventory.update');

        // Orders
        Route::get('/orders', [SellerOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [SellerOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/dispatch', [SellerOrderController::class, 'dispatch'])->name('orders.dispatch');
        Route::post('/orders/{id}/cancel', [SellerOrderController::class, 'cancel'])->name('orders.cancel');

        // Settlements & Ledger
        Route::get('/settlements', [SellerSettlementController::class, 'index'])->name('settlements.index');
    });
});

/*
|--------------------------------------------------------------------------
| Admin Portal Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super-admin,admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Products & Moderation
    Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
    Route::post('/products/{id}/moderate', [AdminProductController::class, 'moderate'])->name('products.moderate');
    Route::post('/products/{id}/toggle-featured', [AdminProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy'])->name('products.destroy');

    // Categories
    Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

    // Sellers
    Route::get('/sellers', [AdminSellerController::class, 'index'])->name('sellers.index');
    Route::get('/sellers/{id}', [AdminSellerController::class, 'show'])->name('sellers.show');
    Route::post('/sellers/{id}/verify', [AdminSellerController::class, 'verify'])->name('sellers.verify');

    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');

    // Payments & Refunds
    Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{id}/refund', [AdminPaymentController::class, 'refund'])->name('payments.refund');

    // Settlements
    Route::get('/settlements', [AdminSettlementController::class, 'index'])->name('settlements.index');
    Route::post('/settlements/generate/{shopId}', [AdminSettlementController::class, 'generate'])->name('settlements.generate');
    Route::post('/settlements/{id}/paid', [AdminSettlementController::class, 'markPaid'])->name('settlements.paid');

    // Coupons
    Route::get('/coupons', [AdminCouponController::class, 'index'])->name('coupons.index');
    Route::post('/coupons', [AdminCouponController::class, 'store'])->name('coupons.store');
    Route::post('/coupons/{id}/toggle', [AdminCouponController::class, 'toggle'])->name('coupons.toggle');
    Route::delete('/coupons/{id}', [AdminCouponController::class, 'destroy'])->name('coupons.destroy');

    // Reports & Analytics
    Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
    Route::get('/price-monitoring', [AdminPriceMonitoringController::class, 'index'])->name('price-monitoring');

    // Settings
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');

    // Audit Logs
    Route::get('/audit-logs', [AdminAuditLogController::class, 'index'])->name('audit-logs.index');

    // Village Smart Mart: Cash Desk & Daily Reconciliation
    Route::get('/cash-desk', [CashDeskController::class, 'index'])->name('cash-desk.index');
    Route::post('/cash-desk/open', [CashDeskController::class, 'open'])->name('cash-desk.open');
    Route::post('/cash-desk/entry', [CashDeskController::class, 'addEntry'])->name('cash-desk.entry');
    Route::post('/cash-desk/close', [CashDeskController::class, 'close'])->name('cash-desk.close');
    Route::post('/cash-desk/deposit', [CashDeskController::class, 'depositCustomer'])->name('cash-desk.deposit');

    // Village Smart Mart: Automated Purchase Orders (PO) & Inventory Replenishment
    Route::get('/purchase-orders', [AdminPurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::post('/purchase-orders/auto-generate', [AdminPurchaseOrderController::class, 'autoGenerate'])->name('purchase-orders.auto-generate');
    Route::get('/purchase-orders/{id}', [AdminPurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::post('/purchase-orders/{id}/approve', [AdminPurchaseOrderController::class, 'approve'])->name('purchase-orders.approve');
    Route::post('/purchase-orders/{id}/receive', [AdminPurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');

    // Village Smart Mart: Multi-Store Village Branches
    Route::get('/village-stores', [VillageStoreController::class, 'index'])->name('village-stores.index');
    Route::post('/village-stores', [VillageStoreController::class, 'store'])->name('village-stores.store');
    Route::post('/village-stores/{id}/toggle', [VillageStoreController::class, 'toggle'])->name('village-stores.toggle');
});

/*
|--------------------------------------------------------------------------
| Payment & Webhook Routes
|--------------------------------------------------------------------------
*/
Route::post('/payments/create-order', [PaymentController::class, 'createOrder'])->name('payments.create-order')->middleware('auth');
Route::post('/payments/verify', [PaymentController::class, 'verify'])->name('payments.verify');
Route::post('/payments/mock/{id}', [PaymentController::class, 'mockSuccess'])->name('payments.mock');
Route::post('/webhooks/payments/{provider}', [PaymentController::class, 'webhook'])->name('payments.webhook');

/*
|--------------------------------------------------------------------------
| Village Smart Mart POS & Dual Exit Verification Routes
|--------------------------------------------------------------------------
*/
Route::get('/pos/kiosk', [PosController::class, 'kiosk'])->name('pos.kiosk');
Route::get('/pos/receipt/{orderNumber}', [PosController::class, 'receipt'])->name('pos.receipt');
Route::post('/pos/scan', [PosController::class, 'scan'])->name('pos.scan');
Route::post('/pos/items/{variantId}', [PosController::class, 'updateQuantity'])->name('pos.items.update');
Route::delete('/pos/items/{variantId}', [PosController::class, 'removeItem'])->name('pos.items.remove');
Route::post('/pos/clear', [PosController::class, 'clear'])->name('pos.clear');
Route::get('/pos/customer-lookup', [PosController::class, 'customerLookup'])->name('pos.customer-lookup');
Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('pos.checkout');

Route::middleware(['auth', 'role:super-admin,admin,store-manager,cashier'])->group(function () {
    Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
    Route::get('/pos/exit-verification', [ExitVerificationController::class, 'index'])->name('pos.exit-verification');
    Route::post('/pos/exit-verification/staff', [ExitVerificationController::class, 'verifyStaff'])->name('pos.exit-verification.staff');
    Route::post('/pos/exit-verification/automated', [ExitVerificationController::class, 'verifyAutomated'])->name('pos.exit-verification.automated');
});
