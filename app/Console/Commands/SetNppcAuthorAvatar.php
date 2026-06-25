<?php

namespace App\Console\Commands;

use App\Models\Author;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * The "National Political Prisoner Coalition" author's avatar is the wide NPPC
 * logo (417x208), which looks wrong when cropped into the square / circular
 * avatar slots (byline, about-box, admin list). Generate a proper square avatar
 * — the logo scaled down to fit, centered on a brand-indigo square — and set it
 * on that author so it fits everywhere. Idempotent; only updates the author if
 * it exists (won't create a duplicate on prod).
 */
final class SetNppcAuthorAvatar extends Command
{
    protected $signature = 'authors:set-nppc-avatar';

    protected $description = 'Give the National Political Prisoner Coalition author a square (fitted) avatar';

    private const AVATAR_PATH = 'authors/national-political-prisoner-coalition.png';

    public function handle(): int
    {
        $logoPath = public_path('logo.png');
        if (! is_file($logoPath)) {
            $this->error('public/logo.png not found.');

            return self::FAILURE;
        }

        // Scale the (wide) logo down to fit, centered on a brand-indigo square.
        $src = imagecreatefrompng($logoPath);
        $sw = imagesx($src);
        $sh = imagesy($src);

        $size = 512;
        $pad = 56;
        $canvas = imagecreatetruecolor($size, $size);
        $bg = imagecolorallocate($canvas, 0x56, 0x60, 0xFE); // #5660fe (brand, matches favicon)
        imagefilledrectangle($canvas, 0, 0, $size, $size, $bg);
        imagealphablending($canvas, true);

        $avail = $size - 2 * $pad;
        $scale = min($avail / $sw, $avail / $sh);
        $dw = (int) round($sw * $scale);
        $dh = (int) round($sh * $scale);
        imagecopyresampled($canvas, $src, (int) (($size - $dw) / 2), (int) (($size - $dh) / 2), 0, 0, $dw, $dh, $sw, $sh);

        ob_start();
        imagepng($canvas);
        $blob = ob_get_clean();
        imagedestroy($canvas);
        imagedestroy($src);

        Storage::disk('public')->put(self::AVATAR_PATH, $blob);
        $this->info('Wrote square avatar to '.self::AVATAR_PATH.' ('.strlen($blob).' bytes)');

        $author = Author::where('name', 'National Political Prisoner Coalition')->first()
            ?? Author::where('name', 'like', '%National Political Prisoner Coalition%')->first();

        if (! $author) {
            $this->warn('No "National Political Prisoner Coalition" author found — avatar generated but not attached.');

            return self::SUCCESS;
        }

        $author->avatar = self::AVATAR_PATH;
        $author->save();
        $this->info("Set avatar on author: {$author->name} (ID: {$author->id})");

        return self::SUCCESS;
    }
}
