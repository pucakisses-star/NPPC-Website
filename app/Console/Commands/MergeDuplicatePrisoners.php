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
 * The AIM Pine Ridge co-defendant pairs (Robert "Bob" Robideau and
 * Darrelle "Dino" Butler) were added after confirming each is one
 * person split across a formal-name and a nickname record; the
 * canonical keeps the fuller legal-name slug (matching the existing
 * anna-mae-pictou-aquash choice). For those three AIM pairs the
 * duplicate's cases are redundant, less-complete copies of the same
 * 1975-76 RESMURS acquittal already on the canonical, so they are
 * dropped rather than reassigned (see $dropDupCasesFor).
 *
 * Pass --only=slug1,slug2 to restrict a run to specific canonicals.
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
final class MergeDuplicatePrisoners extends Command
{
    protected $signature = 'prisoners:merge-duplicates {--apply : Actually perform the merges} {--only= : Comma-separated canonical slugs to restrict the run to}';

    protected $description = 'Merge confirmed duplicate prisoner records into a single canonical slug.';

    /**
     * Merge groups: [canonical_slug, [duplicate_slug, ...]]
     * Canonical chosen as the better-known / more-canonical URL.
     */
    private array $groups = [
        ['eugene-debs',                  ['eugene-victor-debs']],
        ['william-dudley-haywood',       ['bill-haywood', 'william-d-big-bill-haywood', 'w-d-haywood']],
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
        ['robert-robideau',              ['bob-robideau']],
        ['darrelle-dean-butler',         ['dino-butler']],
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
        // Prairieland defendants entered twice — once under their legal name,
        // once under the name they go by. Canonical keeps the legal-name slug
        // (which holds the sentenced case); the duplicate's stub case is dropped.
        ['cameron-arnold',               ['autumn-hill']],
        ['daniel-sanchez-estrada',       ['daniel-rolando-sanchez-estrada']],
        // Duplicates surfaced by the confirmed-identity photo-research pass —
        // each is one person entered twice (a legal/alternate name and the
        // name they go by). The canonical keeps the photographed record; the
        // duplicate's name is folded into aka and, where it holds a redundant
        // copy of the same arrest, its case is dropped (see $dropDupCasesFor).
        ['daniel-alan-baker',            ['dan-baker']],
        ['cara-mitrano',                 ['cara-tobe']],
        ['celeste-legere',               ['celeste-friend']],
        ['anthony-smith',                ['anthony-david-ale-smith']],
        ['branden-wolfe',                ['branden-michael-wolfe']],
        ['carlos-matchett',              ['carlos-a-matchett']],
        ['charles-pittman',              ['charles-anthony-pittman']],
        ['christopher-rojas',            ['christopher-isidro-rojas']],
        ['cyan-bass',                    ['cyan-waters-bass']],
        ['dakotah-horton',               ['dakotah-ray-horton']],
        // Name-variant duplicates surfaced by the historical (IWW / early-labor
        // / Socialist-era) photo-research pass. Canonical keeps the photographed
        // record; the variant name folds into aka.
        ['burt-lorton',                  ['bert-lorton']],
        ['marie-equi',                   ['dr-marie-d-equi']],
        ['louis-parenti',                ['luigi-parenti']],
        ['victor-berger',                ['victor-l-berger']],
        ['annie-arniel',                 ['annie-melvin-arniel']],
        ['ben-salmon',                   ['benjamin-j-salmon']],
        ['hulet-m-wells',                ['hiulet-m-wells']],
        ['j-h-beyer',                    ['j-h-byers']],
        ['james-franklin-melton',        ['jas-franklin-melton']],
        ['otto-janson',                  ['otto-jansen']],
        ['william-ehrhard',              ['william-ehrhardt']],
    ];

    /**
     * Canonicals whose duplicate records carry only redundant, less-complete
     * copies of a case already held (in fuller form) by the canonical. For
     * these the duplicate's cases are deleted rather than reassigned, so the
     * merged record does not end up with two near-identical rows for the same
     * event. Verified individually against production for each listed pair.
     */
    private array $dropDupCasesFor = [
        'anna-mae-pictou-aquash',
        'robert-robideau',
        'darrelle-dean-butler',
        // Prairieland: the duplicate's case is a less-complete stub of the
        // sentenced case already held by the canonical, so drop it.
        'cameron-arnold',
        'daniel-sanchez-estrada',
        // Photo-research duplicates whose dup carries a redundant copy of the
        // same arrest already held by the canonical — drop the dup's case
        // rather than leave the merged record with two near-identical rows.
        'daniel-alan-baker',
        'cara-mitrano',
        'celeste-legere',
        'christopher-rojas',
        // Historical name-variant duplicates: the variant's case (where any) is
        // a redundant copy of the same Espionage-Act / IWW conviction already on
        // the canonical, so drop it instead of duplicating the row.
        'william-dudley-haywood',
        'burt-lorton',
        'marie-equi',
        'louis-parenti',
        'victor-berger',
        'annie-arniel',
        'ben-salmon',
        'hulet-m-wells',
        'j-h-beyer',
        'james-franklin-melton',
        'otto-janson',
        'william-ehrhard',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $only = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('only')))));
        $merged = 0;
        $skipped = 0;

        foreach ($this->groups as [$canonicalSlug, $dupSlugs]) {
            if ($only && ! in_array($canonicalSlug, $only, true)) {
                continue;
            }

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

                $caseCount = PrisonerCase::where('prisoner_id', $dup->id)->count();
                $podcastCount = PodcastEpisode::where('prisoner_id', $dup->id)->count();
                $calendarCount = CalendarEntry::where('prisoner_id', $dup->id)->count();

                $this->info("MERGE  /prisoner/{$dupSlug}  →  /prisoner/{$canonicalSlug}");
                $this->line("   cases={$caseCount}  podcasts={$podcastCount}  calendar={$calendarCount}");

                if (! $apply) {
                    continue;
                }

                DB::transaction(function () use ($canonical, $dup, $canonicalSlug) {
                    if (in_array($canonicalSlug, $this->dropDupCasesFor, true)
                        && PrisonerCase::where('prisoner_id', $canonical->id)->exists()) {
                        // Canonical already holds the authoritative, more complete
                        // case(s); the duplicate's are redundant copies — drop them.
                        PrisonerCase::where('prisoner_id', $dup->id)->delete();
                    } else {
                        PrisonerCase::where('prisoner_id', $dup->id)->update(['prisoner_id' => $canonical->id]);
                    }
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
                            // Partial dates carry a per-field precision; copy it
                            // across too so a backfilled birthdate/death_date
                            // still renders (the API only shows day-precision).
                            if (in_array($f, ['birthdate', 'death_date'], true)) {
                                $dupPrecision = $dup->date_precision[$f] ?? null;
                                if ($dupPrecision !== null) {
                                    $canonical->date_precision = array_merge(
                                        $canonical->date_precision ?? [],
                                        [$f => $dupPrecision],
                                    );
                                }
                            }
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
