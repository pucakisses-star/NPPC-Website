<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Carbon\Carbon;
use Illuminate\Console\Command;

/**
 * Places prisoners sitting at sort_order 0 by slotting each into the
 * already-positioned cluster that shares its affiliation, in chronological
 * order. For each affiliation group of unplaced prisoners it finds the highest
 * sort_order among positioned (sort_order != 0) prisoners with that affiliation
 * and inserts the group's members right after it, oldest first, chaining so the
 * group stays contiguous. Prisoners with no affiliation that matches any
 * positioned cluster are reported as unanchored and left untouched (handle
 * those via prisoners:place-zero-sort anchor rules, e.g. the 1700s colonial
 * block). Idempotent; supports --dry-run.
 */
final class AutoPlaceZeroSort extends Command
{
    protected $signature = 'prisoners:auto-place-zero-sort {--dry-run : Preview without writing}';

    protected $description = 'Slot sort_order=0 prisoners into their affiliation cluster, chronologically';

    private function yearOf(Prisoner $p): int
    {
        $year = null;
        foreach ($p->cases as $c) {
            foreach (['incarceration_date', 'arrest_date', 'sentenced_date', 'in_exile_since'] as $f) {
                if ($c->{$f}) {
                    $y = (int) Carbon::parse($c->{$f})->year;
                    if ($y > 1000) { $year = $year ? min($year, $y) : $y; }
                }
            }
        }
        if (! $year && $p->era && preg_match('/\d{4}/', $p->era, $m)) { $year = (int) $m[0]; }

        return $year ?: 9999;
    }

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $all = Prisoner::withoutGlobalScopes()->with('cases')->get();

        // Max positioned sort_order per affiliation.
        $affMax = [];
        foreach ($all as $p) {
            if ($p->sort_order != 0 && is_array($p->affiliation)) {
                foreach ($p->affiliation as $a) {
                    $affMax[$a] = max($affMax[$a] ?? -1, $p->sort_order);
                }
            }
        }

        // Group the zero-sort prisoners by their best-anchored affiliation.
        $groups = [];
        $unanchored = [];
        foreach ($all->where('sort_order', 0) as $z) {
            $affs = is_array($z->affiliation) ? array_values(array_filter($z->affiliation, fn ($a) => isset($affMax[$a]))) : [];
            if (! $affs) { $unanchored[] = $z; continue; }
            usort($affs, fn ($a, $b) => $affMax[$b] <=> $affMax[$a]);
            $groups[$affs[0]][] = $z;
        }

        // Order affiliations by their anchor position so output reads top-down.
        uksort($groups, fn ($a, $b) => $affMax[$a] <=> $affMax[$b]);

        $placed = 0;
        foreach ($groups as $aff => $members) {
            usort($members, fn ($a, $b) => [$this->yearOf($a), $a->name] <=> [$this->yearOf($b), $b->name]);
            $this->line("\n=== {$aff}: ".count($members)." to place (cluster max sort ".$affMax[$aff].") ===");
            foreach ($members as $m) {
                $yr = $this->yearOf($m);
                $this->line('  '.($yr === 9999 ? '----' : $yr).'  '.$m->slug);
                if (! $dry) {
                    $anchorSort = (int) Prisoner::withoutGlobalScopes()
                        ->where('sort_order', '!=', 0)
                        ->where('affiliation', 'like', '%'.$aff.'%')
                        ->max('sort_order');
                    $newSort = $anchorSort + 1;
                    Prisoner::withoutGlobalScopes()
                        ->where('id', '!=', $m->id)
                        ->where('sort_order', '>=', $newSort)
                        ->increment('sort_order');
                    $m->sort_order = $newSort;
                    $m->save();
                }
                $placed++;
            }
        }

        if ($unanchored) {
            $this->newLine();
            $this->warn('Unanchored (no positioned cluster for their affiliation) — left at sort 0: '.count($unanchored));
            foreach ($unanchored as $u) {
                $aff = (is_array($u->affiliation) && $u->affiliation) ? $u->affiliation[0] : '(none)';
                $this->line('  '.str_pad($u->slug, 34).' | '.$aff.' | '.($u->era ?: '-'));
            }
        }

        if (! $dry) {
            \Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
        }
        $this->newLine();
        $this->info(($dry ? 'DRY RUN — ' : '')."would place {$placed}, unanchored ".count($unanchored).'.');
        if ($dry) { $this->warn('No changes written. Re-run without --dry-run to apply.'); }

        return self::SUCCESS;
    }
}
