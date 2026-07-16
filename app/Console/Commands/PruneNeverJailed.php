<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\CalendarEntry;
use App\Models\PodcastEpisode;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Removes records identified by the "never spent a day in custody" audit — a
 * two-pass review (six reviewers over 228 candidates, then a verification pass
 * over 24 borderline cases) of everyone in the database with no imprisonment
 * data whose bio also indicated no jail time. Only the 25 confirmed as NEVER
 * arrested/booked/held are listed here; anyone held even briefly pre-trial
 * (the large majority, including acquittals) was excluded.
 *
 * DRY-RUN by default — prints exactly who would be deleted, grouped by
 * category. Pass --apply to delete. Pass --only=cat1,cat2 to restrict to
 * specific categories (see keys of CATEGORIES). Deleting a prisoner also
 * deletes its cases, podcast episodes, and calendar entries. Idempotent:
 * once a record is gone it is silently skipped.
 *
 * NOTE two categories are editorial judgment calls, surfaced deliberately:
 *   - "killed" are victims of political violence who were never prosecuted
 *     (the 1979 Greensboro massacre dead; Groveland Four's Ernest Thomas);
 *   - "contempt" includes historically significant figures (Owen Lattimore,
 *     James Matles, Marcus Raskin, etc.) who were subpoenaed/indicted but
 *     never detained.
 */
final class PruneNeverJailed extends Command
{
    protected $signature = 'prisoners:prune-never-jailed {--apply : Actually delete} {--only= : Comma-separated category keys to restrict to}';

    protected $description = 'Delete records for people the audit confirmed never spent any time in custody (dry-run by default)';

    /** @var array<string,array<int,string>> */
    private const CATEGORIES = [
        // Killed by police/vigilantes; never charged or detained (victims/martyrs).
        'killed' => [
            'cesar-cauce', 'james-waller', 'michael-nathan', 'sandra-neely-smith',
            'william-evan-sampson', 'ernest-thomas',
        ],
        // Subpoenaed/indicted for contempt, perjury, tax, or sedition-adjacent
        // charges; acquitted, fined, or dropped — never arrested or held.
        'contempt' => [
            'louise-berman', 'marcel-scherer', 'esther-vice', 'frank-panzino',
            'james-matles', 'talmadge-raley', 'thomas-fitzpatrick', 'reuel-stanfield',
            'sidney-buchman', 'lee-lorch', 'owen-lattimore', 'vivian-hallinan',
            'maurice-travis', 'fred-d-gray',
        ],
        // Convicted/indicted but free on bail the entire time; never served a day.
        'never-served' => [
            'michael-ferber', 'marcus-raskin',
        ],
        // Other confirmed non-custodial outcomes.
        'other' => [
            'ehren-watada',              // court-martial mistrial; never confined
            'leo-sheiner',               // disbarred only
            'american-socialist-society', // an organization, cannot be jailed
        ],
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $only = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('only')))));

        $deleted = 0;
        $missing = 0;

        foreach (self::CATEGORIES as $cat => $slugs) {
            if ($only && ! in_array($cat, $only, true)) {
                continue;
            }

            $this->newLine();
            $this->info("== {$cat} ==");

            foreach ($slugs as $slug) {
                $p = Prisoner::withUnderReview()->where('slug', $slug)->first();
                if (! $p) {
                    $this->line("  · {$slug} — already gone");
                    $missing++;

                    continue;
                }

                $nCases = PrisonerCase::where('prisoner_id', $p->id)->count();
                $nPods = PodcastEpisode::where('prisoner_id', $p->id)->count();
                $nCal = CalendarEntry::where('prisoner_id', $p->id)->count();

                if (! $apply) {
                    $this->line("  WOULD DELETE  {$p->name}  (/prisoner/{$slug})  [cases={$nCases} podcasts={$nPods} calendar={$nCal}]");

                    continue;
                }

                DB::transaction(function () use ($p) {
                    PrisonerCase::where('prisoner_id', $p->id)->delete();
                    PodcastEpisode::where('prisoner_id', $p->id)->delete();
                    CalendarEntry::where('prisoner_id', $p->id)->delete();
                    $p->delete();
                });

                $this->line("  DELETED  {$p->name}  (/prisoner/{$slug})");
                $deleted++;
            }
        }

        if ($apply && $deleted > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->newLine();
        if ($apply) {
            $this->info("Done. Deleted {$deleted} record(s); {$missing} already gone.");
        } else {
            $this->warn('Dry-run only — nothing deleted. Re-run with --apply to delete (optionally --only=killed,contempt,never-served,other).');
        }

        return self::SUCCESS;
    }
}
