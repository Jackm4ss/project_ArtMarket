<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Tags\HasTags;

class Product extends Model implements HasMedia
{
    use HasFactory, HasSlug, HasTags, InteractsWithMedia, Searchable, SoftDeletes;

    protected $fillable = [
        'seller_id',
        'category_id',
        'sku',
        'title',
        'slug',
        'excerpt',
        'description',
        'price',
        'compare_at_price',
        'stock',
        'status',
        'product_type',
        'material',
        'dimensions',
        'weight_gram',
        'location',
        'preorder_days',
        'is_featured',
        'views_count',
        'sold_count',
        'rating_average',
        'rating_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_at_price' => 'decimal:2',
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
            'rating_average' => 'decimal:2',
            'published_at' => 'datetime',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Published->value);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'description' => $this->description,
            'material' => $this->material,
            'location' => $this->location,
        ];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }
}
