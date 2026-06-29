<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Support\Facades\Storage;

final class Product extends Model {
    use HasSlug;

    protected $appends = ['image_url'];

    protected $casts = [
        'price'      => 'decimal:2',
        'featured'   => 'boolean',
        'published'  => 'boolean',
        'gallery'    => 'array',
        'categories' => 'array',
    ];

    public function getImageUrlAttribute(): ?string {
        return $this->image ? Storage::url($this->image) : null;
    }

    /**
     * Every category this product appears under: its primary `category` plus any
     * additional `categories`. Used by the store's category filter pills.
     *
     * @return array<int, string>
     */
    public function getAllCategoriesAttribute(): array {
        return collect([$this->category])
            ->merge($this->categories ?? [])
            ->map(fn ($c) => is_string($c) ? trim($c) : $c)
            ->filter(fn ($c) => filled($c))
            ->unique()
            ->values()
            ->all();
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }

    public function scopeFeatured($query) {
        return $query->where('featured', true);
    }
}
