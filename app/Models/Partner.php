<?php

namespace App\Models;

final class Partner extends Model {
    protected $casts = [
        'published' => 'boolean',
    ];

    public function scopePublished($query) {
        return $query->where('published', true);
    }
}
