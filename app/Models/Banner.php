<?php

namespace App\Models;

use App\Enums\BannerPlacement;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'placement',
        'image_path',
        'link_url',
        'is_active',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'placement' => BannerPlacement::class,
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query, ?BannerPlacement $placement = null): Builder
    {
        return $query
            ->when($placement, fn (Builder $builder): Builder => $builder->where('placement', $placement))
            ->where('is_active', true)
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $builder): void {
                $builder
                    ->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    public function isActiveNow(): bool
    {
        return $this->is_active
            && ($this->starts_at === null || $this->starts_at->isPast())
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }
}
