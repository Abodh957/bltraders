<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_addresses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->string('name');                         // receiver name
            $table->string('phone', 20);
            $table->string('alternate_phone', 20)->nullable();

            $table->string('address_line1', 500);
            $table->string('address_line2', 500)->nullable();
            $table->string('landmark')->nullable();
            $table->string('city', 100);
            $table->string('state', 100);
            $table->string('country', 100)->default('India');
            $table->string('pincode', 15);

            $table->enum('type', ['home', 'shop', 'office', 'other'])->default('shop');
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            // Soft deletes so an order that points at an address the customer
            // later removed still resolves to a real row.
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_addresses');
    }
};
