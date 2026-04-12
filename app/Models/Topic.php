<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

final class Topic extends Model {
    use HasSlug;

    protected $appends = ['image_url'];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function parent(): BelongsTo {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function getImageUrlAttribute(): ?string {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function scopeRoots($query) {
        return $query->whereNull('parent_id');
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }
}
