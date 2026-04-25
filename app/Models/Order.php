<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'invoice',
        'guest_name',
        'guest_email',
        'guest_phone',
        'status',
        'payment_status',
        'subtotal',
        'discount_total',
        'shipping_total',
        'commission_total',
        'grand_total',
        'currency',
        'idempotency_key',
        'shipping_snapshot',
        'completed_at',
        'cancelled_at',
        'stock_released_at',
        'refund_requested_at',
        'refunded_at',
        'status_before_refund',
        'customer_note',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'subtotal' => 'decimal:2',
            'discount_total' => 'decimal:2',
            'shipping_total' => 'decimal:2',
            'commission_total' => 'decimal:2',
            'grand_total' => 'decimal:2',
            'shipping_snapshot' => 'array',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'stock_released_at' => 'datetime',
            'refund_requested_at' => 'datetime',
            'refunded_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'invoice';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function voucherRedemption(): HasOne
    {
        return $this->hasOne(VoucherRedemption::class);
    }
}
