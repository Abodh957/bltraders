<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Which saved address the order was placed against. Nullable: older
            // orders predate addresses, and orders can still be placed with a
            // typed-in or shop address. The shipping_* columns keep the snapshot
            // either way, so editing/deleting an address never rewrites an order.
            $table->unsignedBigInteger('delivery_address_id')->nullable()->after('store_id');
            $table->foreign('delivery_address_id')->references('id')->on('delivery_addresses')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['delivery_address_id']);
            $table->dropColumn('delivery_address_id');
        });
    }
};
