<?php

namespace App\Console\Commands;

use App\Models\HistoryTopic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadHistoryTopicImages extends Command
{
    protected $signature = 'history:download-topic-images {--overwrite : Replace topic images that are already set}';
    protected $description = 'Download a public-domain illustrative photo from Wikimedia Commons for each HistoryTopic on /history. Restricted to Commons (free-license) — never pulls fair-use images from en.wikipedia.';

    /**
     * Map of HistoryTopic title → English Wikipedia article title to pull the
     * main image from. Only includes topics whose Wikipedia article's main
     * image is hosted on Wikimedia Commons (i.e. free / public-domain).
     */
    private const PAGES = [
        'The Sedition Act'                     => 'Alien and Sedition Acts',
        'The Abolition Movement'               => 'Abolitionism in the United States',
        'The Civil War'                        => 'American Civil War',
        'The Labor Movement'                   => 'Labor history of the United States',
        'Suffragism'                           => "Women's suffrage in the United States",
        'The First Red Scare'                  => 'First Red Scare',
        'World War I'                          => 'American entry into World War I',
        'World War II'                         => 'United States home front during World War II',
        'McCarthyism'                          => 'McCarthyism',
        'The Civil Rights Movement'            => 'Civil rights movement',
        'The Vietnam War'                      => 'Opposition to United States involvement in the Vietnam War',
        'COINTELPRO'                           => 'COINTELPRO',
        'Puerto Rican Independence Movement'   => 'Puerto Rican independence movement',
        'The War on Terror'                    => 'War on terror',
        'The Green Scare'                      => 'Green Scare',
        'Anonymous'                            => 'Anonymous (hacker group)',
        'Occupy Wall Street'                   => 'Occupy Wall Street',
        'Black Lives Matter'                   => 'Black Lives Matter',
    ];

    public function handle(): int
    {
        $downloaded   = 0;
        $skippedExisting = 0;
        $skippedNonFree  = 0;
        $skippedNoImage  = 0;
        $skippedNoTopic  = 0;
        $errored         = 0;

        Storage::disk('public')->makeDirectory('history');

        foreach (self::PAGES as $title => $wikiTitle) {
            $topic = HistoryTopic::where('title', $title)->first();
            if (! $topic) {
                $this->warn("  no HistoryTopic with title '{$title}'");
                $skippedNoTopic++;
                continue;
            }

            if ($topic->image && ! $this->option('overwrite')) {
                $this->line("  '{$title}': already has image, skipping");
                $skippedExisting++;
                continue;
            }

            try {
                $thumb = $this->resolveCommonsThumbnail($wikiTitle);
            } catch (\Throwable $e) {
                $this->error("  '{$title}': {$e->getMessage()}");
                $errored++;
                continue;
            }

            if ($thumb === null) {
                $this->warn("  '{$title}': no article image");
                $skippedNoImage++;
                continue;
            }

            if (! str_contains($thumb, '/wikipedia/commons/')) {
                $this->warn("  '{$title}': Wikipedia image is not on Commons (likely fair-use); skipping");
                $skippedNonFree++;
                continue;
            }

            try {
                $bytes = Http::timeout(45)
                    ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (history topic image importer)'])
                    ->get($thumb)
                    ->throw()
                    ->body();
            } catch (\Throwable $e) {
                $this->error("  '{$title}': download failed — {$e->getMessage()}");
                $errored++;
                continue;
            }

            $ext = strtolower(pathinfo(parse_url($thumb, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION)) ?: 'jpg';
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) $ext = 'jpg';

            $slug = Str::slug($title);
            $path = "history/{$slug}.{$ext}";
            Storage::disk('public')->put($path, $bytes);

            $topic->image = $path;
            $topic->save();

            $this->info("  '{$title}': downloaded " . strlen($bytes) . " bytes → {$path}");
            $downloaded++;
        }

        $this->line('');
        $this->info('Done.');
        $this->line("  downloaded:                  {$downloaded}");
        $this->line("  skipped (already had image): {$skippedExisting}");
        $this->line("  skipped (no topic row):      {$skippedNoTopic}");
        $this->line("  skipped (no article image):  {$skippedNoImage}");
        $this->line("  skipped (non-free):          {$skippedNonFree}");
        $this->line("  errored:                     {$errored}");

        return self::SUCCESS;
    }

    /**
     * Use the Wikipedia REST API to fetch the page summary, which exposes
     * `originalimage.source` — the article's main illustrative image.
     */
    private function resolveCommonsThumbnail(string $wikiTitle): ?string
    {
        $url = 'https://en.wikipedia.org/api/rest_v1/page/summary/' . rawurlencode(str_replace(' ', '_', $wikiTitle));

        $resp = Http::timeout(20)
            ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (history topic image importer)'])
            ->get($url);

        if (! $resp->successful()) {
            throw new \RuntimeException("REST API returned HTTP {$resp->status()}");
        }

        $body = $resp->json();
        return $body['originalimage']['source']
            ?? $body['thumbnail']['source']
            ?? null;
    }
}
