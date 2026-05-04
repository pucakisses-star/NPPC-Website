<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class FetchVolunteerHero extends Command
{
    protected $signature = 'site:fetch-volunteer-hero {--overwrite : Replace the existing file if it already exists}';
    protected $description = 'Download a public-domain hero photo to public/images/site/volunteer-hero.jpg from a small list of fallback Wikipedia articles, restricted to Wikimedia Commons.';

    /**
     * Tried in order. The first article whose lead image lives on Wikimedia
     * Commons (i.e. is genuinely public-domain or Commons-licensed, not
     * fair-use) wins. Picked for the volunteer-page hero — a wide,
     * crowd-of-people image that reads as an organizing / civil-rights
     * scene, not a portrait of one person.
     */
    private const CANDIDATES = [
        'March on Washington for Jobs and Freedom',
        'Civil rights movement',
        'Selma to Montgomery marches',
        '1963 March on Washington',
        'Poor People\'s Campaign',
        'Free Mumia Abu-Jamal',
    ];

    public function handle(): int
    {
        $dest = public_path('images/site/volunteer-hero.jpg');
        @mkdir(dirname($dest), 0755, true);

        if (file_exists($dest) && ! $this->option('overwrite')) {
            $this->warn("File exists: {$dest}. Pass --overwrite to replace.");
            return self::SUCCESS;
        }

        foreach (self::CANDIDATES as $title) {
            $this->line("Trying: {$title}");
            $url = $this->resolveCommonsImage($title);
            if (! $url) { $this->warn('  no Commons image'); continue; }

            try {
                $bytes = Http::timeout(45)
                    ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (volunteer hero importer)'])
                    ->get($url)
                    ->throw()
                    ->body();
            } catch (\Throwable $e) {
                $this->error('  download failed: ' . $e->getMessage());
                continue;
            }

            file_put_contents($dest, $bytes);
            $this->info('Saved ' . strlen($bytes) . ' bytes -> ' . $dest);
            $this->line('  source: ' . $url);
            return self::SUCCESS;
        }

        $this->error('No candidate worked. Try passing a different Wikipedia article title.');
        return self::FAILURE;
    }

    private function resolveCommonsImage(string $title): ?string
    {
        try {
            $resp = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'NPPC-Website/1.0 (volunteer hero importer)'])
                ->get('https://en.wikipedia.org/api/rest_v1/page/summary/' . rawurlencode(str_replace(' ', '_', $title)));
        } catch (\Throwable $e) {
            return null;
        }

        if (! $resp->successful()) return null;
        $body = $resp->json();

        $candidate = $body['originalimage']['source'] ?? $body['thumbnail']['source'] ?? null;
        if (! $candidate) return null;

        // Strict Commons-only filter: never pull fair-use images that live on en.wikipedia.org
        if (! str_contains($candidate, '/wikipedia/commons/')) {
            $this->warn("  '{$title}': lead image is not on Commons (likely fair-use); skipping");
            return null;
        }

        return $candidate;
    }
}
