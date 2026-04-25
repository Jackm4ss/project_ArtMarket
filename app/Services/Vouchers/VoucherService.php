<?php

namespace App\Services\Vouchers;

use App\Models\Order;
use App\Models\User;
use App\Models\Voucher;
use App\Models\VoucherRedemption;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VoucherService
{
    /**
     * @param array<string, mixed> $checkoutData
     */
    public function quote(?string $code, float $subtotal, ?User $user, array $checkoutData): ?VoucherQuote
    {
        $code = Str::upper(trim((string) $code));

        if ($code === '') {
            return null;
        }

        /** @var Voucher|null $voucher */
        $voucher = Voucher::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (! $voucher) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher tidak valid.',
            ]);
        }

        $this->assertUsable($voucher, $subtotal);

        $identityHash = $this->identityHash($user, $checkoutData);

        if ($voucher->per_user_limit !== null && $voucher->per_user_limit > 0) {
            $usedByIdentity = VoucherRedemption::query()
                ->where('voucher_id', $voucher->id)
                ->where('identity_hash', $identityHash)
                ->count();

            if ($usedByIdentity >= $voucher->per_user_limit) {
                throw ValidationException::withMessages([
                    'voucher_code' => 'Voucher sudah mencapai batas penggunaan untuk akun atau email ini.',
                ]);
            }
        }

        return new VoucherQuote(
            voucher: $voucher,
            discountAmount: $this->calculateDiscount($voucher, $subtotal),
            identityHash: $identityHash,
        );
    }

    /**
     * @param array<string, mixed> $checkoutData
     */
    public function redeem(VoucherQuote $quote, Order $order, ?User $user, array $checkoutData): VoucherRedemption
    {
        $quote->voucher->increment('used_count');

        return VoucherRedemption::query()->create([
            'voucher_id' => $quote->voucher->id,
            'order_id' => $order->id,
            'user_id' => $user?->id,
            'identity_hash' => $quote->identityHash,
            'guest_email' => $user ? null : Str::lower(trim((string) ($checkoutData['email'] ?? ''))),
            'guest_phone' => $user ? null : trim((string) ($checkoutData['phone'] ?? '')),
            'discount_amount' => $quote->discountAmount,
            'redeemed_at' => now(),
        ]);
    }

    private function assertUsable(Voucher $voucher, float $subtotal): void
    {
        if ($voucher->starts_at && $voucher->starts_at->isFuture()) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher belum aktif.',
            ]);
        }

        if ($voucher->ends_at && $voucher->ends_at->isPast()) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Voucher sudah kedaluwarsa.',
            ]);
        }

        if ($voucher->usage_limit && $voucher->used_count >= $voucher->usage_limit) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Kuota voucher sudah habis.',
            ]);
        }

        if ((float) $voucher->minimum_order_amount > $subtotal) {
            throw ValidationException::withMessages([
                'voucher_code' => 'Subtotal belum memenuhi minimum voucher.',
            ]);
        }
    }

    private function calculateDiscount(Voucher $voucher, float $subtotal): float
    {
        if ($voucher->type === 'percent') {
            $discount = round($subtotal * min(100, (float) $voucher->value) / 100, 2);
        } else {
            $discount = round((float) $voucher->value, 2);
        }

        if ($voucher->max_discount_amount !== null) {
            $discount = min($discount, (float) $voucher->max_discount_amount);
        }

        return min($subtotal, max(0, $discount));
    }

    /**
     * @param array<string, mixed> $checkoutData
     */
    private function identityHash(?User $user, array $checkoutData): string
    {
        if ($user) {
            return hash('sha256', 'user:'.$user->id);
        }

        $email = Str::lower(trim((string) ($checkoutData['email'] ?? '')));
        $phone = preg_replace('/\D+/', '', (string) ($checkoutData['phone'] ?? ''));

        return hash('sha256', 'guest:'.$email.'|'.$phone);
    }
}
