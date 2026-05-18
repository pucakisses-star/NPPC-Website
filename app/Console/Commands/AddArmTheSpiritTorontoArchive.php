<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Register the Toronto autonomist / anti-imperialist newspaper
 * "Arm The Spirit" (1990–1995) as ArchiveRecords — 13 missing
 * issues sourced from archive.org and self-hosted under
 * /pdfs/periodicals/arm-the-spirit/. The remaining three issues
 * of the run (Nos. 10, 11, 13) are already in the database via
 * the Freedom Archives C145 collection.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddArmTheSpiritTorontoArchive extends Command {
    protected $signature = 'archive:add-arm-the-spirit-toronto';
    protected $description = 'Register the Toronto Arm The Spirit newspaper run (1990–1995)';

    public function handle(): int {
        $issues = [
            ['01-jun-1990',          '1990-06-01', 'No. 1',     'June 1990',                1990, 'ArmTheSpiritNo1June1990'],
            ['02-jul-1990',          '1990-07-01', 'No. 2',     'July 1990',                1990, 'ArmTheSpiritNo2July1990'],
            ['03-sep-1990',          '1990-09-01', 'No. 3',     'August/September 1990',    1990, 'ArmTheSpiritNo3September1990'],
            ['04-oct-1990',          '1990-10-01', 'No. 4',     'October 1990',             1990, 'ArmTheSpiritNo4October1990'],
            ['05-nov-dec-1990',      '1990-11-01', 'No. 5',     'November/December 1990',   1990, 'ArmTheSpiritNo5NovemberDecember1990'],
            ['06-jan-mar-1991',      '1991-01-01', 'No. 6',     'January/March 1991',       1991, 'ArmTheSpiritNo6JanuaryMarch1991'],
            ['07-apr-may-1991',      '1991-04-01', 'No. 7',     'April/May 1991',           1991, 'ArmTheSpiritNo7AprilMay1991'],
            ['08-jun-jul-1991',      '1991-06-01', 'No. 8',     'June/July 1991',           1991, 'ArmTheSpiritNo8JuneJuly1991'],
            ['09-aug-sep-1991',      '1991-08-01', 'No. 9',     'August/September 1991',    1991, 'ArmTheSpiritNo9AugustSeptember1991'],
            ['12-mar-may-1992',      '1992-03-01', 'No. 12',    'March/May 1992',           1992, 'ArmTheSpiritNo12MarchMay1992'],
            ['14-15-dec-1992',       '1992-12-01', 'Nos. 14/15', 'August–December 1992 (double issue)', 1992, 'ArmTheSpiritNo1415December1992'],
            ['16-fall-1993',         '1993-09-01', 'No. 16',    'Fall 1993',                1993, 'ArmTheSpiritNo16Fall1993'],
            ['18-winter-1994-1995',  '1994-12-01', 'No. 18',    'Winter 1994/1995',         1994, 'ArmTheSpiritNo18Winter19941995'],
        ];

        $base = [
            'record_type' => 'newspaper',
            'source_format' => 'periodical',
            'collection' => 'Arm The Spirit (Toronto, 1990–1995)',
            'authors' => 'Arm The Spirit Collective (Toronto)',
            'publisher' => 'Arm The Spirit',
            'subjects' => [
                'Arm The Spirit',
                'Autonomist',
                'Anti-Imperialism',
                'Political Prisoners',
                'Prisoners of War',
                'Toronto',
                'Solidarity',
                'Armed Struggle',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($issues as [$ref, $date, $number, $period, $year, $iaId]) {
            $slug = 'ats-toronto-'.$ref;
            $title = "Arm The Spirit ({$number}, {$period})";
            $description = "Issue of *Arm The Spirit*, the autonomist / anti-imperialist newspaper published from Toronto, Canada (1990–1995). ATS reported on armed-struggle movements internationally — the IRA, ETA, the German RAF, Italian autonomist prisoners, the FMLN, Kurdish liberation — and provided sustained coverage of U.S. and Canadian political prisoners and prisoners of war (Black Liberation Army, FALN, Plowshares, Native sovereignty struggles). {$number}, {$period}. Mirrored from Internet Archive item {$iaId}.";
            $payload = $base + [
                'title' => $title,
                'description' => $description,
                'file' => "/pdfs/periodicals/arm-the-spirit/ats-toronto-{$ref}.pdf",
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
