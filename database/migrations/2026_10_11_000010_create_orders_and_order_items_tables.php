<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30)->default('pending')->index(); // pending, paid, processing, completed, cancelled, refunded
            $table->string('payment_status', 30)->default('pending')->index(); // pending, paid, failed, refunded
            $table->string('payment_method', 30)->default('cod'); // cod, razorpay, mock
            $table->string('currency', 10)->default('INR');
            $table->unsignedBigInteger('subtotal_paise');
            $table->unsignedBigInteger('discount_paise')->default(0);
            $table->unsignedBigInteger('shipping_fee_paise')->default(0);
            $table->unsignedBigInteger('tax_paise')->default(0);
            $table->unsignedBigInteger('total_amount_paise');
            $table->string('coupon_code', 50)->nullable();
            $table->text('customer_notes')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->default('shipping'); // shipping, billing
            $table->string('name', 100);
            $table->string('phone', 20);
            $table->string('email', 100)->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('landmark')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('postal_code', 20)->index();
            $table->string('country', 50)->default('India');
            $table->timestamps();
        });

        Schema::create('seller_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('seller_order_number', 60)->unique();
            $table->string('status', 30)->default('pending')->index(); // pending, confirmed, processing, dispatched, delivered, cancelled, returned
            $table->unsignedBigInteger('subtotal_paise');
            $table->unsignedBigInteger('shipping_fee_paise')->default(0);
            $table->unsignedBigInteger('tax_paise')->default(0);
            $table->unsignedBigInteger('discount_paise')->default(0);
            $table->unsignedBigInteger('total_amount_paise');
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->unsignedBigInteger('commission_amount_paise')->default(0);
            $table->unsignedBigInteger('net_seller_amount_paise')->default(0);
            $table->string('payout_status', 30)->default('unpaid')->index(); // unpaid, in_settlement, settled
            $table->timestamp('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('product_name', 200);
            $table->string('variant_name', 150);
            $table->string('sku', 100);
            $table->unsignedBigInteger('price_paise');
            $table->integer('quantity');
            $table->unsignedBigInteger('subtotal_paise');
            $table->unsignedBigInteger('tax_paise')->default(0);
            $table->unsignedBigInteger('total_paise');
            $table->decimal('commission_rate', 5, 2)->default(10.00);
            $table->unsignedBigInteger('commission_amount_paise')->default(0);
            $table->timestamps();
        });

        Schema::create('shipments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->string('courier_name', 100);
            $table->string('tracking_number', 100)->nullable()->index();
            $table->string('tracking_url')->nullable();
            $table->string('status', 30)->default('created')->index(); // created, picked_up, in_transit, out_for_delivery, delivered, returned
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('order_status_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('shipments');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('seller_orders');
        Schema::dropIfExists('order_addresses');
        Schema::dropIfExists('orders');
    }
};
