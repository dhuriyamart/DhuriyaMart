<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Payment;
use App\Models\PaymentEvent;
use App\Models\Refund;
use App\Services\PaymentService;
use App\Support\Money;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AdminPaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    public function index(Request $request): View
    {
        $payments = Payment::with(['order.user', 'refunds'])
            ->latest()
            ->paginate(15, ['*'], 'payments_page');

        $refunds = Refund::with(['payment', 'order', 'processedBy'])
            ->latest()
            ->paginate(10, ['*'], 'refunds_page');

        $events = PaymentEvent::latest()->take(20)->get();

        return view('admin.payments.index', compact('payments', 'refunds', 'events'));
    }

    public function refund(Request $request, int $id): RedirectResponse
    {
        $payment = Payment::findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'reason' => 'required|string|max:500',
        ]);

        $amountPaise = Money::toPaise($validated['amount']);

        try {
            $refund = $this->paymentService->processRefund(
                $payment,
                $amountPaise,
                $validated['reason'],
                $request->user()
            );

            AuditLog::record('payment_refund_issued', $payment, null, [
                'refund_id' => $refund->id,
                'amount_paise' => $amountPaise,
                'reason' => $validated['reason'],
            ]);

            return back()->with('success', "Refund of ₹{$validated['amount']} processed successfully.");
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}

