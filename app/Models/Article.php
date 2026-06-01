<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Spatie\Tags\HasTags;

/**
 * @property string     $title
 * @property string     $slug
 * @property string     $author_id
 * @property string     $body
 * @property string     $intro
 * @property string     $image_url
 * @property string     $image
 * @property string     $url
 * @property string     $category_id
 * @property Carbon     $published_at
 * @property string     $citations_json
 * @property array|null $citations
 * @property Category   $category
 */
final class Article extends Model {
    use HasSlug;
    use HasTags;

    protected $appends = ['url', 'image_url'];
    protected $casts   = [
        'published_at'  => 'datetime',
        'citations_json' => 'array',
    ];

    public static function getBySlug(string $slug): ?self {
        return self::where('slug', $slug)->first();
    }

    public function author(): BelongsTo {
        return $this->belongsTo(Author::class);
    }

    public function getCitationsAttribute(): ?array {
        if (! $this->citations_json) {
            return null;
        }

        $citations = $this->citations_json;

        return collect($citations)
            ->mapWithKeys(function ($item) {
                // Support old Nova Flexible format and new Filament Repeater format
                if (isset($item['attributes'])) {
                    return [$item['attributes']['title'] => $item['attributes']['content']];
                }

                return [$item['title'] ?? '' => $item['content'] ?? ''];
            })
            ->toArray();
    }

    public function getImageUrlAttribute(): string {
        $img = (string) $this->image;
        if ($img === '') {
            return '';
        }
        // If the stored value is already a fully-qualified URL
        // (e.g. a remote fallback from an article-import command),
        // pass it through unchanged instead of prepending /storage/.
        if (str_starts_with($img, 'http://') || str_starts_with($img, 'https://') || str_starts_with($img, '//') || str_starts_with($img, '/')) {
            return $img;
        }
        return Storage::url($img);
    }

    public function getIntroAttribute(): string {
        return substr($this->body, 0, 160);
    }

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class);
    }

    public function getUrlAttribute(): string {
        return '/news/'.$this->slug;
    }
}
