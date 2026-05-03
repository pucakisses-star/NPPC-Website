<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PlaceAllZeroSortPrisoners extends Command
{
    protected $signature = 'prisoners:place-zero-sort {--dry-run : Show planned moves without writing}';

    protected $description = 'Re-evaluate every prisoner currently at sort_order 0 and slot them into a sensible position next to thematically/chronologically related entries.';

    /**
     * Ordered list of [slug → anchor slug]. The prisoner is placed
     * immediately after the anchor (anchor sort_order + 1). Order
     * matters: each iteration looks up the anchor fresh, so a
     * placement can chain off an earlier placement.
     *
     * Anchors were chosen to fit each prisoner with the
     * most-related cluster already in the database:
     *  - Trump-era ICE / anti-ICE → after the existing 2024-25
     *    student-visa ICE cluster (Khalil/Mahdawi/Ozturk/...)
     *  - Prairieland 9 → as a group, right after that cluster
     *  - AIM additions (Means/Banks/Trudell/Aquash) → after Peltier
     *  - SNCC additions (Carmichael/Moses/Forman/Nash/Lawson)
     *    → after John Lewis
     *  - BPP additions (Newton/Seale/Cleaver) → after Fred Hampton
     *  - Chicago 7 → after Dick Gregory in the antiwar cluster
     *  - Catholic Worker (Day, Russo) → after Daniel Berrigan
     *  - MOVE (Ramona Africa) → with the MOVE 9
     *  - Iraq War resisters (Mejía/Funk) → after Kimberly Rivera
     *  - Scott Warren → with humanitarian/border activism
     *  - Lauren Handy → with Caleb Freestone (FACE Act parallel)
     *  - DxE open rescue (Hsiung, Rosenberg) → with the modern
     *    animal-rights cluster (after Walter Bond)
     *  - Operation Backfire cooperators / William Rodgers / Tre
     *    Arrow → after Daniel McGowan in the ELF cluster
     *  - Doug Ellerman → with the cooperator group
     *  - Frank Ambrose → adjacent to Marius Mason (his ex-wife and
     *    the principal target of his cooperation)
     *  - Justin Samuel → adjacent to Peter Young
     *  - Mother Jones / Joe Hill / Goldman / Berkman / Baldwin →
     *    after Eugene Debs in the IWW/labor cluster
     *  - Pedro Albizu Campos + 4 Capitol attackers → at the start
     *    of the existing FALN/PR-independence cluster
     *  - Reuben Crandall + John Brown + 6 raiders + Vallandigham
     *    → in the antebellum cluster after Joseph Palmer
     *  - Haymarket 8 → directly after the antebellum/Civil-War
     *    block
     */
    private const PLACEMENTS = [
        // ── Trump-era ICE / anti-ICE ─────────────────────────────
        ['estefany-rodriguez',         'yunseo-chung'],

        // ── Prairieland 9 (chained as a group) ───────────────────
        ['benjamin-song',              'jeanette-vizguerra'],
        ['cameron-arnold',             'benjamin-song'],
        ['zachary-evetts',             'cameron-arnold'],
        ['bradford-morris',            'zachary-evetts'],
        ['savanna-batten',             'bradford-morris'],
        ['maricela-rueda',             'savanna-batten'],
        ['elizabeth-soto',             'maricela-rueda'],
        ['ines-soto',                  'elizabeth-soto'],
        ['daniel-sanchez-estrada',     'ines-soto'],

        // ── AIM (after Peltier) ──────────────────────────────────
        ['russell-means',              'leonard-peltier'],
        ['dennis-banks',               'russell-means'],
        ['john-trudell',               'dennis-banks'],
        ['anna-mae-aquash',            'john-trudell'],

        // ── BPP additions (after Fred Hampton) ───────────────────
        ['huey-p-newton',              'fred-hampton'],
        ['bobby-seale',                'huey-p-newton'],
        ['eldridge-cleaver',           'bobby-seale'],

        // ── SNCC additions (after John Lewis) ────────────────────
        ['diane-nash',                 'john-lewis'],
        ['bob-moses',                  'diane-nash'],
        ['james-forman',               'bob-moses'],
        ['james-lawson-jr',            'james-forman'],
        ['stokely-carmichael',         'james-lawson-jr'],

        // ── Chicago 7 (after Dick Gregory in antiwar cluster) ────
        ['abbie-hoffman',              'dick-gregory'],
        ['jerry-rubin',                'abbie-hoffman'],
        ['tom-hayden',                 'jerry-rubin'],
        ['rennie-davis',               'tom-hayden'],
        ['david-dellinger',            'rennie-davis'],
        ['john-froines',               'david-dellinger'],
        ['lee-weiner',                 'john-froines'],

        // ── Pentagon Papers / Catholic Worker (after Berrigan) ───
        ['anthony-russo',              'daniel-berrigan'],
        ['dorothy-day',                'anthony-russo'],

        // ── MOVE (with the MOVE 9) ───────────────────────────────
        ['ramona-africa',              'merle-austin-africa'],

        // ── Iraq War resisters (after Kimberly Rivera) ───────────
        ['camilo-mejia',               'kimberly-rivera'],
        ['stephen-funk',               'camilo-mejia'],

        // ── Scott Warren (humanitarian / border) ─────────────────
        ['scott-warren',               'stephen-funk'],

        // ── Lauren Handy (parallel to FACE Act prosecution) ──────
        ['lauren-handy',               'caleb-freestone'],

        // ── DxE open rescue (after Walter Bond) ──────────────────
        ['wayne-hsiung',               'walter-bond'],
        ['zoe-rosenberg',              'wayne-hsiung'],

        // ── Older ELF (after Daniel McGowan) ─────────────────────
        ['william-c-rodgers',          'daniel-gerard-mcgowan'],
        ['tre-arrow',                  'william-c-rodgers'],

        // ── Operation Backfire cooperators (chained) ─────────────
        ['stanislas-meyerhoff',        'tre-arrow'],
        ['kevin-tubbs',                'stanislas-meyerhoff'],
        ['chelsea-gerlach',            'kevin-tubbs'],
        ['suzanne-savoie',             'chelsea-gerlach'],
        ['kendall-tankersley',         'suzanne-savoie'],
        ['darren-thurston',            'kendall-tankersley'],
        ['douglas-joshua-ellerman',    'darren-thurston'],

        // ── Frank Ambrose (informant on Marius Mason) ────────────
        ['frank-ambrose',              'marius-mason'],

        // ── Justin Samuel (informant on Peter Young) ─────────────
        ['justin-samuel',              'peter-young'],

        // ── IWW / WWI labor cluster (after Eugene Debs) ──────────
        ['mary-harris-jones',          'eugene-debs'],
        ['joe-hill',                   'mary-harris-jones'],
        ['emma-goldman',               'joe-hill'],
        ['alexander-berkman',          'emma-goldman'],
        ['roger-baldwin',              'alexander-berkman'],

        // ── Puerto Rican independence (Albizu Campos before FALN) ─
        ['pedro-albizu-campos',        'aida-mccray-robinson'],
        ['lolita-lebron',              'pedro-albizu-campos'],
        ['rafael-cancel-miranda',      'lolita-lebron'],
        ['andres-figueroa-cordero',    'rafael-cancel-miranda'],
        ['irvin-flores-rodriguez',     'andres-figueroa-cordero'],

        // ── Antebellum / Civil War (after Joseph Palmer) ─────────
        ['reuben-crandall',            'joseph-palmer'],
        ['john-brown',                 'reuben-crandall'],
        ['aaron-stevens',              'john-brown'],
        ['john-e-cook',                'aaron-stevens'],
        ['john-anthony-copeland-jr',   'john-e-cook'],
        ['shields-green',              'john-anthony-copeland-jr'],
        ['edwin-coppoc',               'shields-green'],
        ['albert-hazlett',             'edwin-coppoc'],
        ['clement-vallandigham',       'albert-hazlett'],

        // ── Haymarket Martyrs (after Vallandigham) ───────────────
        ['august-spies',               'clement-vallandigham'],
        ['albert-parsons',             'august-spies'],
        ['adolph-fischer',             'albert-parsons'],
        ['george-engel',               'adolph-fischer'],
        ['louis-lingg',                'george-engel'],
        ['samuel-fielden',             'louis-lingg'],
        ['michael-schwab',             'samuel-fielden'],
        ['oscar-neebe',                'michael-schwab'],
    ];

    public function handle(): int
    {
        $moved = 0;
        $skippedNoPrisoner = 0;
        $skippedNoAnchor = 0;
        $skippedAlready = 0;
        $skippedHasNonZeroSort = 0;

        $placedSlugs = array_column(self::PLACEMENTS, 0);

        foreach (self::PLACEMENTS as [$slugToMove, $anchorSlug]) {
            $prisoner = Prisoner::where('slug', $slugToMove)->first();
            $anchor   = Prisoner::where('slug', $anchorSlug)->first();

            if (! $prisoner) {
                $this->warn("  skip: prisoner '{$slugToMove}' not found in DB");
                $skippedNoPrisoner++;
                continue;
            }

            // Only re-place prisoners that are still at sort_order 0.
            // If someone has already been manually placed somewhere
            // sensible, leave them alone.
            if ($prisoner->sort_order !== 0) {
                $this->line("  skip: {$slugToMove} already has sort_order {$prisoner->sort_order}");
                $skippedHasNonZeroSort++;
                continue;
            }

            if (! $anchor) {
                $this->warn("  skip: anchor '{$anchorSlug}' not found (would have placed {$slugToMove} after it)");
                $skippedNoAnchor++;
                continue;
            }

            $newSort = $anchor->sort_order + 1;

            if ($prisoner->sort_order === $newSort) {
                $skippedAlready++;
                continue;
            }

            $this->info("  move {$slugToMove} -> sort {$newSort} (after {$anchor->name} at {$anchor->sort_order})");

            if (! $this->option('dry-run')) {
                DB::transaction(function () use ($prisoner, $newSort) {
                    Prisoner::where('id', '!=', $prisoner->id)
                        ->where('sort_order', '>=', $newSort)
                        ->increment('sort_order');

                    $prisoner->sort_order = $newSort;
                    $prisoner->save();
                });
            }

            $moved++;
        }

        // Surface anything still at sort 0 that we didn't have a placement for.
        $orphans = Prisoner::where('sort_order', 0)
            ->whereNotIn('slug', $placedSlugs)
            ->get(['slug', 'name']);

        if ($orphans->isNotEmpty()) {
            $this->line('');
            $this->warn('Prisoners still at sort_order 0 with no placement rule:');
            foreach ($orphans as $o) {
                $this->line("  - {$o->slug}  ({$o->name})");
            }
            $this->line('Either add a placement for them in PlaceAllZeroSortPrisoners.php, place them via admin, or delete them if duplicates.');
        }

        $this->line('');
        if ($this->option('dry-run')) {
            $this->warn('Dry run — no changes written.');
        }
        $this->info("Done.");
        $this->line("  moved:                   {$moved}");
        $this->line("  skipped (no prisoner):   {$skippedNoPrisoner}");
        $this->line("  skipped (no anchor):     {$skippedNoAnchor}");
        $this->line("  skipped (not at 0):      {$skippedHasNonZeroSort}");

        return self::SUCCESS;
    }
}
