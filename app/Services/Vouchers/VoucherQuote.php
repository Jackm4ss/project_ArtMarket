<?php

namespace App\Services\Vouchers;

use App\Models\Voucher;

readonly class VoucherQuote
{
    public function __construct(
        public Voucher $voucher,
        public float $discountAmount,
        public string $identityHash,
    ) {
    }
}
