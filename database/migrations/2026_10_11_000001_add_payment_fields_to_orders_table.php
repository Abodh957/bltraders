<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // How much of total_amount has actually been received so far.
            if (!Schema::hasColumn('orders', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('orders', 'payment_note')) {
                $table->string('payment_note')->nullable()->after('paid_amount');
            }
        });

        // These were enums limited to cod/online and pending/paid/failed/refunded.
        // Widening them to plain strings lets the admin record cash, UPI, bank
        // transfer and a "partial" payment state without another migration.
        // Existing values stay exactly as they are.
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method', 20)->default('cod')->change();
            $table->string('payment_status', 20)->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'payment_note')) {
                $table->dropColumn('payment_note');
            }
            if (Schema::hasColumn('orders', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
        });
    }
};
