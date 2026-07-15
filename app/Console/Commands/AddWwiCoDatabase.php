<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Bulk-adds the World War I conscientious objectors who were imprisoned in the
 * U.S. military-prison system, drawn entry-by-entry from Anne Yoder's Swarthmore
 * College Peace Collection "Database of World War I C.O.s." This is the ~569
 * people in that roster with a prison record (Fort Leavenworth, the Fort Douglas
 * War Prison Barracks, Alcatraz, Fort MacArthur, etc.), excluding the ~30
 * already added in detail by prisoners:add-wwi-leavenworth-objectors.
 *
 * Each record carries whatever the database lists: birth/death dates, home
 * state, denomination, prison(s), draft and release dates, sentence, and the
 * archival note. Data lives in database/data/wwi-cos-imprisoned.json.
 *
 * SAFETY: create-or-update by name, but any existing record that already has a
 * description is left untouched (only empty stubs are filled) so unrelated
 * prisoners who happen to share a name are never overwritten. Idempotent.
 */
class AddWwiCoDatabase extends Command
{
    protected $signature = 'prisoners:add-wwi-co-database {--limit=0 : only process the first N (for testing)}';

    protected $description = 'Bulk-add WWI conscientious objectors imprisoned at Fort Leavenworth/Douglas/Alcatraz (Swarthmore/Yoder database)';

    public function handle(): int
    {
        $path = database_path('data/wwi-cos-imprisoned.json');
        if (! is_file($path)) {
            $this->error("Missing data file: {$path}");

            return self::FAILURE;
        }
        $people = json_decode(file_get_contents($path), true);
        if (! is_array($people)) {
            $this->error('Could not parse the data file.');

            return self::FAILURE;
        }
        if ($limit = (int) $this->option('limit')) {
            $people = array_slice($people, 0, $limit);
        }

        $institutions = [
            'leavenworth' => Institution::firstOrCreate(['name' => 'United States Disciplinary Barracks, Fort Leavenworth'], ['city' => 'Fort Leavenworth', 'state' => 'Kansas'])->id,
            'douglas' => Institution::firstOrCreate(['name' => 'Fort Douglas War Prison Barracks'], ['city' => 'Salt Lake City', 'state' => 'Utah'])->id,
            'alcatraz' => Institution::firstOrCreate(['name' => 'United States Disciplinary Barracks, Alcatraz'], ['city' => 'San Francisco', 'state' => 'California'])->id,
            'macarthur' => Institution::firstOrCreate(['name' => 'Fort MacArthur'], ['city' => 'San Pedro', 'state' => 'California'])->id,
        ];

        // Records this command authors carry this phrase; we may safely re-run
        // over our own records, but never overwrite an unrelated prisoner who
        // happens to share a name.
        $signature = 'World War I Conscientious Objectors database';

        $added = $updated = $skipped = 0;

        foreach (array_chunk($people, 100) as $chunk) {
            DB::transaction(function () use ($chunk, $institutions, $signature, &$added, &$updated, &$skipped) {
                foreach ($chunk as $p) {
                    $existing = Prisoner::withUnderReview()->where('name', $p['name'])->first();
                    if ($existing) {
                        $desc = (string) $existing->description;
                        if (trim($desc) !== '' && ! str_contains($desc, $signature)) {
                            $skipped++;

                            continue; // an unrelated described record — leave it alone
                        }
                        $updated++;
                    } else {
                        $added++;
                    }
                    $prisoner = $existing ?? new Prisoner(['name' => $p['name']]);
                    $died = ! empty($p['died']);

                    $ideologies = ['Pacifism', 'Conscientious objection'];
                    $affiliation = [];
                    if (! empty($p['denom'])) {
                        if ($p['denom'] === 'Socialist') {
                            $ideologies[] = 'Socialism';
                        } else {
                            $affiliation[] = $p['denom'];
                        }
                    }

                    $prisoner->fill([
                        'name' => $p['name'],
                        'first_name' => $p['first'] ?? null,
                        'middle_name' => $p['middle'] ?? null,
                        'last_name' => $p['last'] ?? null,
                        'gender' => 'Male',
                        'state' => $p['state'] ?? null,
                        'era' => '1910s',
                        'ideologies' => $ideologies,
                        'affiliation' => $affiliation,
                        'description' => $this->bio($p, $died),
                        'in_custody' => false,
                        'released' => ! $died,
                        'in_exile' => false,
                        'awaiting_trial' => false,
                    ]);
                    if (! empty($p['birth'])) {
                        $prisoner->setPartialDate('birthdate', ...$p['birth']);
                    }
                    if (! empty($p['death'])) {
                        $prisoner->setPartialDate('death_date', ...$p['death']);
                    }
                    $prisoner->save();

                    // Attach a bundled portrait (from data/photos/) if provided and unset.
                    if (! empty($p['photo']) && empty($prisoner->photo)) {
                        $src = database_path('data/photos/'.$p['photo']);
                        if (is_file($src)) {
                            Storage::disk('public')->makeDirectory('prisoners');
                            Storage::disk('public')->put('prisoners/'.$p['photo'], file_get_contents($src));
                            $prisoner->photo = 'prisoners/'.$p['photo'];
                            $prisoner->save();
                        }
                    }

                    $prisoner->cases()->delete();
                    $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                    $case->fill([
                        'prisoner_id' => $prisoner->id,
                        'institution_id' => $institutions[$p['pk'] ?? ''] ?? null,
                        'charges' => 'Refusing military service as a conscientious objector during World War I — court-martialed for disobeying orders / refusing to wear the uniform.',
                        'convicted' => 'Yes — court-martialed.',
                        'sentence' => $this->sentence($p),
                    ]);
                    if (! empty($p['incarceration'])) {
                        $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                    }
                    if (! empty($p['release'])) {
                        $case->setPartialDate('release_date', ...$p['release']);
                    }
                    if ($died) {
                        $case->setPartialDate('death_in_custody_date', ...$p['died']);
                    }
                    $case->save();
                }
            });
        }

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info("Done. Added {$added} new, updated {$updated} existing, skipped {$skipped} (unrelated records).");

        return self::SUCCESS;
    }

    private function prisonReadable(array $p): string
    {
        $s = str_replace(['Ft.', '?'], ['Fort', ''], $p['prison'] ?? '');

        return trim(preg_replace('/\s+/', ' ', $s));
    }

    private function bio(array $p, bool $died): string
    {
        if (! empty($p['bio'])) {
            return $p['bio'];
        }
        $denom = ! empty($p['denom']) && $p['denom'] !== 'Socialist' ? $p['denom'].' ' : '';
        $where = ! empty($p['state']) ? ' from '.$p['state'] : '';
        $prison = $this->prisonReadable($p);

        $bio = $p['name'].' was a '.$denom.'conscientious objector'.$where
            .' imprisoned during World War I. Court-martialed for refusing military service, he was held at '
            .($prison ?: 'a U.S. military prison').'.';

        if ($died && ! empty($p['death'])) {
            $bio .= ' He died in custody in '.$this->yearOf($p['death']).'.';
        }
        if (! empty($p['sentence'])) {
            $bio .= ' '.rtrim($p['sentence'], '.').'.';
        }
        $bio .= ' (Documented in the Swarthmore College Peace Collection\'s World War I Conscientious Objectors database.)';
        if (! empty($p['notes'])) {
            $bio .= ' Archival note: '.$p['notes'];
        }

        return $bio;
    }

    private function sentence(array $p): string
    {
        $parts = [];
        if (! empty($p['sentence'])) {
            $parts[] = rtrim($p['sentence'], '.').'.';
        }
        $prison = $this->prisonReadable($p);
        if ($prison) {
            $parts[] = 'Held at '.$prison.'.';
        }
        if (! empty($p['released'])) {
            $parts[] = 'Released '.$p['released'].'.';
        }

        return $parts ? implode(' ', $parts) : 'Imprisoned as a conscientious objector during World War I.';
    }

    private function yearOf(array $d): string
    {
        return (string) $d[0];
    }
}
