<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use Carbon\Carbon;
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
 *                 Custody: 176 Palmer Raid arrestees deported on the
 *                 Buford get the documented arrest-to-sailing detention
 *                 (Ellis Island Immigration Station); 310 more with an
 *                 arrest date and no bail mention get an ASSUMED span
 *                 from arrest to sailing, stated as an assumption in the
 *                 case text; 21 with bail mentioned get the arrest date
 *                 only; 209 exile-only. Every record gets in_exile_since
 *                 = the deportation date; end_of_exile is set from a
 *                 documented return to the US (45 records) or the death
 *                 date, never invented. Death dates parsed from the
 *                 narratives (59 records) are stored at their stated
 *                 precision.
 *
 *   REFRESH       Records this import owns -- identified by either the
 *                 old template-only closing sentence or the current
 *                 attribution line ("Adapted from Kenyon Zimmer") -- are
 *                 diffed against the roster and re-synchronised in
 *                 place: description replaced, case text and dates
 *                 updated (including exile ends and custody spans the
 *                 roster learned after the record was first written),
 *                 photo file re-copied when its bytes differ (recrops
 *                 propagate), birth and death filled where missing,
 *                 affiliations merged. A record that already matches the
 *                 roster counts as "current" and is left untouched, so
 *                 the run is idempotent. Run the command again after
 *                 pulling and the records heal themselves.
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
 * Carl Larson); the slug generator disambiguates them. force_new
 * bypasses the name map only for records this import does NOT own: an
 * existing record with the same name whose description carries the
 * Zimmer attribution IS this record from a previous run, and is
 * refreshed rather than created again.
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
        foreach (Prisoner::withoutGlobalScopes()->get(['slug', 'name', 'aka']) as $p) {
            foreach ([$p->name, $p->aka] as $n) {
                if ($n) {
                    $existing[$this->norm($n)] ??= $p->slug;
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
        $refreshed = 0;
        $currentCount = 0;
        $photosAttached = 0;
        $tiers = ['span' => 0, 'arrest' => 0, 'exile' => 0];

        foreach ($roster as $r) {
            if ($r['enrich_only']) {
                $enriched += $this->enrich($r, $apply, $photosAttached) ? 1 : 0;

                continue;
            }

            $keys = array_filter([$this->norm($r['name']), $r['aka'] ? $this->norm($r['aka']) : null]);
            $ownSlug = null;
            if (empty($r['force_new'])) {
                foreach ($keys as $k) {
                    $ownSlug = $ownSlug ?? ($existing[$k] ?? null);
                }
            } else {
                // force_new exists because the name collides with an
                // UNRELATED record, so the name map cannot be consulted --
                // but a record with this exact name whose description
                // carries the Zimmer attribution is this import's own
                // output from a previous run, not the collision.
                $ownSlug = Prisoner::withoutGlobalScopes()
                    ->where('name', $r['name'])
                    ->where(function ($q) {
                        $q->where('description', 'like', '%Adapted from Kenyon Zimmer%')
                            ->orWhere('description', 'like', '%index of deportation case files%');
                    })
                    ->orderBy('created_at')
                    ->value('slug');
            }
            if ($ownSlug !== null) {
                // A record this import owns is diffed against the roster
                // and re-synchronised in place; one that already matches
                // counts as current and is left alone.
                $result = $this->refreshOwned($r, $ownSlug, $apply, $photosAttached);
                if ($result === 'refresh') {
                    $refreshed++;
                } elseif ($result === 'current') {
                    $currentCount++;
                } else {
                    $this->line('  skip (exists): '.$r['name']);
                    $skipped++;
                }

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
                if (! empty($r['death'])) {
                    $p->setPartialDate('death_date', ...array_map(fn ($x) => $x === null ? null : (int) $x, $r['death']));
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
                foreach ([['arrest_date', $c['arrest']], ['incarceration_date', $c['incarceration']], ['release_date', $c['release']], ['in_exile_since', $c['exile_since']], ['end_of_exile', $c['exile_end'] ?? null]] as [$field, $val]) {
                    if ($val) {
                        $case->setPartialDate($field, ...array_map(fn ($x) => $x === null ? null : (int) $x, $val));
                    }
                }
                $case->save();

                foreach ($keys as $k) {
                    $existing[$k] = $p->slug;
                }
            }
            $created++;
        }

        $this->newLine();
        $this->info(($apply ? 'Created' : 'Would create')." {$created} record(s); refreshed {$refreshed} owned record(s) that had drifted from the roster; {$currentCount} already current; enriched {$enriched} existing; skipped {$skipped}; ".($apply ? "{$photosAttached} photo(s) attached." : 'photos attach on --apply.'));
        if ($refreshed === 0 && $currentCount === 0 && $skipped > 100) {
            $this->warn('Hundreds skipped with zero refreshed/current: the records were probably created by an earlier roster but their attribution signature did not match. Check that git pull brought the latest main before this run.');
        }
        $this->info("Custody tiers: {$tiers['span']} detention-span (Buford/Ellis Island), {$tiers['arrest']} arrest-only, {$tiers['exile']} exile-only.");
        if (! $apply) {
            $this->info('Dry run. Re-run with --apply.');
        } else {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->info('Done. Run php artisan prisoners:place-zero-sort-by-year --apply to place the new records.');
        }

        return self::SUCCESS;
    }

    /**
     * Re-synchronise a record this import owns against the current roster.
     * Two description signatures qualify as owned: the old template-only
     * closing sentence, and the attribution line every roster description
     * carries -- the second matters because a record upgraded under an
     * earlier roster misses whatever the roster learned afterwards (death
     * dates, exile ends, assumed custody spans, recropped photos).
     *
     * Returns 'refresh' when something needed updating, 'current' when the
     * record already matches the roster, and null when the record is not
     * this import's (a genuine name collision -- left alone).
     */
    private function refreshOwned(array $r, string $slug, bool $apply, int &$photosAttached): ?string
    {
        $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->with('cases')->first();
        if (! $p) {
            return null;
        }
        $desc = (string) $p->description;
        if (! str_contains($desc, 'Adapted from Kenyon Zimmer')
            && ! str_contains($desc, 'index of deportation case files')) {
            return null;
        }

        $aff = array_values(array_unique(array_merge($p->affiliation ?: [], $r['affiliations'] ?: [])));
        $ide = array_values(array_unique(array_merge($p->ideologies ?: [], $r['ideologies'] ?: [])));
        $c = $r['case'];
        $case = $p->cases->first(fn ($x) => str_contains((string) $x->charges, 'Red Scare Deportees index')) ?? $p->cases->first();
        $caseDates = [['arrest_date', $c['arrest']], ['incarceration_date', $c['incarceration']], ['release_date', $c['release']], ['in_exile_since', $c['exile_since']], ['end_of_exile', $c['exile_end'] ?? null]];

        $changes = [];
        if ($desc !== $r['description']) {
            $changes[] = 'description';
        }
        if ($r['birth_year'] && ! $p->birthdate) {
            $changes[] = 'birth '.$r['birth_year'];
        }
        if (! empty($r['death']) && ! $p->death_date) {
            $changes[] = 'death '.$r['death'][0];
        }
        if ($aff !== ($p->affiliation ?: [])) {
            $changes[] = 'affiliations';
        }
        if ($ide !== ($p->ideologies ?: [])) {
            $changes[] = 'ideologies';
        }
        if ($r['photo'] && $this->photoStale($p, $r['photo'])) {
            $changes[] = 'photo';
        }
        if ($case) {
            if ((string) $case->charges !== $c['charges'] || (string) $case->sentence !== $c['sentence']) {
                $changes[] = 'case text';
            }
            foreach ($caseDates as [$field, $val]) {
                if ($val && $this->caseDateYmd($case, $field) !== $this->rosterYmd($val)) {
                    $changes[] = $field;
                }
            }
        }

        if (! $changes) {
            return 'current';
        }
        $this->line('  refresh '.$p->slug.': '.implode(', ', $changes));
        if (! $apply) {
            return 'refresh';
        }

        $p->description = $r['description'];
        if ($r['birth_year'] && ! $p->birthdate) {
            $p->setPartialDate('birthdate', (int) $r['birth_year']);
        }
        if (! empty($r['death']) && ! $p->death_date) {
            $p->setPartialDate('death_date', ...array_map(fn ($x) => $x === null ? null : (int) $x, $r['death']));
        }
        $p->affiliation = $aff ?: null;
        $p->ideologies = $ide ?: null;
        $p->save();
        if (in_array('photo', $changes, true)) {
            $this->attachPhoto($p, $r['photo']) && $photosAttached++;
        }

        if ($case) {
            $case->charges = $c['charges'];
            $case->sentence = $c['sentence'];
            foreach ($caseDates as [$field, $val]) {
                if ($val) {
                    $case->setPartialDate($field, ...array_map(fn ($x) => $x === null ? null : (int) $x, $val));
                }
            }
            $case->save();
        }

        return 'refresh';
    }

    /** The stored value of a case date column as Y-m-d, null when unset. */
    private function caseDateYmd(object $case, string $field): ?string
    {
        $v = $case->{$field};
        if (! $v) {
            return null;
        }

        return ($v instanceof Carbon ? $v : Carbon::parse($v))->toDateString();
    }

    /** A roster [y, m, d] tuple as the Y-m-d string setPartialDate would store (missing parts default to 01). */
    private function rosterYmd(array $val): string
    {
        return sprintf('%04d-%02d-%02d', (int) $val[0], (int) ($val[1] ?? null ?: 1), (int) ($val[2] ?? null ?: 1));
    }

    /** True when the record's photo is missing on disk or its bytes differ from the roster file (recrops propagate). */
    private function photoStale(Prisoner $p, string $file): bool
    {
        $src = database_path('data/photos/zimmer/'.$file);
        if (! is_file($src)) {
            return false;
        }
        if (! $p->photo) {
            return true;
        }
        $dest = storage_path('app/public/'.$p->photo);

        return ! is_file($dest) || md5_file($dest) !== md5_file($src);
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
        if (! empty($r['death']) && ! $p->death_date) {
            $actions[] = 'death '.$r['death'][0];
        }
        $merged = array_values(array_unique(array_merge($p->affiliation ?: [], $r['affiliations'] ?: [])));
        if ($merged !== ($p->affiliation ?: [])) {
            $actions[] = 'affiliations';
        }

        // The index's biographical narrative, appended as a clearly
        // attributed supplement -- the existing bio is never replaced.
        // The narrative sits between the facts header and the attribution
        // line of the roster description; a 60-character probe keeps the
        // append idempotent.
        $narrative = $this->narrativeOf($r['description']);
        $probe = $narrative ? mb_substr($narrative, 0, 60) : null;
        if ($narrative && ! str_contains((string) $p->description, $probe)) {
            $actions[] = 'bio supplement';
        } else {
            $narrative = null;
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
            if (! empty($r['death']) && ! $p->death_date) {
                $p->setPartialDate('death_date', ...array_map(fn ($x) => $x === null ? null : (int) $x, $r['death']));
            }
            $p->affiliation = $merged ?: null;
            if ($narrative) {
                $p->description = rtrim((string) $p->description)
                    ."\n\nFrom Kenyon Zimmer's Red Scare Deportees index (kenyonzimmer.com, compiled from INS deportation case files): "
                    .$narrative;
            }
            $p->save();
        }

        return true;
    }

    /** The narrative paragraphs of a roster description: the attribution line and (when present) the facts header are stripped by pattern, not by position. */
    private function narrativeOf(string $description): ?string
    {
        $parts = explode("\n\n", $description);
        if ($parts && str_starts_with(end($parts), 'Adapted from Kenyon Zimmer')) {
            array_pop($parts);
        }
        if ($parts && mb_strlen($parts[0]) < 320
            && preg_match('/ was born |^Worked as a |^Immigrated to the United States/u', $parts[0])) {
            array_shift($parts);
        }
        $narr = trim(implode("\n\n", $parts));

        return $narr !== '' ? $narr : null;
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
