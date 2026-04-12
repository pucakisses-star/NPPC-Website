<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

final class Petition extends Model {
    use HasSlug;

    protected $appends = ['image_url'];

    protected $casts = ['published' => 'boolean'];

    public function signatures(): HasMany {
        return $this->hasMany(PetitionSignature::class);
    }

    public function getImageUrlAttribute(): ?string {
        return $this->image ? Storage::url($this->image) : null;
    }

    public function getSignatureCountAttribute(): int {
        return $this->signatures_count ?? $this->signatures()->count();
    }

    public function getProgressPercentAttribute(): float {
        if (! $this->signature_goal || $this->signature_goal <= 0) {
            return 0;
        }

        return min(100, round(($this->signature_count / $this->signature_goal) * 100, 1));
    }
}
