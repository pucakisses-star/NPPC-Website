<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Support\Facades\Storage;

final class Product extends Model {
    use HasSlug;

    protected $appends = ['image_url'];

    protected $casts = [
        'price'     => 'decimal:2',
        'featured'  => 'boolean',
        'published' => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }

    public function scopeFeatured($query) {
        return $query->where('featured', true);
    }
}
