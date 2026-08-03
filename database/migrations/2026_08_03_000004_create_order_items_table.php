<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('product_id')->nullable();
            $table->unsignedBigInteger('color_id')->nullable();

            // Snapshot of the product at the moment the order was placed.
            $table->string('product_name');
            $table->string('product_sku')->nullable();
            $table->string('product_image')->nullable();
            $table->string('color_name', 100)->nullable();

            $table->decimal('mrp', 10, 2)->default(0);        // products.price
            $table->decimal('unit_price', 10, 2)->default(0); // sale_price ?? price
            $table->unsignedInteger('quantity')->default(1);

            $table->enum('tax_type', ['inclusive', 'exclusive', 'none'])->default('none');
            $table->decimal('gst_percentage', 5, 2)->default(0);
            $table->decimal('taxable_amount', 12, 2)->default(0);
            $table->decimal('gst_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0); // payable for this line

            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('color_id')->references('id')->on('colors')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
