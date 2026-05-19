<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Register the 4 known issues of *Underground*, the zine of the
 * North American Animal Liberation Front Supporters Group (NA ALF SG),
 * 1994–1996. Self-hosted under /pdfs/periodicals/underground-alf-sg/.
 * Issue 4 is unaccounted for; issue 5 was compressed for size.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddUndergroundAlfSgArchive extends Command {
    protected $signature = 'archive:add-underground-alf-sg';
    protected $description = 'Register the NA ALF SG Underground zine run (1994–1996, 4 issues)';

    public function handle(): int {
        $issues = [
            ['01', '1994-08-01', 'No. 1', 'August 1994',                  1994],
            ['02', '1994-11-01', 'No. 2', 'November 1994',                1994],
            ['03', '1994-12-01', 'No. 3', 'December 1994 – November 1995', 1995],
            ['05', '1996-06-01', 'No. 5', 'Summer–Fall 1996',             1996],
        ];

        $base = [
            'record_type' => 'zine',
            'source_format' => 'periodical',
            'collection' => 'North American ALF Supporters Group — Underground',
            'authors' => 'North American ALF Supporters Group',
            'publisher' => 'North American ALF Supporters Group',
            'subjects' => [
                'Underground',
                'North American ALF Supporters Group',
                'NA ALF SG',
                'Animal Liberation Front',
                'ALF',
                'Animal Liberation',
                'Eco-Defense',
                'Political Prisoners',
                'Above-ground Support',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($issues as [$ref, $date, $number, $period, $year]) {
            $slug = 'underground-alf-sg-no-'.$ref;
            $title = "Underground ({$number}, {$period}) — NA ALF SG";
            $description = "Issue of *Underground*, the zine of the North American Animal Liberation Front Supporters Group (NA ALF SG). The ALF SG was the public/above-ground support formation for clandestine animal-liberation and eco-defense actions in North America in the 1990s — circulating ALF communiques, documenting actions, defending the politics of underground direct action, and providing support and correspondence work for animal-liberation political prisoners. The zine prefigured the later FBI/AETA (Animal Enterprise Terrorism Act) prosecutions of the 2000s — the SHAC 7, Operation Backfire / Green Scare, and the long line of animal-liberation prisoners (Daniel McGowan, Marius Mason / Marie Mason, Walter Bond, et al.). {$number}, {$period}. Mirrored from Internet Archive.";
            $payload = $base + [
                'title' => $title,
                'description' => $description,
                'file' => "/pdfs/periodicals/underground-alf-sg/underground-{$ref}.pdf",
                'year' => $year,
                'date' => $date,
                'volume' => $number,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) { $existing->update($payload); $updated++; }
            else { ArchiveRecord::create(['slug' => $slug] + $payload); $added++; }
        }

        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
