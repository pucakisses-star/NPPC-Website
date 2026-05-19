<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Register the full available run of *Love and Rage* — the newspaper
 * of the Love and Rage Revolutionary Anarchist Federation (US / Canada
 * / Mexico, 1989–1998) — as ArchiveRecords. 46 of the 49 known issues
 * are self-hosted under /pdfs/periodicals/love-and-rage/, mirrored
 * from archive.org. Also upgrades the previously-registered Vol. 4
 * No. 1 record (originally added by AddPfocAdjacentArchive with an
 * IA detail-page URL) to point at the self-hosted PDF.
 *
 * Three issues (Vol. 1 No. 6 Oct 1990, Vol. 1 No. 7 Nov 1990, Vol. 4
 * No. 4 Sept 1993) were not retrievable at sweep time and can be
 * backfilled in a follow-up.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddLoveAndRageArchive extends Command {
    protected $signature = 'archive:add-love-and-rage';
    protected $description = 'Register the Love and Rage newspaper run (1990–1998, 46 issues self-hosted)';

    public function handle(): int {
        // [ref, date, volno, period, year]
        $issues = [
            ['01-01', '1990-04-01', 'Vol. 1 No. 1',  'April 1990',              1990],
            ['01-02', '1990-05-01', 'Vol. 1 No. 2',  'May 1990',                1990],
            ['01-03', '1990-06-01', 'Vol. 1 No. 3',  'June 1990',               1990],
            ['01-04', '1990-07-01', 'Vol. 1 No. 4',  'July 1990',               1990],
            ['01-05', '1990-08-01', 'Vol. 1 No. 5',  'August 1990',             1990],
            ['02-01', '1991-01-01', 'Vol. 2 No. 1',  'January 1991',            1991],
            ['02-02', '1991-02-01', 'Vol. 2 No. 2',  'February 1991',           1991],
            ['02-03', '1991-03-01', 'Vol. 2 No. 3',  'March 1991',              1991],
            ['02-04', '1991-04-01', 'Vol. 2 No. 4',  'April 1991',              1991],
            ['02-05', '1991-05-01', 'Vol. 2 No. 5',  'May 1991',                1991],
            ['02-06', '1991-06-01', 'Vol. 2 No. 6',  'June/July 1991',          1991],
            ['02-07', '1991-08-01', 'Vol. 2 No. 7',  'August 1991',             1991],
            ['02-08', '1991-10-01', 'Vol. 2 No. 8',  'October 1991',            1991],
            ['02-09', '1991-11-01', 'Vol. 2 No. 9',  'November 1991',           1991],
            ['02-10', '1991-12-01', 'Vol. 2 No. 10', 'December 1991',           1991],
            ['03-01', '1992-01-01', 'Vol. 3 No. 1',  'January 1992',            1992],
            ['03-02', '1992-02-01', 'Vol. 3 No. 2',  'February 1992',           1992],
            ['03-03', '1992-03-01', 'Vol. 3 No. 3',  'March 1992',              1992],
            ['03-04', '1992-04-01', 'Vol. 3 No. 4',  'April/May 1992',          1992],
            ['03-05', '1992-06-01', 'Vol. 3 No. 5',  'June 1992',               1992],
            ['03-06', '1992-07-01', 'Vol. 3 No. 6',  'July/August 1992',        1992],
            ['03-07', '1992-09-01', 'Vol. 3 No. 7',  'September/October/November 1992', 1992],
            ['04-01', '1993-02-01', 'Vol. 4 No. 1',  'February/March 1993',     1993],
            ['04-02', '1993-04-01', 'Vol. 4 No. 2',  'April/May 1993',          1993],
            ['04-03', '1993-06-01', 'Vol. 4 No. 3',  'June/July 1993',          1993],
            ['04-05', '1993-11-01', 'Vol. 4 No. 5',  'November 1993',           1993],
            ['04-06', '1993-12-01', 'Vol. 4 No. 6',  'December 1993',           1993],
            ['05-01', '1994-03-01', 'Vol. 5 No. 1',  'March/April 1994',        1994],
            ['05-02', '1994-06-01', 'Vol. 5 No. 2',  'June 1994',               1994],
            ['05-03', '1994-08-01', 'Vol. 5 No. 3',  'August 1994',             1994],
            ['05-04', '1994-11-01', 'Vol. 5 No. 4',  'November/December 1994',  1994],
            ['06-01', '1995-01-01', 'Vol. 6 No. 1',  'January/February 1995',   1995],
            ['06-02', '1995-03-01', 'Vol. 6 No. 2',  'March/April 1995',        1995],
            ['06-03', '1995-05-01', 'Vol. 6 No. 3',  'May/June 1995',           1995],
            ['06-04', '1995-08-01', 'Vol. 6 No. 4',  'August/September 1995',   1995],
            ['07-01', '1996-04-01', 'Vol. 7 No. 1',  'April/May 1996',          1996],
            ['07-02', '1996-06-01', 'Vol. 7 No. 2',  'June/July 1996',          1996],
            ['07-03', '1996-08-01', 'Vol. 7 No. 3',  'August 1996',             1996],
            ['07-05', '1996-10-01', 'Vol. 7 No. 5',  'October/November 1996',   1996],
            ['08-01', '1997-01-01', 'Vol. 8 No. 1',  'January/February 1997',   1997],
            ['08-02', '1997-03-01', 'Vol. 8 No. 2',  'March/April 1997',        1997],
            ['08-03', '1997-06-01', 'Vol. 8 No. 3',  'June/July 1997',          1997],
            ['08-04', '1997-08-01', 'Vol. 8 No. 4',  'August/September 1997',   1997],
            ['08-05', '1997-11-01', 'Vol. 8 No. 5',  'November/December 1997',  1997],
            ['09-01', '1998-01-01', 'Vol. 9 No. 1',  'January/February 1998',   1998],
            ['09-02', '1998-09-01', 'Vol. 9 No. 2',  'Fall 1998 (final issue)', 1998],
        ];

        $base = [
            'record_type' => 'newspaper',
            'source_format' => 'periodical',
            'collection' => 'Love and Rage Revolutionary Anarchist Federation',
            'authors' => 'Love and Rage Revolutionary Anarchist Federation',
            'publisher' => 'Love and Rage Revolutionary Anarchist Federation',
            'subjects' => [
                'Love and Rage',
                'Anarchism',
                'Revolutionary Anarchism',
                'Anti-Imperialism',
                'Anti-Fascism',
                'Political Prisoners',
                'Mumia Abu-Jamal',
                'Anti-Authoritarian',
                'North American Anarchism',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($issues as [$ref, $date, $volno, $period, $year]) {
            // For Vol. 4 No. 1 the old AddPfocAdjacentArchive seeded slug is
            // 'love-and-rage-vol-4-no-1-feb-march-1993'. Preserve it so this
            // command upgrades that existing record instead of creating a
            // duplicate.
            $slug = $ref === '04-01'
                ? 'love-and-rage-vol-4-no-1-feb-march-1993'
                : 'love-and-rage-vol-'.$ref;
            $title = "Love and Rage — {$volno}, {$period}";
            $description = "Issue of *Love and Rage*, the newspaper of the Love and Rage Revolutionary Anarchist Federation — the principal 1990s North American revolutionary-anarchist formation (active 1989–1998 across the United States, Canada, and Mexico). The paper carried sustained political-prisoner support coverage (Mumia Abu-Jamal, the Puerto Rican independentistas, Leonard Peltier, the Ohio 7, the Native sovereignty struggles), anti-fascist organizing reports, and anti-imperialist analysis aligned with the post-PFOC tradition of solidarity work. {$volno}, {$period}. Mirrored from Internet Archive.";
            $payload = $base + [
                'title' => $title,
                'description' => $description,
                'file' => "/pdfs/periodicals/love-and-rage/love-and-rage-{$ref}.pdf",
                'year' => $year,
                'date' => $date,
                'volume' => $volno,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) { $existing->update($payload); $updated++; }
            else { ArchiveRecord::create(['slug' => $slug] + $payload); $added++; }
        }

        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
