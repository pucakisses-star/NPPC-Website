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
        return self::resolveAssetUrl($this->file);
    }

    public function getThumbnailUrlAttribute(): ?string {
        return self::resolveAssetUrl($this->thumbnail);
    }

    private static function resolveAssetUrl(?string $path): ?string {
        if ($path === null || $path === '') {
            return null;
        }
        // Absolute URL or already-public path → return as-is so we can
        // store files that live under /public/ (e.g. /pdfs/4strugglemag/X.pdf)
        // without round-tripping through Storage::url().
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::url($path);
    }

    public function scopePublished($query) {
        return $query->where('published', true);
    }

    public function scopeDigitized($query) {
        return $query->where('is_digitized', true);
    }
}
