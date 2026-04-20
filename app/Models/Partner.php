<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

final class Partner extends Model {
    protected $casts = [
        'published' => 'boolean',
    ];

    public function getLogoUrlAttribute(): ?string {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }
}
