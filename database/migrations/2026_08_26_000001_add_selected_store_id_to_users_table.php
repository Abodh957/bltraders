<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // The store the customer is currently shopping in. Null = not chosen
            // yet, in which case the APIs fall back to showing everything.
            $table->unsignedBigInteger('selected_store_id')->nullable()->after('role');
            $table->foreign('selected_store_id')->references('id')->on('stores')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['selected_store_id']);
            $table->dropColumn('selected_store_id');
        });
    }
};
