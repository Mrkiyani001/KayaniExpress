<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('address_id');
            $table->decimal('grand_total', 10, 2);
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('shipping_cost', 10, 2)->nullable();
            $table->enum('payment_method',['cash_on_delivery','online'])->default('cash_on_delivery');
            $table->enum('payment_status',['pending','paid','failed','refunded'])->default('pending');
            $table->enum('order_status',['pending','confirmed','shipped','delivered','cancelled'])->default('pending');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
