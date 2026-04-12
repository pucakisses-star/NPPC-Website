<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Support\Facades\Storage;

final class Event extends Model {
    use HasSlug;

    protected $appends = ['image_url'];

    protected $casts = [
        'event_date' => 'date',
        'published'  => 'boolean',
    ];

    public function getImageUrlAttribute(): ?string {
        if (! $this->image) {
            return null;
        }

        return Storage::url($this->image);
    }

    public function scopeUpcoming($query) {
        return $query->where('event_date', '>=', now()->startOfDay())->orderBy('event_date');
    }

    public function scopePast($query) {
        return $query->where('event_date', '<', now()->startOfDay())->orderByDesc('event_date');
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }
}
