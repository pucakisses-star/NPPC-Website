<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

final class PodcastEpisode extends Model {
    protected $appends = ['cover_url'];

    protected $casts = [
        'published' => 'boolean',
    ];

    public function prisoner(): BelongsTo {
        return $this->belongsTo(Prisoner::class);
    }

    public function getCoverUrlAttribute(): ?string {
        return $this->cover_image ? Storage::url($this->cover_image) : null;
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }
}
