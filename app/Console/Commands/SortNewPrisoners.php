<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Slot every prisoner with sort_order 0 (or null) into its appropriate
 * place in the curated archive order.
 *
 * The archive listing is ordered by sort_order, roughly newest-first,
 * but the sequence is curated and only loosely chronological — eras
 * interleave hundreds of times, while same-event cohorts sit adjacent.
 * A naive "insert at the first position whose date is older" would
 * therefore scatter records far from their cohorts. Instead each
 * unsorted record is placed immediately AFTER the already-sorted
 * record whose own date is NEAREST to its date — which lands cohort
 * members next to their co-defendants (an exact date match is, by
 * construction, the nearest anchor).
 *
 * A record's date key is its earliest case's arrest date, falling back
 * to incarceration, sentenced, then release date; a record with no
 * case dates at all falls back to the midpoint of its era ("1960s" ->
 * 1965-01-01), and a record with neither dates nor era goes to the end
 * of the list, reported loudly.
 *
 * Unsorted records are processed oldest-first so that same-date
 * cohorts chain: once the first member is placed it becomes a
 * legitimate anchor for the next.
 *
 * Idempotent: a second run finds no zero rows and changes nothing.
 * Run with --dry-run to see the placements without writing.
 */
final class SortNewPrisoners extends Command
{
    protected $signature = 'prisoners:sort-new {--dry-run : Report placements without writing}';

    protected $description = 'Insert prisoners with sort_order 0 into their place in the curated archive order';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $dateKey = function (Prisoner $p): ?string {
            $best = null;
            foreach ($p->cases as $c) {
                foreach (['arrest_date', 'incarceration_date', 'sentenced_date', 'release_date'] as $f) {
                    if ($c->{$f}) {
                        $d = $c->{$f}->format('Y-m-d');
                        if ($best === null || $d < $best) {
                            $best = $d;
                        }

                        break; // first present field of this case wins
                    }
                }
            }

            if ($best !== null) {
                return $best;
            }

            if ($p->era && preg_match('/^(\d{4})s$/', $p->era, $m)) {
                return sprintf('%04d-01-01', (int) $m[1] + 5);
            }

            return null;
        };

        $sorted = Prisoner::withUnderReview()
            ->where('sort_order', '>', 0)
            ->with('cases')
            ->get();

        $anchors = [];
        foreach ($sorted as $p) {
            $k = $dateKey($p);
            if ($k !== null) {
                $anchors[] = ['ts' => strtotime($k), 'sort' => $p->sort_order, 'slug' => $p->slug];
            }
        }

        if (! $anchors) {
            $this->error('No sorted, dated records to anchor against — nothing done.');

            return self::FAILURE;
        }

        $maxSort = (int) Prisoner::withUnderReview()->max('sort_order');

        $unsorted = Prisoner::withUnderReview()
            ->where(fn ($q) => $q->where('sort_order', 0)->orWhereNull('sort_order'))
            ->with('cases')
            ->get()
            ->map(fn ($p) => ['p' => $p, 'key' => $dateKey($p)])
            ->sortBy(fn ($row) => $row['key'] ?? '9999-99-99')
            ->values();

        if ($unsorted->isEmpty()) {
            $this->info('No records with sort_order 0 — nothing to do.');

            return self::SUCCESS;
        }

        $this->info(($dry ? '[DRY RUN] ' : '').'Placing '.$unsorted->count().' unsorted record(s) among '.$sorted->count().' sorted ones.');

        foreach ($unsorted as $row) {
            $p = $row['p'];
            $key = $row['key'];

            if ($key === null) {
                $maxSort++;
                $this->warn(sprintf('%-36s NO DATES AND NO ERA — appended at the end (sort %d)', $p->slug, $maxSort));
                if (! $dry) {
                    DB::table('prisoners')->where('id', $p->id)->update(['sort_order' => $maxSort]);
                }

                continue;
            }

            $ts = strtotime($key);
            $bestAnchor = null;
            $bestDiff = PHP_INT_MAX;
            foreach ($anchors as $a) {
                $diff = abs($a['ts'] - $ts);
                if ($diff < $bestDiff || ($diff === $bestDiff && $bestAnchor && $a['sort'] < $bestAnchor['sort'])) {
                    $bestDiff = $diff;
                    $bestAnchor = $a;
                }
            }

            $pos = $bestAnchor['sort'] + 1;

            if (! $dry) {
                DB::table('prisoners')->where('sort_order', '>=', $pos)->increment('sort_order');
                DB::table('prisoners')->where('id', $p->id)->update(['sort_order' => $pos]);
            }

            // keep the in-memory anchor list consistent with the shift
            foreach ($anchors as &$a) {
                if ($a['sort'] >= $pos) {
                    $a['sort']++;
                }
            }
            unset($a);
            $anchors[] = ['ts' => $ts, 'sort' => $pos, 'slug' => $p->slug];
            $maxSort++;

            $this->line(sprintf(
                '%-36s %s  -> sort %-5d  (after %s, %s, %s day(s) apart)',
                $p->slug,
                $key,
                $pos,
                $bestAnchor['slug'],
                date('Y-m-d', $bestAnchor['ts']),
                intdiv($bestDiff, 86400)
            ));
        }

        if (! $dry) {
            \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
            $this->info('Done — cache cleared.');
        } else {
            $this->info('Dry run — nothing written.');
        }

        return self::SUCCESS;
    }
}
