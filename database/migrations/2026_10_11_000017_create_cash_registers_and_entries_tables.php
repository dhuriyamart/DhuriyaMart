<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('opened_by_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('closed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('register_date')->index();
            $table->unsignedBigInteger('opening_float_paise')->default(0);
            $table->unsignedBigInteger('cash_sales_paise')->default(0);
            $table->unsignedBigInteger('cash_in_paise')->default(0);
            $table->unsignedBigInteger('cash_out_paise')->default(0);
            $table->unsignedBigInteger('expected_cash_paise')->default(0);
            $table->unsignedBigInteger('closing_actual_paise')->nullable();
            $table->bigInteger('difference_paise')->default(0); // can be negative (shortage) or positive (excess)
            $table->string('status', 30)->default('open')->index(); // open, closed, reconciled
            $table->text('notes')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('cash_register_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cash_register_id')->constrained('cash_registers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type', 30); // opening_float, sale, cash_in, cash_out, customer_deposit
            $table->unsignedBigInteger('amount_paise');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_register_entries');
        Schema::dropIfExists('cash_registers');
    }
};
