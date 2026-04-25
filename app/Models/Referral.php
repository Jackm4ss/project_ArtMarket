<?php

namespace App\Models;

use App\Enums\ReferralStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_id',
        'code',
        'referral_code',
        'status',
        'reward_amount',
        'qualified_at',
        'rewarded_at',
        'rejected_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => ReferralStatus::class,
            'reward_amount' => 'decimal:2',
            'qualified_at' => 'datetime',
            'rewarded_at' => 'datetime',
            'rejected_at' => 'datetime',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referred(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_id');
    }
}
