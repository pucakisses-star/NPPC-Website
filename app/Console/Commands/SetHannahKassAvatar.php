<?php

namespace App\Console\Commands;

use App\Models\Author;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Fetches and assigns the Hannah Kass author avatar from the UPenn
 * Liberal & Professional Studies site.
 *
 * Idempotent — if the file already exists on disk, it just re-points
 * the avatar column.
 */
final class SetHannahKassAvatar extends Command {
    protected $signature = 'archive:set-hannah-kass-avatar';
    protected $description = 'Download + set the Hannah Kass author avatar';

    private const URL  = 'https://www.lps.upenn.edu/sites/default/files/inline-images/MES-Community-March2020-headshot_0.jpg';
    private const PATH = 'authors/hannah-kass.jpg';

    public function handle(): int {
        $author = Author::where('name', 'Hannah Kass')->first();
        if (! $author) {
            $this->error('Author "Hannah Kass" not found.');
            return self::FAILURE;
        }

        if (! Storage::disk('public')->exists(self::PATH)) {
            try {
                $resp = Http::withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; NPPC-Archive/1.0)'])
                    ->timeout(45)->get(self::URL);
                if (! $resp->successful() || strlen($resp->body()) < 2000) {
                    $this->error('Download HTTP '.$resp->status());
                    return self::FAILURE;
                }
                Storage::disk('public')->put(self::PATH, $resp->body());
                $this->info('Saved avatar to '.self::PATH);
            } catch (\Throwable $e) {
                $this->error('Image fetch failed: '.$e->getMessage());
                return self::FAILURE;
            }
        } else {
            $this->info('Avatar already on disk at '.self::PATH);
        }

        $author->avatar = self::PATH;
        $author->save();
        $this->info('Author updated: '.$author->name.' avatar -> '.self::PATH);

        return self::SUCCESS;
    }
}
