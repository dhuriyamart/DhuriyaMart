<?php

namespace App\Http\Controllers;

use App\Models\SellerProfile;
use App\Models\Shop;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected CartService $cartService
    ) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->status === 'suspended') {
                Auth::logout();

                return back()->withErrors(['email' => 'Your account has been suspended by administration.']);
            }

            // Merge guest cart items into authenticated user cart
            $this->cartService->mergeGuestCartIntoUserCart($request->session()->getId(), $user);

            // Role-based redirect
            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
            }
            if ($user->isSeller()) {
                return redirect()->intended(route('seller.dashboard'));
            }

            return redirect()->intended(route('account.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $user->assignRole('customer');
        Auth::login($user);

        $this->cartService->mergeGuestCartIntoUserCart($request->session()->getId(), $user);

        return redirect()->route('account.dashboard')->with('success', 'Welcome to DhuriyaMart! Your account was created successfully.');
    }

    public function showSellerRegister(): View
    {
        return view('auth.seller-register');
    }

    public function registerSeller(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'shop_name' => 'required|string|max:150',
            'legal_name' => 'nullable|string|max:150',
            'pan_number' => 'required|string|max:20',
            'gstin' => 'nullable|string|max:30',
            'bank_account_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_ifsc' => 'required|string|max:20',
            'bank_name' => 'required|string|max:100',
            'pickup_address' => 'required|string|max:500',
            'description' => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'status' => 'active',
        ]);

        $user->assignRole('seller');

        $shopSlug = Str::slug($validated['shop_name']);
        if (Shop::where('slug', $shopSlug)->exists()) {
            $shopSlug .= '-'.rand(100, 999);
        }

        $shop = Shop::create([
            'user_id' => $user->id,
            'name' => $validated['shop_name'],
            'slug' => $shopSlug,
            'legal_name' => $validated['legal_name'] ?? $validated['shop_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'description' => $validated['description'] ?? null,
            'status' => 'pending', // Pending admin verification
            'commission_rate' => 10.00,
        ]);

        SellerProfile::create([
            'shop_id' => $shop->id,
            'pan_number' => strtoupper($validated['pan_number']),
            'gstin' => strtoupper($validated['gstin'] ?? ''),
            'bank_account_name' => $validated['bank_account_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_ifsc' => strtoupper($validated['bank_ifsc']),
            'bank_name' => $validated['bank_name'],
            'pickup_address' => $validated['pickup_address'],
        ]);

        Auth::login($user);

        return redirect()->route('seller.dashboard')->with(
            'success',
            'Your merchant account and shop have been created! Our admin team is reviewing your KYC documents.'
        );
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('info', 'You have been logged out.');
    }
}
