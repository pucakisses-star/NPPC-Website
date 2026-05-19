<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Register the complete archive.org-available run of *The Sedition
 * Committee* — the monthly defense-campaign newsletter for the Ohio 7
 * Sedition Conspiracy trial (Springfield, MA, 1988–1989). The Ohio 7
 * (Ray Luc Levasseur, Tom Manning, Richard Williams, Jaan Laaman,
 * Carol Manning, Patricia Levasseur, Barbara Curzi-Laaman) were tried
 * for seditious conspiracy in connection with the United Freedom Front
 * bombings; the case ended in acquittal on the sedition charges in
 * November 1989.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddSeditionCommitteeArchive extends Command {
    protected $signature = 'archive:add-sedition-committee';
    protected $description = 'Register the Ohio 7 Sedition Committee defense newsletter run (1988–1989)';

    public function handle(): int {
        $issues = [
            ['1988-05-may',       '1988-05-01', 'May 1988',       1988],
            ['1988-06-june',      '1988-06-01', 'June 1988',      1988],
            ['1988-07-july',      '1988-07-01', 'July 1988',      1988],
            ['1988-08-august',    '1988-08-01', 'August 1988',    1988],
            ['1988-09-september', '1988-09-01', 'September 1988', 1988],
            ['1988-10-october',   '1988-10-01', 'October 1988',   1988],
            ['1988-11-november',  '1988-11-01', 'November 1988',  1988],
            ['1988-12-december',  '1988-12-01', 'December 1988',  1988],
            ['1989-02-february',  '1989-02-01', 'February 1989',  1989],
            ['1989-04-april',     '1989-04-01', 'April 1989',     1989],
            ['1989-05-may',       '1989-05-01', 'May 1989',       1989],
            ['1989-06-june',      '1989-06-01', 'June 1989',      1989],
            ['1989-07-july',      '1989-07-01', 'July 1989',      1989],
            ['1989-09-september', '1989-09-01', 'September 1989', 1989],
            ['1989-12-december',  '1989-12-01', 'December 1989',  1989],
        ];

        $base = [
            'record_type' => 'newsletter',
            'source_format' => 'periodical',
            'collection' => 'Ohio 7 Sedition Committee Defense',
            'authors' => 'The Sedition Committee (Ohio 7 Defense)',
            'publisher' => 'The Sedition Committee',
            'subjects' => [
                'Ohio 7',
                'Sedition Conspiracy',
                'United Freedom Front',
                'UFF',
                'Ray Luc Levasseur',
                'Tom Manning',
                'Richard Williams',
                'Jaan Laaman',
                'Carol Manning',
                'Patricia Levasseur',
                'Barbara Curzi-Laaman',
                'Springfield Trial',
                'Defense Campaign',
                'Political Prisoners',
                'Anti-Imperialism',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($issues as [$ref, $date, $period, $year]) {
            $slug = 'sedition-committee-'.$ref;
            $title = "The Sedition Committee — {$period} (Ohio 7 Defense)";
            $description = "Monthly defense-campaign newsletter from *The Sedition Committee*, the support formation organizing for the Ohio 7 sedition-conspiracy trial in Springfield, Massachusetts (1988–1989). The Ohio 7 — Ray Luc Levasseur, Tom Manning, Richard Williams, Jaan Laaman, Carol Manning, Patricia Levasseur, and Barbara Curzi-Laaman — were prosecuted on seditious-conspiracy and RICO charges in connection with the United Freedom Front (UFF) bombings of corporate and military targets during the early 1980s. The Springfield trial was the largest U.S. sedition prosecution in modern movement history; in November 1989 the jury acquitted on the sedition counts. The newsletter coordinated court-watching, defense fundraising, prisoner correspondence, and political education throughout the trial. {$period}.";
            $payload = $base + [
                'title' => $title,
                'description' => $description,
                'file' => "/pdfs/periodicals/sedition-committee/sedition-committee-{$ref}.pdf",
                'year' => $year,
                'date' => $date,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) { $existing->update($payload); $updated++; }
            else { ArchiveRecord::create(['slug' => $slug] + $payload); $added++; }
        }

        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
