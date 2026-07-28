<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * Import the First Red Scare deportees from Kenyon Zimmer's "Red Scare
 * Deportees" index (kenyonzimmer.com/red-scare-deportees), a scholarly
 * database built from INS deportation case files.
 *
 * The roster ships as database/data/zimmer-deportees.json: 702 people
 * deported as alien radicals in 1919-1922, parsed from all 89 pages of
 * the index, with 45 people already in the database excluded at build
 * time (Emma Goldman, Berkman, the Abrams defendants, the Magonistas and
 * others), 86 excluded because the source gives no deportation date, and
 * a handful of unusable entries (illegible surnames) dropped.
 *
 * CUSTODY IS RECORDED CONSERVATIVELY, in three tiers:
 *
 *   span (173)   Arrested in the 1919 Palmer Raids and deported on the
 *                Buford transport of December 21, 1919, with no mention
 *                of bail: the detention from arrest to sailing --
 *                principally at Ellis Island -- is documented for this
 *                group, so incarceration and release dates are set and
 *                the counter runs. Institution: Ellis Island Immigration
 *                Station (the name the database already uses).
 *
 *   arrest (324) An arrest date is documented but continuous detention
 *                between arrest and deportation is not (or bail is
 *                mentioned): the arrest date is set, the counter stays
 *                empty, and the case text says why.
 *
 *   exile (205)  Only the deportation is dated. No custody fields at
 *                all; the deportation is recorded as exile.
 *
 * EXILE: every record gets in_exile_since = the deportation date. No end
 * of exile is set -- most never returned -- so the exile counter does not
 * run open-ended for people long dead (the same released-without-a-date
 * rule the imprisonment counter uses).
 *
 * Bios are composed from the structured facts of each entry (birth,
 * occupation, migration, affiliation, arrest, ship, destination) with
 * attribution to Zimmer's index -- they are not copied from the site's
 * narrative text.
 *
 * Birth years (many "c." approximations) are stored at YEAR precision;
 * unknown day/month is never invented. Deportation dates are full dates.
 *
 * Idempotent: existing records (matched by normalized name or AKA,
 * accent-insensitive) are skipped at runtime as well. Dry-run by
 * default:
 *
 *   php artisan prisoners:add-zimmer-deportees
 *   php artisan prisoners:add-zimmer-deportees --apply
 */
final class AddZimmerDeportees extends Command
{
    protected $signature = 'prisoners:add-zimmer-deportees {--apply : Create the records}';

    protected $description = 'Import First Red Scare deportees from Kenyon Zimmer\'s Red Scare Deportees index';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $path = database_path('data/zimmer-deportees.json');
        $roster = json_decode(file_get_contents($path), true);
        if (! is_array($roster)) {
            $this->error('Could not read '.$path);

            return self::FAILURE;
        }

        // Runtime duplicate check against every existing name and AKA.
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
        }

        $created = 0;
        $skipped = 0;
        $tiers = ['span' => 0, 'arrest' => 0, 'exile' => 0];

        foreach ($roster as $r) {
            $keys = array_filter([$this->norm($r['name']), $r['aka'] ? $this->norm($r['aka']) : null]);
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
                    'gender' => null,
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

                // Keep the runtime index current so roster-internal
                // duplicates cannot slip through either.
                foreach ($keys as $k) {
                    $existing[$k] = true;
                }
            }
            $created++;
        }

        $this->newLine();
        $this->info(($apply ? 'Created' : 'Would create')." {$created} record(s); skipped {$skipped} already present.");
        $this->info("Custody tiers: {$tiers['span']} with a documented detention span (Buford/Ellis Island), {$tiers['arrest']} arrest-date-only, {$tiers['exile']} exile-only.");
        if (! $apply) {
            $this->info('Dry run. Re-run with --apply to create the records.');
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info('Done. Run php artisan prisoners:place-zero-sort-by-year --apply to place the new records in the archive order.');
        }

        return self::SUCCESS;
    }

    private function norm(string $s): string
    {
        $s = strtolower(Str::ascii($s));

        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z ]/', ' ', $s)));
    }
}
