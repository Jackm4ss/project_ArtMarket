<?php

namespace App\Models;

use App\Enums\WithdrawStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'amount',
        'fee',
        'status',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'admin_note',
        'requested_at',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'fee' => 'decimal:2',
            'status' => WithdrawStatus::class,
            'requested_at' => 'datetime',
            'processed_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }
}
