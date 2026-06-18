<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds U.S.-imprisoned peace activists drawn from Wikipedia's "List of peace
 * activists" who were missing from the database, from
 * database/data/wikipedia-peace-activists.json. Each was verified to have
 * been imprisoned/sentenced in the United States for peace/anti-war activism.
 *
 * Where a record carries a freely-licensed photo_url (Wikimedia Commons),
 * the image is downloaded to the public disk and attached. Idempotent —
 * skips a prisoner whose name already exists.
 */
class AddWikipediaPeaceActivists extends Command
{
    protected $signature = 'prisoners:add-wikipedia-peace-activists';

    protected $description = 'Add U.S.-imprisoned peace activists from wikipedia-peace-activists.json (with photos)';

    private const UA = 'NPPC-Archive-PhotoBot/1.0 (https://nppc.org; advocacy nonprofit) Laravel-Http';

    public function handle(): int
    {
        $file = database_path('data/wikipedia-peace-activists.json');
        if (! file_exists($file)) {
            $this->error('wikipedia-peace-activists.json not found.');

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);
        if (! is_array($rows)) {
            $this->error("Could not parse {$file}");

            return self::FAILURE;
        }

        $disk = Storage::disk('public');
        $added = 0;
        $skipped = 0;

        foreach ($rows as $r) {
            $prisoner = Prisoner::withUnderReview()->where('name', $r['name'])->first();

            if ($prisoner) {
                $this->warn("Exists: {$r['name']}");
                $skipped++;
            } else {
                $prisoner = DB::transaction(function () use ($r) {
                    $prisoner = Prisoner::create([
                        'name' => $r['name'],
                        'first_name' => $r['first_name'] ?? null,
                        'last_name' => $r['last_name'] ?? null,
                        'description' => $r['bio'],
                        'gender' => $r['gender'] ?? null,
                        'race' => $r['race'] ?? null,
                        'birthdate' => $r['birthdate'] ?? null,
                        'death_date' => $r['death_date'] ?? null,
                        'state' => $r['state'] ?? null,
                        'era' => $r['era'] ?? null,
                        'ideologies' => $r['ideologies'] ?? [],
                        'affiliation' => $r['affiliation'] ?? [],
                        'in_custody' => false,
                        'released' => $r['released'] ?? true,
                        'awaiting_trial' => false,
                    ]);

                    PrisonerCase::create([
                        'prisoner_id' => $prisoner->id,
                        'charges' => $r['charges'] ?? null,
                        'convicted' => $r['convicted'] ?? null,
                        'sentence' => $r['sentence'] ?? null,
                    ]);

                    return $prisoner;
                });

                $this->info("Added: {$r['name']}");
                $added++;
            }

            // Attach the photo whenever one is configured and not yet set
            // (so re-running fills in any photo that previously failed).
            if (! empty($r['photo_url']) && ! $prisoner->photo) {
                $this->attachPhoto($prisoner, $r['photo_url'], $r['photo_license'] ?? '', $disk);
            }
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function attachPhoto(Prisoner $prisoner, string $url, string $license, $disk): void
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }
        $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;

        if (! $disk->exists($path)) {
            foreach ([0, 5, 12] as $wait) {
                if ($wait) {
                    sleep($wait);
                }
                try {
                    $resp = Http::withHeaders(['User-Agent' => self::UA])->timeout(60)->get($url);
                    if ($resp->status() === 429) {
                        $this->warn("  429 rate-limited, backing off ({$prisoner->name})");

                        continue;
                    }
                    if (! $resp->successful() || strlen($resp->body()) < 2000) {
                        $this->warn("  Photo download failed (HTTP {$resp->status()}): {$prisoner->name}");

                        return;
                    }
                    $disk->put($path, $resp->body());
                    break;
                } catch (\Throwable $e) {
                    $this->warn('  Photo fetch error for '.$prisoner->name.': '.$e->getMessage());

                    return;
                }
            }
        }

        if ($disk->exists($path)) {
            $prisoner->photo = $path;
            $prisoner->save();
            $this->info("  Photo set: {$path}  [{$license}]");
        }
    }
}
