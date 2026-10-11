<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('contact_person', 100)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('gstin', 30)->nullable();
            $table->string('category', 50)->default('general'); // kirana, hardware, general
            $table->string('payment_terms', 50)->default('Net 15');
            $table->decimal('rating', 3, 1)->default(4.5);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number', 50)->unique();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
            $table->foreignId('created_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index(); // draft, pending_approval, approved, ordered, received, cancelled
            $table->unsignedBigInteger('total_cost_paise')->default(0);
            $table->text('notes')->nullable();
            $table->date('expected_delivery_date')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->string('product_name', 200);
            $table->string('variant_name', 150);
            $table->string('sku', 100);
            $table->integer('quantity_ordered');
            $table->integer('quantity_received')->default(0);
            $table->unsignedBigInteger('unit_cost_paise');
            $table->unsignedBigInteger('total_cost_paise');
            $table->string('status', 30)->default('pending'); // pending, received, damaged_returned
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('suppliers');
    }
};

