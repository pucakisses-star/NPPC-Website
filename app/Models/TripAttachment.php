<?php

namespace App\Models;

use Illuminate\Support\Facades\Storage;

final class TripAttachment extends Model {
    protected $appends = ['file_url', 'preview_url'];

    public function getFileUrlAttribute(): ?string {
        return $this->file_path ? Storage::url($this->file_path) : null;
    }

    public function getPreviewUrlAttribute(): ?string {
        return $this->preview_image ? Storage::url($this->preview_image) : null;
    }

    public function isPdf(): bool {
        return str_ends_with(strtolower($this->file_path ?? ''), '.pdf');
    }

    public function generatePdfPreview(): void {
        if (! $this->isPdf() || ! extension_loaded('imagick')) {
            return;
        }

        try {
            $pdfPath = Storage::disk('public')->path($this->file_path);

            if (! file_exists($pdfPath)) {
                return;
            }

            $imagick = new \Imagick();
            $imagick->setResolution(200, 200);
            $imagick->readImage($pdfPath.'[0]');
            $imagick->setImageFormat('jpg');
            $imagick->setImageCompressionQuality(85);

            $filename = 'trip-attachments/previews/'.pathinfo($this->file_path, PATHINFO_FILENAME).'.jpg';
            Storage::disk('public')->put($filename, $imagick->getImageBlob());
            $imagick->clear();
            $imagick->destroy();

            $this->update(['preview_image' => $filename]);
        } catch (\Exception $e) {
            // ImageMagick not available or PDF couldn't be read
        }
    }
}
