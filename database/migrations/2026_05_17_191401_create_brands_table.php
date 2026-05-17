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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('slug', 160)->unique();
            $table->string('logo', 255)->nullable();
            $table->string('cover_image', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('website_url', 255)->nullable();
            $table->string('meta_title', 160)->nullable();
            $table->text('meta_description')->nullable();
            $table->tinyInteger('status')->default(1)->comment('1=active,0=inactive');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
