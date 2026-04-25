<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table): void {
            $table->string('referral_code')->nullable()->after('code')->index();
            $table->timestamp('qualified_at')->nullable()->after('reward_amount');
            $table->timestamp('rejected_at')->nullable()->after('rewarded_at');
            $table->text('admin_note')->nullable()->after('rejected_at');
        });

        Schema::table('wallet_ledgers', function (Blueprint $table): void {
            $table->foreignId('referral_id')->nullable()->after('withdraw_id')->constrained('referrals')->nullOnDelete();
            $table->index(['seller_id', 'referral_id']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_ledgers', function (Blueprint $table): void {
            $table->dropIndex(['seller_id', 'referral_id']);
            $table->dropConstrainedForeignId('referral_id');
        });

        Schema::table('referrals', function (Blueprint $table): void {
            $table->dropColumn(['referral_code', 'qualified_at', 'rejected_at', 'admin_note']);
        });
    }
};
