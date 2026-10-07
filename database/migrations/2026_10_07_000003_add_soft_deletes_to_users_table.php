<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'deleted_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Account deletion is a soft delete: the row stays so past orders
            // keep resolving, but the user disappears from every query.
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('users', 'deleted_at')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
