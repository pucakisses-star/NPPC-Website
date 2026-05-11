<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Support\Facades\Storage;

/**
 * @property string $title
 * @property string $slug
 * @property ?string $description
 * @property string $record_type
 * @property ?string $source_format
 * @property ?string $file
 * @property ?string $thumbnail
 * @property ?int $year
 * @property ?string $date
 * @property ?string $publisher
 * @property ?string $authors
 * @property ?string $collection
 * @property ?string $volume
 * @property ?array $subjects
 * @property bool $is_digitized
 * @property bool $published
 * @property int $sort_order
 * @property ?string $file_url
 * @property ?string $thumbnail_url
 */
final class ArchiveRecord extends Model {
    use HasSlug;

    protected $appends = ['file_url', 'thumbnail_url'];

    protected $casts = [
        'subjects' => 'array',
        'is_digitized' => 'boolean',
        'published' => 'boolean',
        'date' => 'date',
    ];

    public function getFileUrlAttribute(): ?string {
        return $this->file ? Storage::url($this->file) : null;
    }

    public function getThumbnailUrlAttribute(): ?string {
        return $this->thumbnail ? Storage::url($this->thumbnail) : null;
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }

    public function scopeDigitized($query) {
        return $query->where('is_digitized', true);
    }
}
