<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Download the ABCF Alex Stokes photo and embed it into the
 * prisoner.body rich-HTML block on Alex Stokes / Alexander
 * Contompasis's profile — the "additional info" section that
 * renders under the auto-bio on his /prisoner/<slug> page.
 *
 * Explicitly does NOT touch prisoner.photo (the profile picture
 * thumbnail). Idempotent — re-runs append the image once and
 * "already present" thereafter.
 */
final class AddAlexStokesBodyImage extends Command {
    protected $signature = 'prisoners:add-alex-stokes-body-image';
    protected $description = "Embed the ABCF photo into Alex Stokes's prisoner.body (additional info), not the profile pic";

    private const IMAGE_URL    = 'https://www.abcf.net/wp-content/uploads/2026/02/alex-stokes.jpg';
    private const STORAGE_PATH = 'prisoners/inline/alex-stokes-abcf.jpg';
    private const WEB_PATH     = '/storage/prisoners/inline/alex-stokes-abcf.jpg';

    public function handle(): int {
        $alex = Prisoner::where(function ($q) {
            $q->where('name', 'like', '%Contompasis%')
              ->orWhere('name', 'like', '%Stokes%')
              ->orWhere('aka', 'like', '%Stokes%')
              ->orWhere('aka', 'like', '%Contompasis%')
              ->orWhere('slug', 'like', '%contompasis%')
              ->orWhere('slug', 'like', '%alex-stokes%');
        })->first();

        if (! $alex) {
            $this->error('Alex Stokes / Alexander Contompasis not found in DB.');
            return self::FAILURE;
        }

        // Download the photo to the public disk (mirrored at /storage/...).
        if (! Storage::disk('public')->exists(self::STORAGE_PATH)) {
            $this->line('fetch '.self::IMAGE_URL);
            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(60)
                    ->get(self::IMAGE_URL);
                if (! $resp->successful() || strlen($resp->body()) < 5000) {
                    $this->error('Image download failed (HTTP '.$resp->status().').');
                    return self::FAILURE;
                }
                Storage::disk('public')->put(self::STORAGE_PATH, $resp->body());
                $this->info('Saved image to '.self::STORAGE_PATH);
            } catch (\Throwable $e) {
                $this->error('Fetch error: '.$e->getMessage());
                return self::FAILURE;
            }
        } else {
            $this->line('image exists at '.self::STORAGE_PATH);
        }

        // If the body already contains this image, do nothing.
        $existingBody = (string) $alex->body;
        if (str_contains($existingBody, self::WEB_PATH)) {
            $this->line('Body already contains the image — no changes.');
            return self::SUCCESS;
        }

        // Build the HTML block to embed.
        $snippet = "\n\n".'<figure style="margin: 24px 0;">'
            .'<img src="'.self::WEB_PATH.'" alt="Alex Stokes / Alexander Contompasis" '
            .'style="max-width: 100%; height: auto; border-radius: 6px;">'
            .'<figcaption style="margin-top: 8px; font-size: 13px; color: rgba(255,255,255,0.55);">'
            .'Alex Stokes (Alexander Contompasis), antifascist political prisoner. '
            .'Photo: <a href="https://www.abcf.net" target="_blank" rel="noopener" style="color: inherit; text-decoration: underline;">Anarchist Black Cross Federation</a>.'
            .'</figcaption>'
            .'</figure>'."\n";

        $alex->body = trim($existingBody) === ''
            ? trim($snippet)
            : rtrim($existingBody)."\n".$snippet;
        $alex->save();
        $this->info('Embedded image into Alex Stokes prisoner.body.');

        return self::SUCCESS;
    }
}
