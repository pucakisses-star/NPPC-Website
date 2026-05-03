<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PlaceThreePrisoners extends Command
{
    protected $signature = 'prisoners:place-three {--dry-run : Show planned changes without writing}';
    protected $description = 'Move Charles Littlejohn, Ashanti Alston, and Tarik Shah into appropriate sort_order positions among existing prisoners.';

    /**
     * Each entry: [slug to move, anchor slug → place immediately after this prisoner].
     *
     * Anchors chosen to fit thematically:
     *  - Charles Littlejohn (2020s tax-records leak): with the recent
     *    Espionage-Act-adjacent leakers, immediately after Daniel E. Hale.
     *  - Ashanti Alston (1970s BLA bank robbery, 11 years served, later
     *    Black-anarchist organizer): with the BLA cluster, immediately
     *    after Sekou Odinga.
     *  - Tarik Shah (post-9/11 material-support prosecution): with the
     *    Holy Land Foundation / material-support cluster, immediately
     *    after Mohammad El-Mezain.
     */
    private const PLACEMENTS = [
        ['charles-littlejohn',   'daniel-e-hale'],
        ['ashanti-alston',       'sekou-odinga'],
        ['tarik-shah',           'mohammad-el-mezain'],
    ];

    public function handle(): int
    {
        foreach (self::PLACEMENTS as [$slugToMove, $anchorSlug]) {
            $prisoner = Prisoner::where('slug', $slugToMove)->first();
            $anchor   = Prisoner::where('slug', $anchorSlug)->first();

            if (! $prisoner) {
                $this->error("Prisoner '{$slugToMove}' not found — skipping.");
                continue;
            }
            if (! $anchor) {
                $this->error("Anchor '{$anchorSlug}' not found — skipping.");
                continue;
            }

            $oldSort = $prisoner->sort_order;
            $newSort = $anchor->sort_order + 1;

            if ($oldSort === $newSort) {
                $this->info("{$prisoner->name} is already at sort_order {$newSort} (after {$anchor->name}).");
                continue;
            }

            $this->info("Plan: move {$prisoner->name} from sort_order {$oldSort} -> {$newSort} (immediately after {$anchor->name} at {$anchor->sort_order})");

            if ($this->option('dry-run')) {
                continue;
            }

            DB::transaction(function () use ($prisoner, $newSort) {
                Prisoner::where('id', '!=', $prisoner->id)
                    ->where('sort_order', '>=', $newSort)
                    ->increment('sort_order');

                $prisoner->sort_order = $newSort;
                $prisoner->save();
            });

            $this->info("  -> done.");
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry run — no changes written.');
        }

        return self::SUCCESS;
    }
}
