<?php

namespace App\Services;

use App\Models\SellerOrder;
use App\Models\Setting;
use App\Models\Shipment;

class ShippingService
{
    /**
     * Calculate delivery shipping fee based on platform threshold.
     */
    public function calculateShippingFee(int $subtotalPaise): int
    {
        $thresholdPaise = (int) Setting::get('free_shipping_threshold_paise', 49900); // default ₹499
        $standardFeePaise = (int) Setting::get('standard_shipping_fee_paise', 4900);  // default ₹49

        if ($subtotalPaise >= $thresholdPaise || $subtotalPaise <= 0) {
            return 0;
        }

        return $standardFeePaise;
    }

    /**
     * Create shipment dispatch record for a seller order.
     */
    public function createShipment(
        SellerOrder $sellerOrder,
        string $courierName,
        ?string $trackingNumber = null,
        ?string $trackingUrl = null,
        ?string $notes = null
    ): Shipment {
        return Shipment::updateOrCreate(
            ['seller_order_id' => $sellerOrder->id],
            [
                'courier_name' => $courierName,
                'tracking_number' => $trackingNumber,
                'tracking_url' => $trackingUrl,
                'status' => 'picked_up',
                'dispatched_at' => now(),
                'notes' => $notes,
            ]
        );
    }

    /**
     * Update shipment delivery state.
     */
    public function updateShipmentStatus(Shipment $shipment, string $status, ?string $notes = null): Shipment
    {
        $data = ['status' => $status];
        if ($notes) {
            $data['notes'] = $notes;
        }
        if ($status === 'delivered') {
            $data['delivered_at'] = now();
        }

        $shipment->update($data);
        return $shipment;
    }
}

