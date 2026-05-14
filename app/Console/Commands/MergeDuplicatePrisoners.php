<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\PodcastEpisode;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merge confirmed duplicate prisoner records surfaced by
 * `prisoners:audit-duplicates`. Pairs were reviewed individually;
 * the William E. / William M. Martin pair is intentionally NOT
 * merged because the differing middle initials and states indicate
 * two distinct people sharing a birthdate.
 *
 * For each group, the canonical slug is kept and the duplicates
 * are folded in:
 *
 *   - All prisoner_cases rows have their prisoner_id reassigned.
 *   - All podcast_episodes rows have their prisoner_id reassigned.
 *   - All calendar_entries rows have their prisoner_id reassigned.
 *   - Scalar fields on the canonical that are NULL/empty are
 *     populated from the duplicate.
 *   - The duplicate's aka is folded into the canonical's aka
 *     (deduped).
 *   - Array fields (ideologies, affiliation) are unioned.
 *   - The duplicate row is then deleted.
 *
 * Dry-run by default; --apply writes. Idempotent: if the duplicate
 * has already been merged the group is skipped silently.
 */
final class MergeDuplicatePrisoners extends Command {
    protected $signature = 'prisoners:merge-duplicates {--apply : Actually perform the merges}';
    protected $description = 'Merge confirmed duplicate prisoner records into a single canonical slug.';

    /**
     * Merge groups: [canonical_slug, [duplicate_slug, ...]]
     * Canonical chosen as the better-known / more-canonical URL.
     */
    private array $groups = [
        ['eugene-debs',                  ['eugene-victor-debs']],
        ['william-dudley-haywood',       ['bill-haywood', 'william-d-big-bill-haywood']],
        ['ricardo-flores-magon',         ['ricardo-flores-magon-2']],
        ['thomas-mooney',                ['tom-mooney']],
        ['jacob-stachel',                ['jack-stachel']],
        ['benjamin-j-davis-jr',          ['benjamin-j-davis']],
        ['henry-winston',                ['henry-m-winston']],
        ['filiberto-ojeda-rios',         ['filiberto-ojeda-rios-2']],
        ['sundiata-acoli',               ['clark-squire']],
        ['basheer-hameed',               ['bashir-hameed']],
        ['jim-forest',                   ['james-forest']],
        ['oscar-lopez-rivera',           ['oscar-lopez-rivera-2']],
        ['jamil-abdullah-al-amin',       ['jamil-abdullah-al-amin-2']],
        ['bill-ayers',                   ['william-charles-ayers']],
        ['william-taylor-harris',        ['bill-harris']],
        ['anna-mae-pictou-aquash',       ['anna-mae-aquash']],
        ['thomas-william-manning',       ['tom-manning']],
        ['dylcia-pagan',                 ['dylcia-pagan-2']],
        ['mark-rudd',                    ['mark-william-rudd']],
        ['elmer-geronimo-pratt',         ['geronimo-pratt']],
        ['jaan-laaman',                  ['jaan-karl-laaman']],
        ['sekou-kambui',                 ['william-j-turk']],
        ['abdul-majid',                  ['anthony-laborde']],
        ['judith-clark',                 ['judith-a-clark']],
        ['joseph-patrick-doherty',       ['joe-doherty', 'joseph-doherty']],
        ['gerardo-hernandez-nordelo',    ['gerardo-hernandez']],
        ['fernando-gonzalez-llort',      ['fernando-gonzalez']],
        ['christina-reid',               ['christina-l-reid']],
        ['douglas-l-wright',             ['douglas-wright']],
    ];

    public function handle(): int {
        $apply = (bool) $this->option('apply');
        $merged = 0;
        $skipped = 0;

        foreach ($this->groups as [$canonicalSlug, $dupSlugs]) {
            $canonical = Prisoner::where('slug', $canonicalSlug)->first();
            if (! $canonical) {
                $this->warn("MISS canonical /prisoner/{$canonicalSlug} — skipping group.");
                $skipped++;
                continue;
            }

            foreach ($dupSlugs as $dupSlug) {
                $dup = Prisoner::where('slug', $dupSlug)->first();
                if (! $dup) {
                    $this->line("  -- already merged or missing: /prisoner/{$dupSlug}");
                    continue;
                }
                if ($dup->id === $canonical->id) {
                    continue;
                }

                $caseCount     = PrisonerCase::where('prisoner_id', $dup->id)->count();
                $podcastCount  = PodcastEpisode::where('prisoner_id', $dup->id)->count();
                $calendarCount = CalendarEntry::where('prisoner_id', $dup->id)->count();

                $this->info("MERGE  /prisoner/{$dupSlug}  →  /prisoner/{$canonicalSlug}");
                $this->line("   cases={$caseCount}  podcasts={$podcastCount}  calendar={$calendarCount}");

                if (! $apply) {
                    continue;
                }

                DB::transaction(function () use ($canonical, $dup) {
                    PrisonerCase::where('prisoner_id', $dup->id)->update(['prisoner_id' => $canonical->id]);
                    PodcastEpisode::where('prisoner_id', $dup->id)->update(['prisoner_id' => $canonical->id]);
                    CalendarEntry::where('prisoner_id', $dup->id)->update(['prisoner_id' => $canonical->id]);

                    // Backfill scalar fields on canonical from dup where canonical is empty.
                    $scalarFields = [
                        'photo', 'description', 'state', 'address', 'lat', 'lng',
                        'first_name', 'middle_name', 'last_name', 'race', 'gender',
                        'birthdate', 'death_date', 'era', 'website', 'twitter',
                        'facebook', 'instagram', 'inmate_number',
                    ];
                    $dirty = false;
                    foreach ($scalarFields as $f) {
                        $cv = $canonical->{$f};
                        $dv = $dup->{$f};
                        if (($cv === null || $cv === '') && $dv !== null && $dv !== '') {
                            $canonical->{$f} = $dv;
                            $dirty = true;
                        }
                    }

                    // Merge aka (string, slash-separated).
                    $akaParts = collect(preg_split('/\s*[\/;]\s*/', (string) $canonical->aka))
                        ->merge(preg_split('/\s*[\/;]\s*/', (string) $dup->aka))
                        ->merge([$dup->name])
                        ->map(fn ($s) => trim((string) $s))
                        ->filter()
                        ->filter(fn ($s) => mb_strtolower($s) !== mb_strtolower($canonical->name))
                        ->unique(fn ($s) => mb_strtolower($s))
                        ->values()
                        ->all();
                    $newAka = implode(' / ', $akaParts);
                    if ($newAka !== (string) $canonical->aka) {
                        $canonical->aka = $newAka === '' ? null : $newAka;
                        $dirty = true;
                    }

                    // Merge array fields (ideologies, affiliation).
                    foreach (['ideologies', 'affiliation'] as $f) {
                        $merged = collect((array) $canonical->{$f})
                            ->merge((array) $dup->{$f})
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                        if ($merged !== (array) $canonical->{$f}) {
                            $canonical->{$f} = $merged;
                            $dirty = true;
                        }
                    }

                    // OR boolean status flags so the canonical reflects
                    // any "active" signal that lived only on the dup.
                    foreach (['in_custody', 'released', 'in_exile', 'currently_in_exile', 'awaiting_trial'] as $f) {
                        if (! $canonical->{$f} && $dup->{$f}) {
                            $canonical->{$f} = true;
                            $dirty = true;
                        }
                    }

                    if ($dirty) {
                        $canonical->save();
                    }

                    $dup->delete();
                });

                $merged++;
            }
        }

        $this->line('');
        if ($apply) {
            $this->info("Done. Merged {$merged} duplicate(s); skipped {$skipped} group(s).");
        } else {
            $this->info("Plan: {$merged} merge(s); {$skipped} group(s) skipped (missing canonical).");
            $this->info('(dry-run; re-run with --apply to write)');
        }

        return self::SUCCESS;
    }
}
