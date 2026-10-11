<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_id', 100)->nullable()->index();
            $table->string('provider', 50)->default('cod'); // cod, razorpay, mock
            $table->unsignedBigInteger('amount_paise');
            $table->string('currency', 10)->default('INR');
            $table->string('status', 30)->default('pending')->index(); // pending, successful, failed, refunded
            $table->string('gateway_order_id', 100)->nullable()->index();
            $table->string('signature')->nullable();
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event_id', 100)->nullable()->unique();
            $table->string('event_type', 100);
            $table->string('provider', 50);
            $table->json('payload')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_order_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('amount_paise');
            $table->text('reason');
            $table->string('status', 30)->default('pending')->index(); // pending, processed, failed
            $table->string('gateway_refund_id', 100)->nullable()->index();
            $table->foreignId('processed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('payment_events');
        Schema::dropIfExists('payments');
    }
};
