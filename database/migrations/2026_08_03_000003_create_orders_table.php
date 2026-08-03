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
            $table->string('order_number', 30)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('shop_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();

            $table->enum('status', [
                'pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled',
            ])->default('pending');

            $table->enum('payment_method', ['cod', 'online'])->default('cod');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');

            // Billing breakup
            $table->unsignedInteger('items_count')->default(0);
            $table->decimal('mrp_total', 12, 2)->default(0);       // sum of (price x qty) before any sale discount
            $table->decimal('subtotal', 12, 2)->default(0);        // taxable value (GST excluded)
            $table->decimal('tax_amount', 12, 2)->default(0);      // total GST
            $table->decimal('discount_amount', 12, 2)->default(0); // sale-price savings
            $table->decimal('shipping_charge', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);    // payable

            // Shipping / billing address snapshot
            $table->string('shipping_name')->nullable();
            $table->string('shipping_phone', 20)->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_state', 100)->nullable();
            $table->string('shipping_country', 100)->nullable();
            $table->string('shipping_pincode', 15)->nullable();

            $table->text('notes')->nullable();
            $table->text('cancel_reason')->nullable();

            $table->timestamp('placed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('shop_id')->references('id')->on('shops')->onDelete('set null');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
