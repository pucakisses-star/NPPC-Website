<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Sets photos on the 5 prisoners added in PR #558 (the 1917 NWP
 * suffragists + Bonus Army leader John Pace). Pulls each photo from
 * Wikipedia's REST summary endpoint and stores under
 * prisoners/<slug>.jpg.
 *
 * Idempotent.
 */
final class SetSuffragistsPacePhotos extends Command {
    protected $signature = 'archive:set-suffragists-pace-photos';
    protected $description = 'Pull Wikipedia photos for the 4 NWP suffragists + John Pace';

    private const MAP = [
        // prisoner.name           Wikipedia article slug                       local path
        'Dora Lewis'             => ['Dora Lewis',                              'prisoners/dora-lewis.jpg'],
        'Mabel Vernon'           => ['Mabel Vernon',                            'prisoners/mabel-vernon.jpg'],
        'Annie Arniel'           => ['Annie Arniel',                            'prisoners/annie-arniel.jpg'],
        'Florence Bayard Hilles' => ['Florence_Bayard_Hilles',                  'prisoners/florence-bayard-hilles.jpg'],
        'John Pace'              => ['John_T._Pace',                            'prisoners/john-pace.jpg'],
    ];

    public function handle(): int {
        $set = 0; $missing = []; $noPhoto = [];

        foreach (self::MAP as $name => [$articleSlug, $localPath]) {
            $prisoner = Prisoner::query()->where('name', $name)->first();
            if (! $prisoner) {
                $missing[] = $name;
                continue;
            }

            $imageUrl = $this->wikipediaImage($articleSlug);
            if (! $imageUrl) {
                $noPhoto[] = $name.' (Wikipedia article: '.$articleSlug.')';
                continue;
            }

            try {
                $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)'])
                    ->timeout(60)
                    ->get($imageUrl);
                if (! $resp->successful() || strlen($resp->body()) < 2000) {
                    $noPhoto[] = $name.' (download failed: HTTP '.$resp->status().')';
                    continue;
                }
                Storage::disk('public')->put($localPath, $resp->body());
            } catch (\Throwable $e) {
                $noPhoto[] = $name.' ('.$e->getMessage().')';
                continue;
            }

            $prisoner->photo = $localPath;
            $prisoner->save();
            $this->info("PHOTO: {$name} → {$localPath}");
            $set++;
        }

        $this->newLine();
        $this->info("Done — photos set: {$set}.");
        if ($missing) {
            $this->warn('Prisoner row not found ('.count($missing).'): '.implode(', ', $missing));
        }
        if ($noPhoto) {
            $this->warn('No Wikipedia image ('.count($noPhoto).'):');
            foreach ($noPhoto as $n) {
                $this->line('  - '.$n);
            }
        }
        return self::SUCCESS;
    }

    private function wikipediaImage(string $slug): ?string {
        try {
            $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)'])
                ->timeout(20)
                ->get('https://en.wikipedia.org/api/rest_v1/page/summary/'.rawurlencode($slug));
        } catch (\Throwable $e) {
            return null;
        }
        if (! $resp->successful()) {
            return null;
        }
        $data = $resp->json();
        $url = $data['originalimage']['source'] ?? $data['thumbnail']['source'] ?? null;
        if (! $url || str_contains($url, 'wikipedia-logo')) {
            return null;
        }
        return $url;
    }
}
