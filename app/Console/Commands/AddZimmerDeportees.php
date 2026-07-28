<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Import and enrich the First Red Scare deportees from Kenyon Zimmer's
 * "Red Scare Deportees" index (kenyonzimmer.com/red-scare-deportees), a
 * scholarly database built from INS deportation case files.
 *
 * The roster ships as database/data/zimmer-deportees.json -- 746 records
 * parsed from all 89 pages of the index -- plus 100 portrait photographs
 * (mugshots, prison photos, newspaper portraits) under
 * database/data/photos/zimmer/, hand-verified as portraits of the person
 * rather than document scans (membership cards, fliers and the like are
 * excluded).
 *
 * TWO KINDS OF RECORD:
 *
 *   NEW (716)     Deportees not in the database. Full record: composed
 *                 facts header plus the index's biographical narrative,
 *                 with attribution; birth year (year precision, "c."
 *                 kept approximate); affiliations mapped onto existing
 *                 taxonomy (Union of Russian Workers, IWW, Communist
 *                 Party USA, Partido Liberal Mexicano, Socialist Party
 *                 of America); portrait attached where one exists (67).
 *                 Custody in three conservative tiers: 176 Palmer Raid
 *                 arrestees deported on the Buford with no bail mention
 *                 get the documented arrest-to-sailing detention (Ellis
 *                 Island Immigration Station) and a running counter; 331
 *                 arrest-date-only; 209 exile-only. Every record gets
 *                 in_exile_since = the deportation date, with no end of
 *                 exile invented.
 *
 *   ENRICH (30)   Deportees already in the database (Goldman, Berkman,
 *                 Steimer, Galleani...). NOTHING IS OVERWRITTEN: a
 *                 portrait is attached only if the record has no photo
 *                 (24 available), the birth year is set only if the
 *                 field is empty, and affiliations are merged in without
 *                 removing existing ones. Bios are left alone.
 *
 * MATCHING is accent-insensitive on name and alias, but only multi-word
 * keys count and matched records must sit in a plausible era -- single
 * -word aliases and era mismatches produced false matches (a 1919
 * deportee is not the 2000s Vieques protester who shares his name).
 * Three new records carry force_new because their names collide with
 * unrelated existing records (Jose Angel Hernandez, Johan Johanson,
 * Carl Larson); the slug generator disambiguates them.
 *
 * Idempotent, dry-run by default:
 *
 *   php artisan prisoners:add-zimmer-deportees
 *   php artisan prisoners:add-zimmer-deportees --apply
 */
final class AddZimmerDeportees extends Command
{
    protected $signature = 'prisoners:add-zimmer-deportees {--apply : Create and enrich the records}';

    protected $description = 'Import and enrich First Red Scare deportees from Kenyon Zimmer\'s Red Scare Deportees index';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $roster = json_decode(file_get_contents(database_path('data/zimmer-deportees.json')), true);
        if (! is_array($roster)) {
            $this->error('Could not read zimmer-deportees.json');

            return self::FAILURE;
        }

        $existing = [];
        foreach (Prisoner::withoutGlobalScopes()->get(['name', 'aka']) as $p) {
            foreach ([$p->name, $p->aka] as $n) {
                if ($n) {
                    $existing[$this->norm($n)] = true;
                }
            }
        }

        $ellis = null;
        if ($apply) {
            $ellis = Institution::firstOrCreate(
                ['name' => 'Ellis Island Immigration Station'],
                ['city' => 'New York', 'state' => 'New York'],
            );
            File::ensureDirectoryExists(storage_path('app/public/prisoners'));
        }

        $created = 0;
        $skipped = 0;
        $enriched = 0;
        $photosAttached = 0;
        $tiers = ['span' => 0, 'arrest' => 0, 'exile' => 0];

        foreach ($roster as $r) {
            if ($r['enrich_only']) {
                $enriched += $this->enrich($r, $apply, $photosAttached) ? 1 : 0;

                continue;
            }

            $keys = array_filter([$this->norm($r['name']), $r['aka'] ? $this->norm($r['aka']) : null]);
            if (empty($r['force_new'])) {
                $isDup = false;
                foreach ($keys as $k) {
                    if (isset($existing[$k])) {
                        $isDup = true;
                    }
                }
                if ($isDup) {
                    $this->line('  skip (exists): '.$r['name']);
                    $skipped++;

                    continue;
                }
            }

            $c = $r['case'];
            $tier = $c['incarceration'] ? 'span' : ($c['arrest'] ? 'arrest' : 'exile');
            $tiers[$tier]++;

            if ($apply) {
                $p = new Prisoner([
                    'name' => $r['name'],
                    'first_name' => $r['first_name'],
                    'middle_name' => $r['middle_name'],
                    'last_name' => $r['last_name'],
                    'aka' => $r['aka'],
                    'description' => $r['description'],
                    'era' => $r['era'],
                    'ideologies' => $r['ideologies'] ?: null,
                    'affiliation' => $r['affiliations'] ?: null,
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'currently_in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if ($r['birth_year']) {
                    $p->setPartialDate('birthdate', (int) $r['birth_year']);
                }
                $p->save();

                if ($r['photo']) {
                    $this->attachPhoto($p, $r['photo']) && $photosAttached++;
                }

                $case = $p->cases()->make([
                    'charges' => $c['charges'],
                    'sentence' => $c['sentence'],
                ]);
                if ($tier === 'span' && $ellis) {
                    $case->institution_id = $ellis->id;
                }
                foreach ([['arrest_date', $c['arrest']], ['incarceration_date', $c['incarceration']], ['release_date', $c['release']], ['in_exile_since', $c['exile_since']]] as [$field, $val]) {
                    if ($val) {
                        $case->setPartialDate($field, ...array_map(fn ($x) => $x === null ? null : (int) $x, $val));
                    }
                }
                $case->save();

                foreach ($keys as $k) {
                    $existing[$k] = true;
                }
            }
            $created++;
        }

        $this->newLine();
        $this->info(($apply ? 'Created' : 'Would create')." {$created} record(s); enriched {$enriched} existing; skipped {$skipped}; ".($apply ? "{$photosAttached} photo(s) attached." : 'photos attach on --apply.'));
        $this->info("Custody tiers: {$tiers['span']} detention-span (Buford/Ellis Island), {$tiers['arrest']} arrest-only, {$tiers['exile']} exile-only.");
        if (! $apply) {
            $this->info('Dry run. Re-run with --apply.');
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info('Done. Run php artisan prisoners:place-zero-sort-by-year --apply to place the new records.');
        }

        return self::SUCCESS;
    }

    /** Fill gaps on an existing record without overwriting anything. */
    private function enrich(array $r, bool $apply, int &$photosAttached): bool
    {
        $p = Prisoner::withoutGlobalScopes()->where('slug', $r['existing_slug'])->first();
        if (! $p) {
            $this->warn('  enrich target missing: '.$r['existing_slug']);

            return false;
        }

        $actions = [];
        if ($r['photo'] && ! $p->photo) {
            $actions[] = 'photo';
        }
        if ($r['birth_year'] && ! $p->birthdate) {
            $actions[] = 'birth '.$r['birth_year'];
        }
        $merged = array_values(array_unique(array_merge($p->affiliation ?: [], $r['affiliations'] ?: [])));
        if ($merged !== ($p->affiliation ?: [])) {
            $actions[] = 'affiliations';
        }

        if (! $actions) {
            return false;
        }
        $this->line('  enrich '.$p->slug.': '.implode(', ', $actions));

        if ($apply) {
            if (in_array('photo', $actions, true)) {
                $this->attachPhoto($p, $r['photo']) && $photosAttached++;
            }
            if ($r['birth_year'] && ! $p->birthdate) {
                $p->setPartialDate('birthdate', (int) $r['birth_year']);
            }
            $p->affiliation = $merged ?: null;
            $p->save();
        }

        return true;
    }

    private function attachPhoto(Prisoner $p, string $file): bool
    {
        $src = database_path('data/photos/zimmer/'.$file);
        if (! is_file($src)) {
            $this->warn('  photo file missing: '.$file);

            return false;
        }
        $dest = 'prisoners/'.$p->slug.'.jpg';
        File::copy($src, storage_path('app/public/'.$dest));
        touch(storage_path('app/public/'.$dest));
        $p->photo = $dest;
        $p->save();

        return true;
    }

    private function norm(string $s): string
    {
        $s = strtolower(Str::ascii($s));

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z ]/', ' ', $s)));
    }
}
