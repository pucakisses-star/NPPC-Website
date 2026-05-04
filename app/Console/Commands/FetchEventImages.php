<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FetchEventImages extends Command
{
    protected $signature = 'events:fetch-images {--overwrite : Re-download even when an image is already set} {--limit= : Stop after processing N events}';
    protected $description = 'For every event with an event_url and no image, fetch the source page, extract its og:image, download it to storage/app/public/events/, and attach it.';

    public function handle(): int
    {
        $overwrite = (bool) $this->option('overwrite');
        $limit     = $this->option('limit') ? (int) $this->option('limit') : null;

        $events = Event::query()
            ->whereNotNull('event_url')
            ->where('event_url', '!=', '')
            ->when(! $overwrite, fn ($q) => $q->where(fn ($q2) => $q2->whereNull('image')->orWhere('image', '')))
            ->orderBy('event_date', 'desc')
            ->get();

        if ($limit) $events = $events->take($limit);

        $this->info("Looking for images on " . $events->count() . " events.");
        Storage::disk('public')->makeDirectory('events');

        $downloaded = 0;
        $skipped    = 0;
        $errors     = 0;

        foreach ($events as $event) {
            $url = $event->event_url;
            $this->line("--- {$event->title}");
            $this->line("    {$url}");

            try {
                $resp = Http::timeout(20)
                    ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (event image importer)'])
                    ->get($url);
            } catch (\Throwable $e) {
                $this->warn("    fetch failed: {$e->getMessage()}");
                $errors++;
                continue;
            }

            if (! $resp->successful()) {
                $this->warn("    HTTP " . $resp->status());
                $errors++;
                continue;
            }

            $ogImage = $this->extractOgImage($resp->body());
            if (! $ogImage) {
                $this->warn("    no og:image meta tag");
                $skipped++;
                continue;
            }

            // Resolve relative URLs against the page URL
            if (! preg_match('#^https?://#i', $ogImage)) {
                $base = parse_url($url);
                $ogImage = ($base['scheme'] ?? 'https') . '://' . ($base['host'] ?? '') . $ogImage;
            }

            try {
                $imgResp = Http::timeout(45)
                    ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (event image importer)'])
                    ->get($ogImage);
            } catch (\Throwable $e) {
                $this->warn("    image fetch failed: {$e->getMessage()}");
                $errors++;
                continue;
            }

            if (! $imgResp->successful()) {
                $this->warn("    image HTTP " . $imgResp->status());
                $errors++;
                continue;
            }

            $bytes = $imgResp->body();
            $ext = strtolower(pathinfo(parse_url($ogImage, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) ?: 'jpg';
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) $ext = 'jpg';

            $slug = $event->slug ?: Str::slug($event->title);
            $path = "events/{$slug}.{$ext}";
            Storage::disk('public')->put($path, $bytes);

            $event->image = $path;
            $event->save();

            $this->info("    downloaded " . strlen($bytes) . " bytes -> {$path}");
            $downloaded++;

            usleep(300 * 1000);
        }

        $this->info("\nDone. Downloaded {$downloaded}, skipped (no og:image) {$skipped}, errors {$errors}.");

        return self::SUCCESS;
    }

    private function extractOgImage(string $html): ?string
    {
        // <meta property="og:image" content="..."> or content first
        if (preg_match('/<meta\s+(?:[^>]*?\s)?property=["\']og:image["\'][^>]*?content=["\']([^"\']+)["\']/i', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        }
        if (preg_match('/<meta\s+(?:[^>]*?\s)?content=["\']([^"\']+)["\'][^>]*?property=["\']og:image["\']/i', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        }
        // Twitter card fallback
        if (preg_match('/<meta\s+(?:[^>]*?\s)?name=["\']twitter:image["\'][^>]*?content=["\']([^"\']+)["\']/i', $html, $m)) {
            return html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5);
        }
        return null;
    }
}
