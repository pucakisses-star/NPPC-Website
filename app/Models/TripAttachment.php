<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

final class TripAttachment extends Model {
    protected $appends = ['file_url'];

    public function getFileUrlAttribute(): ?string {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }
}
