<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name', 150);
            $table->string('village_name', 100)->index();
            $table->string('district', 100);
            $table->string('state', 100)->default('Uttar Pradesh');
            $table->string('pincode', 20);
            $table->string('phone', 30)->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('customer_id', 50)->nullable()->unique()->after('id');
            $table->string('qr_token', 100)->nullable()->unique()->after('customer_id');
            $table->unsignedBigInteger('wallet_balance_paise')->default(0)->after('status');
            $table->string('village_name', 100)->nullable()->after('phone');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('store_id')->nullable()->after('user_id')->constrained('stores')->nullOnDelete();
            $table->string('order_type', 30)->default('online')->after('status'); // online, pos_self_service, pos_staff_assisted
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn(['store_id', 'order_type']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['customer_id', 'qr_token', 'wallet_balance_paise', 'village_name']);
        });

        Schema::dropIfExists('stores');
    }
};

