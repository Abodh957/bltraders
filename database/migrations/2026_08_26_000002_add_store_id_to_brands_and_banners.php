<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nullable on purpose: existing rows stay null, and null means "global"
        // — shown in every store. Nothing that already works stops working.
        Schema::table('brands', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('id');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->unsignedBigInteger('store_id')->nullable()->after('id');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });

        Schema::table('banners', function (Blueprint $table) {
            $table->dropForeign(['store_id']);
            $table->dropColumn('store_id');
        });
    }
};
