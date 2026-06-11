<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Auto-generates a URL slug on create for models that have a unique `slug`
 * column.
 *
 * The slug is derived from `title`, falling back to `name` so models keyed on
 * a name column (e.g. Product) slug correctly rather than producing an empty
 * string. The result is made unique against the (UNIQUE) slug column by
 * appending -2, -3, … on collision, so two records with the same title don't
 * violate the constraint.
 *
 * @property string $slug
 *
 * @mixin Model
 */
trait HasSlug {
    public static function bootHasSlug(): void {
        static::creating(function (Model $model) {
            if (! $model->slug) {
                /** @var Model&\App\Models\Concerns\HasSlug $model */
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    /**
     * The text the slug is derived from. Defaults to `title` with a fallback
     * to `name`. Override in a model to use a different source.
     */
    protected function slugSource(): string {
        return (string) ($this->title ?? $this->name ?? '');
    }

    /**
     * Build a slug that is unique against the table's `slug` column. Runs only
     * on create (the row isn't persisted yet), so any existing match is a real
     * collision and gets a -2/-3/… suffix.
     */
    protected function generateUniqueSlug(): string {
        $base = Str::slug($this->slugSource());

        if ($base === '') {
            // No usable source text — use a random token rather than insert an
            // empty slug that would collide with the next row.
            $base = 'item-'.Str::lower(Str::random(6));
        }

        $slug = $base;
        $i = 2;
        while (static::query()->withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
