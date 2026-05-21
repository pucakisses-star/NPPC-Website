<?php

namespace App\Console\Commands;

use App\Models\Partner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds Freedom For All Americans (freedomforallamericans.org) as a
 * partner. Downloads the logo from the org's site into the public
 * disk at runtime so the binary isn't tracked in git.
 *
 * Idempotent — matches by name.
 */
final class AddFfaaPartner extends Command {
    protected $signature = 'archive:add-ffaa-partner';
    protected $description = 'Add Freedom For All Americans as a partner';

    private const NAME = 'Freedom For All Americans';
    private const LOGO_SRC = 'https://freedomforallamericans.org/wp-content/uploads/2024/05/freedomforallamericans.org-logo-e1716444672625.png';
    private const LOGO_PATH = 'partners/freedom-for-all-americans-logo.png';

    public function handle(): int {
        // Always re-render so the white-repaint applies even when the
        // file already exists from an earlier (pre-white) run.
        $bytes = @file_get_contents(self::LOGO_SRC);
        if ($bytes === false) {
            $this->error('Failed to download logo from '.self::LOGO_SRC);
            return self::FAILURE;
        }
        $whiteBytes = $this->repaintWhite($bytes);
        if ($whiteBytes === null) {
            $this->error('Failed to repaint logo white.');
            return self::FAILURE;
        }
        Storage::disk('public')->put(self::LOGO_PATH, $whiteBytes);
        $this->info('Wrote white logo to '.self::LOGO_PATH);

        $payload = [
            'logo' => self::LOGO_PATH,
            'url' => 'https://freedomforallamericans.org',
            'description' => null,
            'category' => null,
            'published' => true,
            'sort_order' => 0,
        ];

        $existing = Partner::query()
            ->whereRaw('TRIM(name) = ?', [self::NAME])
            ->first();
        if ($existing) {
            $existing->fill($payload);
            $existing->name = self::NAME;
            $existing->save();
            $this->info('Updated existing partner: '.self::NAME);
        } else {
            Partner::create(['name' => self::NAME] + $payload);
            $this->info('Created partner: '.self::NAME);
        }

        return self::SUCCESS;
    }

    /**
     * Recolor every non-transparent pixel of $bytes to white, preserving
     * alpha. Returns a PNG byte string, or null on failure. Accepts any
     * image format GD can read (PNG, WebP, JPEG, GIF).
     */
    private function repaintWhite(string $bytes): ?string {
        $src = @imagecreatefromstring($bytes);
        if ($src === false) {
            return null;
        }
        imagepalettetotruecolor($src);
        imagealphablending($src, false);
        imagesavealpha($src, true);

        $w = imagesx($src);
        $h = imagesy($src);
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $rgba = imagecolorat($src, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                if ($alpha < 127) {
                    $color = imagecolorallocatealpha($src, 255, 255, 255, $alpha);
                    imagesetpixel($src, $x, $y, $color);
                }
            }
        }

        ob_start();
        imagepng($src);
        $png = ob_get_clean();
        imagedestroy($src);

        return $png ?: null;
    }
}
