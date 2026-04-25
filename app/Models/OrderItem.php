<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_id',
        'product_id',
        'product_title',
        'product_snapshot',
        'quantity',
        'unit_price',
        'subtotal',
        'commission_amount',
        'status',
        'courier',
        'tracking_number',
        'shipped_at',
    ];

    protected function casts(): array
    {
        return [
            'product_snapshot' => 'array',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'commission_amount' => 'decimal:2',
            'status' => OrderStatus::class,
            'shipped_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(ProductReview::class);
    }
}
