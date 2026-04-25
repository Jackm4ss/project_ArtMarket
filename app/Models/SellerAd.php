<?php

namespace App\Models;

use App\Enums\AdsPlacement;
use App\Enums\AdsStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SellerAd extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'product_id',
        'title',
        'placement',
        'status',
        'budget',
        'starts_at',
        'ends_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'placement' => AdsPlacement::class,
            'status' => AdsStatus::class,
            'budget' => 'decimal:2',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
