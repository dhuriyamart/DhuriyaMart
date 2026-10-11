<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20)->index(); // credit, debit
            $table->unsignedBigInteger('amount_paise');
            $table->bigInteger('balance_after_paise');
            $table->string('reference_type', 50)->index(); // seller_order, settlement, refund, adjustment
            $table->string('reference_id', 100)->nullable()->index();
            $table->text('description');
            $table->timestamps();
        });

        Schema::create('seller_settlements', function (Blueprint $table) {
            $table->id();
            $table->string('settlement_number', 50)->unique();
            $table->foreignId('shop_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('total_sales_paise')->default(0);
            $table->unsignedBigInteger('commission_deducted_paise')->default(0);
            $table->bigInteger('adjustments_paise')->default(0);
            $table->unsignedBigInteger('net_payout_paise')->default(0);
            $table->string('status', 30)->default('draft')->index(); // draft, approved, processing, paid, cancelled
            $table->string('payment_reference', 100)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_settlement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_settlement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('gross_amount_paise');
            $table->unsignedBigInteger('commission_amount_paise');
            $table->unsignedBigInteger('net_amount_paise');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_settlement_items');
        Schema::dropIfExists('seller_settlements');
        Schema::dropIfExists('seller_ledger_entries');
    }
};

