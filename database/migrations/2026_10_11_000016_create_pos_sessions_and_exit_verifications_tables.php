<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_code', 60)->unique();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('cashier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mode', 30)->default('staff_assisted'); // self_service, staff_assisted
            $table->string('status', 30)->default('active')->index(); // active, billed, completed, cancelled
            $table->json('cart_items')->nullable();
            $table->unsignedBigInteger('subtotal_paise')->default(0);
            $table->unsignedBigInteger('discount_paise')->default(0);
            $table->unsignedBigInteger('tax_paise')->default(0);
            $table->unsignedBigInteger('total_amount_paise')->default(0);
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('payment_method', 30)->nullable(); // cash, upi, wallet
            $table->unsignedBigInteger('cash_tendered_paise')->nullable();
            $table->unsignedBigInteger('change_returned_paise')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('exit_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('verification_code', 60)->unique();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->foreignId('pos_session_id')->nullable()->constrained('pos_sessions')->nullOnDelete();
            $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('mode', 30)->default('staff_assisted'); // staff_assisted (Mode A), automated (Mode B)
            $table->string('status', 30)->default('passed')->index(); // passed, discrepancy_flagged, resolved
            $table->integer('total_billed_items')->default(0);
            $table->integer('total_scanned_items')->default(0);
            $table->json('scanned_items')->nullable();
            $table->json('discrepancy_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exit_verifications');
        Schema::dropIfExists('pos_sessions');
    }
};
