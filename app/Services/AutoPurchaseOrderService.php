<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AutoPurchaseOrderService
{
    /**
     * Identify all product variants that have hit or fallen below their low stock threshold.
     */
    public function detectLowStockVariants(?int $customThreshold = null): Collection
    {
        return ProductVariant::with(['product.category', 'product.shop'])
            ->where('is_active', true)
            ->where(function ($query) use ($customThreshold) {
                if ($customThreshold !== null) {
                    $query->where('stock_quantity', '<=', $customThreshold);
                } else {
                    $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
                }
            })
            ->orderBy('stock_quantity', 'asc')
            ->get();
    }

    /**
     * Automatically generate draft Purchase Orders to suppliers for low-stock items.
     */
    public function generateAutoDraftPOs(Store $store, User $creator, int $defaultRestockQty = 50): Collection
    {
        $lowVariants = $this->detectLowStockVariants();

        if ($lowVariants->isEmpty()) {
            return collect();
        }

        return DB::transaction(function () use ($store, $creator, $lowVariants, $defaultRestockQty) {
            $suppliers = Supplier::where('is_active', true)->get();
            $defaultSupplier = $suppliers->first() ?? Supplier::create([
                'name' => 'General Mandi Wholesaler',
                'category' => 'general',
                'payment_terms' => 'Net 15 Days',
            ]);

            // Group variants by category to match optimal supplier
            $createdPOs = collect();
            $grouped = $lowVariants->groupBy(function ($v) {
                $catSlug = strtolower($v->product->category->slug ?? '');
                if (str_contains($catSlug, 'kirana') || str_contains($catSlug, 'food') || str_contains($catSlug, 'grain')) {
                    return 'kirana';
                }
                if (str_contains($catSlug, 'hardware') || str_contains($catSlug, 'tool') || str_contains($catSlug, 'paint')) {
                    return 'hardware';
                }
                return 'general';
            });

            foreach ($grouped as $catType => $variants) {
                $matchedSupplier = $suppliers->firstWhere('category', $catType) ?? $defaultSupplier;

                $po = PurchaseOrder::create([
                    'po_number' => 'PO-' . date('Ym') . '-' . strtoupper(Str::random(5)),
                    'store_id' => $store->id,
                    'supplier_id' => $matchedSupplier->id,
                    'created_by_id' => $creator->id,
                    'approved_by_id' => null,
                    'status' => 'draft',
                    'total_cost_paise' => 0,
                    'notes' => "Auto-replenishment order for {$variants->count()} low-stock {$catType} items.",
                    'expected_delivery_date' => now()->addDays(3)->toDateString(),
                ]);

                $totalCost = 0;
                foreach ($variants as $variant) {
                    // Restock quantity: target minimum threshold * 5, or defaultRestockQty
                    $targetQty = max($defaultRestockQty, $variant->low_stock_threshold * 4);
                    $unitCost = $variant->cost_price_paise ?? (int)($variant->price_paise * 0.82);
                    $lineTotal = $unitCost * $targetQty;
                    $totalCost += $lineTotal;

                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'product_variant_id' => $variant->id,
                        'product_name' => $variant->product->name,
                        'variant_name' => $variant->name,
                        'sku' => $variant->sku,
                        'quantity_ordered' => $targetQty,
                        'quantity_received' => 0,
                        'unit_cost_paise' => $unitCost,
                        'total_cost_paise' => $lineTotal,
                        'status' => 'pending',
                    ]);
                }

                $po->update(['total_cost_paise' => $totalCost]);
                $createdPOs->push($po->load('items'));
            }

            return $createdPOs;
        });
    }

    /**
     * Approve a draft Purchase Order.
     */
    public function approvePO(PurchaseOrder $po, User $approver): PurchaseOrder
    {
        $po->status = 'approved';
        $po->approved_by_id = $approver->id;
        $po->save();

        return $po;
    }

    /**
     * Process Goods Received Note (GRN) / Receiving inspection.
     * Updates stocks, logs movements, handles damaged goods return.
     */
    public function receiveGoods(PurchaseOrder $po, array $receivedQuantities, ?array $damagedQuantities = null): PurchaseOrder
    {
        return DB::transaction(function () use ($po, $receivedQuantities, $damagedQuantities) {
            $allReceived = true;

            foreach ($po->items as $item) {
                $qtyRecv = (int)($receivedQuantities[$item->id] ?? $item->quantity_ordered);
                $qtyDamaged = (int)($damagedQuantities[$item->id] ?? 0);
                $goodQty = max(0, $qtyRecv - $qtyDamaged);

                $item->quantity_received += $goodQty;

                if ($qtyDamaged > 0) {
                    $item->status = 'damaged_returned';
                } elseif ($item->quantity_received >= $item->quantity_ordered) {
                    $item->status = 'received';
                } else {
                    $item->status = 'partially_received';
                    $allReceived = false;
                }
                $item->save();

                // Increment variant stock
                if ($goodQty > 0) {
                    $variant = $item->variant;
                    if ($variant) {
                        $variant->increment('stock_quantity', $goodQty);
                        $variant->refresh();

                        InventoryMovement::create([
                            'product_variant_id' => $variant->id,
                            'user_id' => $po->approved_by_id ?? $po->created_by_id,
                            'type' => 'restock',
                            'quantity_change' => $goodQty,
                            'stock_after' => $variant->stock_quantity,
                            'reference_type' => 'purchase_order',
                            'reference_id' => (string)$po->id,
                            'notes' => "GRN stock intake PO #{$po->po_number} from {$po->supplier->name}",
                        ]);
                    }
                }
            }

            $po->status = $allReceived ? 'received' : 'partially_received';
            $po->received_at = now();
            $po->save();

            return $po->load('items');
        });
    }
}
