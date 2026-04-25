<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vouchers', function (Blueprint $table): void {
            $table->decimal('max_discount_amount', 15, 2)->nullable()->after('minimum_order_amount');
            $table->unsignedInteger('per_user_limit')->nullable()->after('usage_limit');
        });

        Schema::create('voucher_redemptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('voucher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('identity_hash', 80);
            $table->string('guest_email')->nullable();
            $table->string('guest_phone')->nullable();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->timestamp('redeemed_at');
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['voucher_id', 'identity_hash']);
            $table->index(['user_id', 'voucher_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voucher_redemptions');

        Schema::table('vouchers', function (Blueprint $table): void {
            $table->dropColumn(['max_discount_amount', 'per_user_limit']);
        });
    }
};
