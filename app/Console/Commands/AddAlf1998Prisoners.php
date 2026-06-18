<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Adds (and enriches) animal-liberation prisoners from a March 6, 1998
 * ALF prisoner-support mailing list, from database/data/alf-1998-prisoners.json.
 *
 * New names are created in full; names already in the database are left as-is
 * except for filling in a missing inmate number and attaching the listed
 * facility (with its mailing address) to a case that has no institution.
 * Idempotent.
 */
class AddAlf1998Prisoners extends Command
{
    protected $signature = 'prisoners:add-alf-1998';

    protected $description = 'Add/enrich animal-liberation prisoners from the March 1998 ALF support list';

    public function handle(): int
    {
        $file = database_path('data/alf-1998-prisoners.json');
        if (! file_exists($file)) {
            $this->error('alf-1998-prisoners.json not found.');

            return self::FAILURE;
        }

        $rows = json_decode(file_get_contents($file), true);
        if (! is_array($rows)) {
            $this->error("Could not parse {$file}");

            return self::FAILURE;
        }

        $added = 0;
        $enriched = 0;

        foreach ($rows as $r) {
            $prisoner = Prisoner::withUnderReview()->where('name', $r['name'])->first();

            if (! $prisoner) {
                $prisoner = Prisoner::create([
                    'name' => $r['name'],
                    'first_name' => $r['first_name'] ?? null,
                    'last_name' => $r['last_name'] ?? null,
                    'description' => $r['bio'] ?? null,
                    'gender' => $r['gender'] ?? null,
                    'race' => $r['race'] ?? null,
                    'birthdate' => $r['birthdate'] ?? null,
                    'state' => $r['state'] ?? null,
                    'era' => $r['era'] ?? '1990s',
                    'ideologies' => $r['ideologies'] ?? [],
                    'affiliation' => $r['affiliation'] ?? [],
                    'in_custody' => $r['in_custody'] ?? false,
                    'released' => $r['released'] ?? true,
                    'awaiting_trial' => false,
                ]);
                $this->info("Added: {$r['name']}");
                $added++;
            } else {
                $this->line("Exists: {$r['name']} (enriching)");
                $enriched++;
            }

            // Set the inmate number from the list (authoritative for these records).
            if (! empty($r['inmate_number']) && $prisoner->inmate_number !== $r['inmate_number']) {
                $prisoner->inmate_number = $r['inmate_number'];
                $prisoner->save();
            }

            // Fill in a missing birthdate.
            if (! empty($r['birthdate']) && empty($prisoner->birthdate)) {
                $prisoner->birthdate = $r['birthdate'];
                $prisoner->save();
            }

            // Download and attach a photo when one is configured and not set.
            if (! empty($r['photo_url']) && empty($prisoner->photo)) {
                $this->attachPhoto($prisoner, $r['photo_url']);
            }

            // Or copy a committed local photo file (used when no stable URL exists).
            if (! empty($r['photo_file']) && empty($prisoner->photo)) {
                $this->attachLocalPhoto($prisoner, $r['photo_file']);
            }

            // Facility + mailing address.
            $inst = null;
            if (! empty($r['institution']['name'])) {
                $i = $r['institution'];
                $inst = Institution::firstOrCreate(
                    ['name' => $i['name']],
                    ['city' => $i['city'] ?? null, 'state' => $i['state'] ?? null, 'mailing_address' => $i['mailing_address'] ?? null]
                );
                if (empty($inst->mailing_address) && ! empty($i['mailing_address'])) {
                    $inst->mailing_address = $i['mailing_address'];
                    $inst->city = $inst->city ?: ($i['city'] ?? null);
                    $inst->state = $inst->state ?: ($i['state'] ?? null);
                    $inst->save();
                }
            }

            // Ensure a case; create one for new prisoners, then attach the
            // facility to any case that lacks an institution.
            $case = $prisoner->cases()->first();
            if (! $case) {
                $case = PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges' => $r['charges'] ?? null,
                    'convicted' => $r['convicted'] ?? null,
                    'sentence' => $r['sentence'] ?? null,
                ]);
            }
            if ($inst && empty($case->institution_id)) {
                $case->institution_id = $inst->id;
                $case->save();
            }

            // Fill in a missing release date.
            if (! empty($r['release_date']) && empty($case->release_date)) {
                $case->release_date = $r['release_date'];
                $case->save();
            }

            // For fully-managed records (those carrying a bio), keep the
            // narrative fields in sync with the file so research updates apply.
            // Enrich-only entries (no bio) are left untouched.
            if (! empty($r['bio'])) {
                if ($prisoner->description !== $r['bio']) {
                    $prisoner->description = $r['bio'];
                    $prisoner->save();
                }
                $dirty = false;
                foreach (['charges', 'convicted', 'sentence'] as $f) {
                    if (! empty($r[$f]) && $case->{$f} !== $r[$f]) {
                        $case->{$f} = $r[$f];
                        $dirty = true;
                    }
                }
                if ($dirty) {
                    $case->save();
                }
            }
        }

        $this->info("\nDone. Added={$added} Enriched={$enriched}");

        return self::SUCCESS;
    }

    private function attachPhoto(Prisoner $prisoner, string $url): void
    {
        $ext = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg');
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            $ext = 'jpg';
        }
        $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;

        if (! Storage::disk('public')->exists($path)) {
            try {
                $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (advocacy nonprofit)'])
                    ->timeout(60)->get($url);
                if (! $resp->successful() || strlen($resp->body()) < 1500) {
                    $this->warn("  Photo download failed (HTTP {$resp->status()}): {$prisoner->name}");

                    return;
                }
                Storage::disk('public')->put($path, $resp->body());
            } catch (\Throwable $e) {
                $this->warn('  Photo fetch error for '.$prisoner->name.': '.$e->getMessage());

                return;
            }
        }

        $prisoner->photo = $path;
        $prisoner->save();
        $this->info("  Photo set: {$path}");
    }

    private function attachLocalPhoto(Prisoner $prisoner, string $relative): void
    {
        $src = database_path('data/'.$relative);
        if (! is_file($src)) {
            $this->warn("  Local photo not found: {$relative}");

            return;
        }
        $ext = strtolower(pathinfo($src, PATHINFO_EXTENSION) ?: 'jpg');
        $path = 'prisoners/'.Str::slug($prisoner->name).'.'.$ext;
        Storage::disk('public')->put($path, (string) file_get_contents($src));
        $prisoner->photo = $path;
        $prisoner->save();
        $this->info("  Photo set from file: {$path}");
    }
}
