<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug', 160)->unique();   // terms-and-conditions, privacy-policy, ...
            $table->longText('content')->nullable(); // rich text from the editor
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // The two pages the app always expects to exist.
        $now = now();
        foreach ([
            ['Terms and Conditions', 'terms-and-conditions'],
            ['Privacy Policy',       'privacy-policy'],
        ] as [$title, $slug]) {
            if (!\Illuminate\Support\Facades\DB::table('pages')->where('slug', $slug)->exists()) {
                \Illuminate\Support\Facades\DB::table('pages')->insert([
                    'title' => $title, 'slug' => $slug, 'content' => null,
                    'status' => 1, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
