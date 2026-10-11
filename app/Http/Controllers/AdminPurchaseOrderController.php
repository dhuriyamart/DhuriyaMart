<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Store;
use App\Models\Supplier;
use App\Services\AutoPurchaseOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPurchaseOrderController extends Controller
{
    public function __construct(
        protected AutoPurchaseOrderService $poService
    ) {}

    /**
     * List all Purchase Orders & Low-Stock Alerts.
     */
    public function index(Request $request): View
    {
        $status = $request->input('status');
        $query = PurchaseOrder::with(['supplier', 'store', 'createdBy', 'approvedBy', 'items']);

        if ($status) {
            $query->where('status', $status);
        }

        $purchaseOrders = $query->latest()->paginate(15);
        $lowStockVariants = $this->poService->detectLowStockVariants();
        $suppliers = Supplier::where('is_active', true)->get();

        return view('admin.purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
            'lowStockVariants' => $lowStockVariants,
            'suppliers' => $suppliers,
            'selectedStatus' => $status,
        ]);
    }

    /**
     * Auto-generate draft Purchase Orders for low-stock items.
     */
    public function autoGenerate(Request $request): RedirectResponse
    {
        $store = Store::where('is_active', true)->first()
            ?? Store::firstOrCreate(
                ['code' => 'STORE-MAU-01'],
                [
                    'name' => 'Dhuriya Mart - Flagship Village Store #01',
                    'village_name' => 'Ranipur / Mau Dehat',
                    'district' => 'Mau',
                    'state' => 'Uttar Pradesh',
                    'pincode' => '275101',
                    'is_active' => true,
                ]
            );
        $user = $request->user();

        $generated = $this->poService->generateAutoDraftPOs($store, $user);

        if ($generated->isEmpty()) {
            return back()->with('info', 'All items currently meet or exceed minimum stock thresholds. No restock needed.');
        }

        return back()->with('success', "Generated {$generated->count()} replenishment Purchase Order(s) based on supplier categories & inventory triggers.");
    }

    /**
     * View PO details and supplier price comparison.
     */
    public function show(int $id): View
    {
        $po = PurchaseOrder::with(['supplier', 'store', 'createdBy', 'approvedBy', 'items.variant.product'])
            ->findOrFail($id);

        $suppliers = Supplier::where('is_active', true)->get();

        return view('admin.purchase-orders.show', [
            'po' => $po,
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Approve draft Purchase Order.
     */
    public function approve(int $id, Request $request): RedirectResponse
    {
        $po = PurchaseOrder::findOrFail($id);
        $this->poService->approvePO($po, $request->user());

        return back()->with('success', "Purchase Order #{$po->po_number} approved! Ready to dispatch to supplier {$po->supplier->name}.");
    }

    /**
     * Goods Received Note (GRN) / Receiving inspection.
     */
    public function receive(int $id, Request $request): RedirectResponse
    {
        $po = PurchaseOrder::with('items.variant')->findOrFail($id);

        $received = $request->input('received_quantities', []);
        $damaged = $request->input('damaged_quantities', []);

        $this->poService->receiveGoods($po, $received, $damaged);

        return back()->with('success', 'Goods Received Note processed! Live inventory updated for all received units.');
    }
}
