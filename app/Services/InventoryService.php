<?php

namespace App\Services;

use App\Models\InventoryMovement;
use App\Models\ProductVariant;
use App\Models\Shop;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InventoryService
{
    /**
     * Check if requested quantity is available in stock.
     */
    public function checkStock(ProductVariant $variant, int $quantity): bool
    {
        return $variant->stock_quantity >= $quantity && $quantity > 0;
    }

    /**
     * Deduct stock for a purchase or reservation, preventing negative stock.
     */
    public function deductStock(ProductVariant $variant, int $quantity, string $reason = 'sale', ?string $referenceId = null, ?User $user = null): InventoryMovement
    {
        if ($quantity <= 0) {
            throw new RuntimeException("Quantity to deduct must be greater than zero.");
        }

        return DB::transaction(function () use ($variant, $quantity, $reason, $referenceId, $user) {
            // Lock the variant record for concurrency safety
            $lockedVariant = ProductVariant::where('id', $variant->id)->lockForUpdate()->firstOrFail();

            if ($lockedVariant->stock_quantity < $quantity) {
                throw new RuntimeException(
                    "Insufficient stock for SKU: {$lockedVariant->sku}. Available: {$lockedVariant->stock_quantity}, Requested: {$quantity}"
                );
            }

            $stockAfter = $lockedVariant->stock_quantity - $quantity;
            $lockedVariant->update(['stock_quantity' => $stockAfter]);

            return InventoryMovement::create([
                'product_variant_id' => $lockedVariant->id,
                'user_id' => $user?->id ?? auth()->id(),
                'type' => $reason,
                'quantity_change' => -$quantity,
                'stock_after' => $stockAfter,
                'reference_type' => 'order',
                'reference_id' => $referenceId,
                'notes' => "Deducted {$quantity} unit(s) for {$reason}",
            ]);
        });
    }

    /**
     * Restore stock upon order cancellation or return.
     */
    public function restoreStock(ProductVariant $variant, int $quantity, string $reason = 'return', ?string $referenceId = null, ?User $user = null): InventoryMovement
    {
        if ($quantity <= 0) {
            throw new RuntimeException("Quantity to restore must be greater than zero.");
        }

        return DB::transaction(function () use ($variant, $quantity, $reason, $referenceId, $user) {
            $lockedVariant = ProductVariant::where('id', $variant->id)->lockForUpdate()->firstOrFail();

            $stockAfter = $lockedVariant->stock_quantity + $quantity;
            $lockedVariant->update(['stock_quantity' => $stockAfter]);

            return InventoryMovement::create([
                'product_variant_id' => $lockedVariant->id,
                'user_id' => $user?->id ?? auth()->id(),
                'type' => $reason,
                'quantity_change' => $quantity,
                'stock_after' => $stockAfter,
                'reference_type' => 'order_return',
                'reference_id' => $referenceId,
                'notes' => "Restored {$quantity} unit(s) due to {$reason}",
            ]);
        });
    }

    /**
     * Manually adjust stock by seller or admin.
     */
    public function adjustStock(ProductVariant $variant, int $newQuantity, string $reason = 'manual_adjustment', ?User $user = null): InventoryMovement
    {
        if ($newQuantity < 0) {
            throw new RuntimeException("Stock quantity cannot be negative.");
        }

        return DB::transaction(function () use ($variant, $newQuantity, $reason, $user) {
            $lockedVariant = ProductVariant::where('id', $variant->id)->lockForUpdate()->firstOrFail();

            $diff = $newQuantity - $lockedVariant->stock_quantity;
            $lockedVariant->update(['stock_quantity' => $newQuantity]);

            return InventoryMovement::create([
                'product_variant_id' => $lockedVariant->id,
                'user_id' => $user?->id ?? auth()->id(),
                'type' => $diff >= 0 ? 'restock' : 'adjustment',
                'quantity_change' => $diff,
                'stock_after' => $newQuantity,
                'reference_type' => 'manual',
                'reference_id' => null,
                'notes' => "Manual stock adjustment: {$reason}",
            ]);
        });
    }

    /**
     * Retrieve all variants at or below their low-stock threshold.
     */
    public function getLowStockVariants(?Shop $shop = null): Collection
    {
        $query = ProductVariant::with(['product.shop'])
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->where('is_active', true);

        if ($shop) {
            $query->whereHas('product', fn($q) => $q->where('shop_id', $shop->id));
        }

        return $query->get();
    }
}

