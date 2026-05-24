<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('id');
            $table->unsignedBigInteger('category_id')->nullable()->after('store_id');
            $table->unsignedBigInteger('sub_category_id')->nullable()->after('category_id');
            $table->string('slug')->nullable()->after('name');
            $table->text('description')->nullable()->after('slug');
            $table->decimal('price', 10, 2)->default(0)->after('description');
            $table->decimal('sale_price', 10, 2)->nullable()->after('price');
            $table->integer('stock')->default(0)->after('sale_price');
            $table->string('sku')->nullable()->after('stock');
            $table->boolean('is_gst_paid')->default(false)->after('sku');
            $table->decimal('gst_percentage', 5, 2)->nullable()->after('is_gst_paid');
            $table->tinyInteger('status')->default(1)->after('gst_percentage');
            $table->boolean('is_featured')->default(false)->after('status');

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['store_id', 'category_id', 'sub_category_id']);
            $table->dropColumn([
                'store_id', 'category_id', 'sub_category_id', 'slug', 'description',
                'price', 'sale_price', 'stock', 'sku', 'is_gst_paid',
                'gst_percentage', 'status', 'is_featured',
            ]);
        });
    }
};
