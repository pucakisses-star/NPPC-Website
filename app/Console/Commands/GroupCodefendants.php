<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Bring connected prisoners together in the sort order.
 *
 * The archive list is chronological, but co-defendants of the same case
 * often ended up hundreds or thousands of positions apart -- the four
 * Abrams v. United States defendants were scattered across 132 positions,
 * Michael Doyle sat 4,700 positions from the rest of the Camden 28, and
 * the Missouri Staats-Zeitung pair (fixed by hand first) were a thousand
 * apart. This generalizes that fix.
 *
 * WHAT COUNTS AS CONNECTED -- two signals, deliberately conservative:
 *
 *   1. Same-day co-defendants: records whose (day-precision) arrest dates
 *      are identical AND that share an affiliation, an institution, or at
 *      least three substantive words of their charge text. The date alone
 *      is never enough -- two unrelated people arrested the same day stay
 *      unrelated.
 *
 *   2. Named single-case groups: affiliations ending in a number or
 *      number word ("Camden 28", "San Quentin Six", "Kings Bay Plowshares
 *      7"), plus a short curated list of single-event groups without a
 *      number. Broad organizations (NAACP, UMWA, IWW, SDS...) are NOT
 *      grouped: their members belong to different events in different
 *      decades, and scattering them through the chronology is correct.
 *
 * THE MULTI-ERA GUARD. A member whose own cases span more than a few
 * years, or whose case years sit far from the cluster's event year, is
 * SKIPPED and reported, never moved -- Dorothy Day was a 1917 Silent
 * Sentinel, but her record spans six decades of arrests and her placement
 * among the Catholic Worker records is a choice, not an accident.
 *
 * HOW THE MOVE WORKS. Each scattered cluster keeps its largest contiguous
 * run as the anchor; the outliers are spliced in next to it. All moves
 * are simulated on the full ordered list in memory, and at the end each
 * record is reassigned a value from the ORIGINAL multiset of sort values
 * in the new order -- so sort values stay exactly as unique as they were,
 * and nothing outside the affected ranges changes.
 *
 * Dry-run by default:
 *
 *   php artisan prisoners:group-codefendants
 *   php artisan prisoners:group-codefendants --apply
 */
final class GroupCodefendants extends Command
{
    protected $signature = 'prisoners:group-codefendants {--apply : Save the changes}';

    protected $description = 'Bring co-defendants and named single-case groups together in the sort order';

    /** Affiliation name-forms that mark a single-case group. */
    private const NUMBERED = '/\b(?:\d+|Two|Three|Four|Five|Six|Seven|Eight|Nine|Ten|Eleven|Twelve|Thirteen|Fourteen|Fifteen|Twenty\w*)\s*$/';

    /** Single-event groups whose names carry no number. */
    private const EXTRA_GROUPS = [
        'Inmates for Action',
        'United Freedom Front',
    ];

    private const STOPWORDS = ['with', 'that', 'from', 'were', 'their', 'after', 'under', 'during', 'charged', 'arrested', 'federal', 'state', 'county', 'court', 'united', 'states'];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');

        $people = Prisoner::withoutGlobalScopes()
            ->with('cases')
            ->whereNotNull('sort_order')
            ->orderBy('sort_order')->orderBy('slug')
            ->get();

        $n = $people->count();
        $index = [];            // prisoner id -> position in $order
        $order = [];            // position -> prisoner id
        $info = [];             // prisoner id -> feature bundle
        $sortValues = [];       // original multiset of sort values, ascending

        foreach ($people as $i => $p) {
            $order[$i] = $p->id;
            $index[$p->id] = $i;
            $sortValues[$i] = (int) $p->sort_order;

            $years = [];
            $day = null;
            $institutions = [];
            $chargeTokens = [];
            foreach ($p->cases as $c) {
                foreach ([$c->arrest_date, $c->incarceration_date] as $dt) {
                    if ($dt) {
                        $years[] = (int) $dt->year;
                    }
                }
                if ($c->arrest_date && $c->datePrecisionFor('arrest_date') === 'day') {
                    $day = $day ?? $c->arrest_date->toDateString();
                }
                if ($c->institution_id) {
                    $institutions[] = $c->institution_id;
                }
                foreach (preg_split('/[^a-z]+/', strtolower((string) $c->charges)) as $w) {
                    if (strlen($w) >= 4 && ! in_array($w, self::STOPWORDS, true)) {
                        $chargeTokens[$w] = true;
                    }
                }
            }
            $info[$p->id] = [
                'slug' => $p->slug,
                'name' => $p->name,
                'aff' => $p->affiliation ?: [],
                'day' => $day,
                'years' => $years,
                'inst' => $institutions,
                'chg' => $chargeTokens,
            ];
        }

        // ---- build clusters (union-find over prisoner ids) -----------------
        $parent = [];
        $find = function ($x) use (&$parent, &$find) {
            while (($parent[$x] ?? $x) !== $x) {
                $parent[$x] = $parent[$parent[$x]] ?? $parent[$x];
                $x = $parent[$x];
            }

            return $x;
        };
        $touched = [];
        $union = function ($a, $b) use (&$parent, &$touched, $find) {
            $touched[$a] = true;
            $touched[$b] = true;
            $parent[$find($a)] = $find($b);
        };

        // 1) same-day co-defendants
        $byDay = [];
        foreach ($info as $id => $f) {
            if ($f['day']) {
                $byDay[$f['day']][] = $id;
            }
        }
        foreach ($byDay as $ids) {
            $m = count($ids);
            if ($m < 2 || $m > 60) {
                continue;
            }
            for ($i = 0; $i < $m; $i++) {
                for ($j = $i + 1; $j < $m; $j++) {
                    $a = $info[$ids[$i]];
                    $b = $info[$ids[$j]];
                    $linked = array_intersect($a['aff'], $b['aff'])
                        || array_intersect($a['inst'], $b['inst'])
                        || count(array_intersect_key($a['chg'], $b['chg'])) >= 3;
                    if ($linked) {
                        $union($ids[$i], $ids[$j]);
                    }
                }
            }
        }

        // 2) named single-case affiliation groups
        $byAff = [];
        foreach ($info as $id => $f) {
            foreach ($f['aff'] as $a) {
                $byAff[$a][] = $id;
            }
        }
        foreach ($byAff as $affName => $ids) {
            $single = preg_match(self::NUMBERED, $affName) || in_array($affName, self::EXTRA_GROUPS, true);
            if (! $single || count($ids) < 2 || count($ids) > 60) {
                continue;
            }
            for ($i = 1, $m = count($ids); $i < $m; $i++) {
                $union($ids[0], $ids[$i]);
            }
        }

        $clusters = [];
        foreach (array_keys($touched) as $id) {
            $clusters[$find($id)][] = $id;
        }

        // ---- plan the moves ------------------------------------------------
        $movedTotal = 0;
        $clustersFixed = 0;
        $skips = [];

        uasort($clusters, fn ($a, $b) => min(array_map(fn ($id) => $index[$id], $a)) <=> min(array_map(fn ($id) => $index[$id], $b)));

        foreach ($clusters as $members) {
            if (count($members) < 2) {
                continue;
            }

            // A merged cluster can span eras -- Elizabeth McAlister's 1971
            // Harrisburg arrest chains the Kings Bay 7 to the Harrisburg
            // cluster. Split into year-cohort BUCKETS (sub-events) and
            // regroup each independently; a member whose own record spans
            // eras is never moved at all.
            $buckets = [];
            $yearless = [];
            foreach ($members as $id) {
                $ys = $info[$id]['years'];
                if (! $ys) {
                    $yearless[] = $id;
                    continue;
                }
                if (max($ys) - min($ys) > 4) {
                    $skips[] = "{$info[$id]['slug']}: record spans ".min($ys).'-'.max($ys).', left where it is';
                    continue;
                }
                $y = min($ys);
                $placed = false;
                foreach (array_keys($buckets) as $by) {
                    if (abs($by - $y) <= 3) {
                        $buckets[$by][] = $id;
                        $placed = true;
                        break;
                    }
                }
                if (! $placed) {
                    $buckets[$y] = [$id];
                }
            }

            // A dateless member follows its named group: it joins the bucket
            // holding the most members of a numbered affiliation it carries.
            foreach ($yearless as $id) {
                $home = null;
                $best = 0;
                foreach ($info[$id]['aff'] as $a) {
                    if (! preg_match(self::NUMBERED, $a) && ! in_array($a, self::EXTRA_GROUPS, true)) {
                        continue;
                    }
                    foreach ($buckets as $by => $ids) {
                        $n2 = count(array_filter($ids, fn ($m) => in_array($a, $info[$m]['aff'], true)));
                        if ($n2 > $best) {
                            $best = $n2;
                            $home = $by;
                        }
                    }
                }
                if ($home !== null) {
                    $buckets[$home][] = $id;
                } elseif (count($buckets) === 1) {
                    $buckets[array_key_first($buckets)][] = $id;
                } elseif (count($buckets) > 1) {
                    $skips[] = "{$info[$id]['slug']}: no case dates in a multi-era cluster, left where it is";
                }
            }

            foreach ($buckets as $eligible) {
                $this->regroup($eligible, $info, $order, $index, $sortValues, $movedTotal, $clustersFixed);
            }
        }

        if ($skips) {
            $this->warn('Skipped, never moved (multi-era or undatable records):');
            foreach (array_unique($skips) as $s) {
                $this->warn('  '.$s);
            }
            $this->newLine();
        }

        // ---- persist -------------------------------------------------------
        $changed = [];
        foreach ($order as $pos => $id) {
            if ($people->firstWhere('id', $id)->sort_order !== $sortValues[$pos]) {
                $changed[$id] = $sortValues[$pos];
            }
        }

        $this->info("{$clustersFixed} cluster(s) regrouped, {$movedTotal} member move(s), ".count($changed).' row(s) get a new sort value.');

        if (! $apply) {
            $this->info('Dry run. Re-run with --apply to save.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($changed) {
            foreach ($changed as $id => $sort) {
                DB::table('prisoners')->where('id', $id)->update(['sort_order' => $sort]);
            }
        });

        $dupes = Prisoner::withoutGlobalScopes()
            ->selectRaw('sort_order, COUNT(*) c')
            ->groupBy('sort_order')->havingRaw('COUNT(*) > 1')->count();
        $this->info($dupes > 1 ? "NOTE: {$dupes} duplicated sort value(s) (one pre-existing duplicate is known)." : 'Sort values verified unique.');

        Cache::forget(PrisonerApiController::cacheKey());
        $this->info('Applied.');

        return self::SUCCESS;
    }

    /** Move one year-cohort of a cluster together, anchored at its largest contiguous run. */
    private function regroup(array $eligible, array $info, array &$order, array &$index, array $sortValues, int &$movedTotal, int &$clustersFixed): void
    {
        if (count($eligible) < 2) {
            return;
        }

        $positions = array_map(fn ($id) => $index[$id], $eligible);
        sort($positions);
        $span = end($positions) - $positions[0];
        if ($span <= count($positions) + 3) {
            return; // already effectively together
        }

        // anchor = longest contiguous (or near-contiguous) run of positions
        $runs = [[]];
        foreach ($positions as $pos) {
            $current = &$runs[count($runs) - 1];
            if ($current && $pos - end($current) > 2) {
                $runs[] = [$pos];
            } else {
                $current[] = $pos;
            }
            unset($current);
        }
        usort($runs, fn ($a, $b) => count($b) <=> count($a));
        $anchor = $runs[0];
        $anchorEnd = end($anchor);

        // label for the receipt
        $common = array_values(array_filter(count($eligible) > 1
            ? array_intersect(...array_map(fn ($id) => $info[$id]['aff'] ?: [''], $eligible))
            : []));
        $label = $common ? implode(', ', $common) : 'arrested '.($info[$eligible[0]]['day'] ?? '?');
        $this->line($label.'  ('.count($eligible).' members)');

        $before = [];
        foreach ($eligible as $id) {
            $before[$id] = $sortValues[$index[$id]];
        }

        // splice each out-of-run member in after the anchor's end
        $moves = 0;
        foreach ($eligible as $id) {
            $pos = $index[$id];
            if ($pos >= $anchor[0] && $pos <= $anchorEnd) {
                continue;
            }
            $target = $anchorEnd + ($pos > $anchorEnd ? 1 : 0);
            array_splice($order, $pos, 1);
            array_splice($order, $target, 0, [$id]);
            $lo = min($pos, $target);
            $hi = max($pos, $target);
            for ($i = $lo; $i <= $hi; $i++) {
                $index[$order[$i]] = $i;
            }
            $anchorEnd = $index[$id];
            $moves++;
        }

        foreach ($eligible as $id) {
            $newSort = $sortValues[$index[$id]];
            $flag = $newSort === $before[$id] ? '' : '   <- was '.$before[$id];
            $this->line('    '.str_pad((string) $newSort, 6).$info[$id]['slug'].$flag);
        }
        $this->newLine();
        $movedTotal += $moves;
        $clustersFixed++;
    }
}
