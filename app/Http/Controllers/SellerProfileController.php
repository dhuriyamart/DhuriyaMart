<?php

namespace App\Http\Controllers;

use App\Models\SellerDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SellerProfileController extends Controller
{
    public function index(Request $request): View
    {
        $shop = $request->user()->shop;
        $profile = $shop ? $shop->profile : null;
        $documents = $shop ? $shop->documents : collect();

        return view('seller.profile', compact('shop', 'profile', 'documents'));
    }

    public function update(Request $request): RedirectResponse
    {
        $shop = $request->user()->shop;

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:100',
            'phone' => 'nullable|string|max:25',
            'description' => 'nullable|string|max:1000',
            'pan_number' => 'required|string|max:20',
            'gstin' => 'nullable|string|max:30',
            'bank_account_name' => 'required|string|max:100',
            'bank_account_number' => 'required|string|max:50',
            'bank_ifsc' => 'required|string|max:20',
            'bank_name' => 'required|string|max:100',
            'pickup_address' => 'required|string|max:500',
            'document_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'document_type' => 'nullable|string|max:50',
        ]);

        $shop->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'description' => $validated['description'],
        ]);

        $profile = $shop->profile()->firstOrCreate(['shop_id' => $shop->id]);
        $profile->update([
            'pan_number' => strtoupper($validated['pan_number']),
            'gstin' => strtoupper($validated['gstin'] ?? ''),
            'bank_account_name' => $validated['bank_account_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_ifsc' => strtoupper($validated['bank_ifsc']),
            'bank_name' => $validated['bank_name'],
            'pickup_address' => $validated['pickup_address'],
        ]);

        if ($request->hasFile('document_file') && ! empty($validated['document_type'])) {
            $path = $request->file('document_file')->store('seller_documents', 'local');
            SellerDocument::create([
                'shop_id' => $shop->id,
                'document_type' => $validated['document_type'],
                'file_path' => $path,
                'original_name' => $request->file('document_file')->getClientOriginalName(),
                'status' => 'pending',
            ]);
        }

        return back()->with('success', 'Shop settings and profile details updated successfully.');
    }
}
