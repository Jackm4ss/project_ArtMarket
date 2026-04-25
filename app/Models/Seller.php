<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Seller extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'user_id',
        'store_name',
        'slug',
        'bio',
        'status',
        'location',
        'phone',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'rating_average',
        'rating_count',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'rating_average' => 'decimal:2',
            'verified_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('store_name')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function walletLedgers(): HasMany
    {
        return $this->hasMany(WalletLedger::class);
    }
}
