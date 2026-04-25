<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->timestamp('cancelled_at')->nullable()->after('completed_at');
            $table->timestamp('stock_released_at')->nullable()->after('cancelled_at');
            $table->timestamp('refund_requested_at')->nullable()->after('stock_released_at');
            $table->timestamp('refunded_at')->nullable()->after('refund_requested_at');
            $table->string('status_before_refund')->nullable()->after('refunded_at');
            $table->text('customer_note')->nullable()->after('status_before_refund');
            $table->text('admin_note')->nullable()->after('customer_note');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'cancelled_at',
                'stock_released_at',
                'refund_requested_at',
                'refunded_at',
                'status_before_refund',
                'customer_note',
                'admin_note',
            ]);
        });
    }
};
