<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Disambiguates ArchiveRecord titles that collide because the same
 * event or zine has multiple legitimate variants (event flyer +
 * participant brochure + poster, different issues of a periodical
 * without issue numbers in the title, etc.). Renames rather than
 * deletes — the records ARE different artifacts.
 *
 * Pass --apply to write.
 */
final class DisambiguateArchiveTitles extends Command {
    protected $signature = 'archive:disambiguate-titles {--apply : Write the changes (dry-run by default)}';
    protected $description = 'Rename ArchiveRecord rows to disambiguate same-title artifacts';

    public function handle(): int {
        $apply = (bool) $this->option('apply');

        // Map of slug → title suffix to append.
        $renames = [
            // NYC ABC RDTW 2016
            'nycabc-2016-participant-sponsor-brochure' => ' (Participant / Sponsor Brochure)',
            'nycabc-rdtw-2016-quarter-sheet' => ' (Quarter-sheet Flyer)',
            // 2017
            'nycabc-rdtw-2017-quarter-sheet' => ' (Quarter-sheet Flyer)',
            'nycabc-2017-participant-sponsor-brochure-back' => ' (Participant / Sponsor Brochure)',
            'nycabc-rdtw2017-copy' => ' (Poster)',
            // 2018
            'nycabc-rdtw-2018-quartersheet-final' => ' (Quarter-sheet Flyer)',
            'nycabc-2018-participant-sponsor-brochure' => ' (Participant / Sponsor Brochure)',
            // 2019
            'nycabc-rdtw-2019-quartersheet' => ' (Quarter-sheet Flyer)',
            'nycabc-2019-participant-sponsor-form' => ' (Participant / Sponsor Brochure)',
            // 2021
            'nycabc-rdtwflyer' => ' (Quarter-sheet Flyer)',
            'nycabc-participant-sponsor-brochure-2021' => ' (Participant / Sponsor Brochure)',
            // 2022
            'nycabc-flyer-2022' => ' (Quarter-sheet Flyer)',
            'nycabc-participant-sponsor-brochure-2022' => ' (Participant / Sponsor Brochure)',
            // 2023
            'nycabc-quarter-sheet-2023' => ' (Quarter-sheet Flyer)',
            'nycabc-poster-2023' => ' (Poster)',
            'nycabc-participant-sponsor-brochure-1' => ' (Participant / Sponsor Brochure)',
            // 2024
            'nycabc-quarter-sheet-compressed' => ' (Quarter-sheet Flyer)',
            'nycabc-poster-2024' => ' (Poster)',
            'nycabc-rdtw-2024-participant-sponsor-brochure' => ' (Participant / Sponsor Brochure)',
            // 2025
            'nycabc-quarter-sheet-2025' => ' (Quarter-sheet Flyer)',
            'nycabc-rdtw-2025-participant-sponsor-brochure' => ' (Participant / Sponsor Brochure)',
            // Tom Manning memorial zine variants
            'nycabc-tomzinereading' => ' (Reading Layout)',
            'nycabc-tomzinetotal' => ' (Full Booklet)',
            // fag-c303 BPP NorCal "Black Power!" plain ×4 — append printing # by slug suffix
            'fag-c303-black-power' => ' (Printing 1)',
            'fag-c303-black-power-2' => ' (Printing 2)',
            'fag-c303-black-power-3' => ' (Printing 3)',
            'fag-c303-black-power-4' => ' (Printing 4)',
            // fag-c1009 "Off the Hook" ×4
            'fag-c1009-off-the-hook' => ' (Issue 1)',
            'fag-c1009-off-the-hook-2' => ' (Issue 2)',
            'fag-c1009-off-the-hook-3' => ' (Issue 3)',
            'fag-c1009-off-the-hook-4' => ' (Issue 4)',
            // fa-c145 Arm the Spirit ×3
            'fa-c145-arm-the-spirit-for-revolutionary-resistance' => ' (Printing 1)',
            'fa-c145-arm-the-spirit-for-revolutionary-resistance-2' => ' (Printing 2)',
            'fa-c145-arm-the-spirit-for-revolutionary-resistance-3' => ' (Printing 3)',
            // c1 Marion Lockdown "Letter to Friends" pair
            'fag-c1-letter-to-friends' => ' (CEML, Nov 1985)',
            'fag-c1-letter-to-friends-2' => ' (CEML, Nov 1991)',
        ];

        $renamed = 0;
        $missing = [];
        foreach ($renames as $slug => $suffix) {
            $r = ArchiveRecord::where('slug', $slug)->first();
            if (! $r) {
                $missing[] = $slug;

                continue;
            }
            if (str_ends_with($r->title, $suffix)) {
                $this->line('SKIP (already suffixed): #'.$r->id.'  '.$r->title);

                continue;
            }
            $old = $r->title;
            $new = $r->title.$suffix;
            $this->info('  '.$old.'  →  '.$new);
            if ($apply) {
                $r->title = $new;
                $r->save();
            }
            $renamed++;
        }

        $this->info("\nRenamed: {$renamed}");
        if (! empty($missing)) {
            $this->warn('Missing (already renamed / slug changed): '.count($missing));
            foreach ($missing as $m) {
                $this->line('  '.$m);
            }
        }
        if (! $apply) {
            $this->info('(dry-run; re-run with --apply to write)');
        }

        return self::SUCCESS;
    }
}
