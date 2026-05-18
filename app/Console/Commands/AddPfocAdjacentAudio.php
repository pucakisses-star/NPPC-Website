<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Add 2 PFOC-adjacent audio interviews surfaced from IA in the
 * 2026-05-18 follow-up scan: the 2018 Making Contact episode
 * "Specters of Attica" on the 2016 Michigan Kinross prison strike,
 * and the 2016 Interference Archive interview with PFOC veteran
 * Diana Block. A third Laura Whitehorn interview was found but is
 * access-restricted on IA and not freely downloadable.
 *
 * Idempotent.
 */
final class AddPfocAdjacentAudio extends Command {
    protected $signature = 'archive:add-pfoc-adjacent-audio';
    protected $description = 'Add 2 PFOC-adjacent audio archive records (Making Contact / Specters of Attica + Diana Block)';

    public function handle(): int {
        $records = [
            [
                'slug' => 'making-contact-specters-of-attica-2018',
                'title' => "Making Contact: Specters of Attica — Reflections from Inside a Michigan Prison Strike (April 25, 2018)",
                'description' => "Making Contact (Frequencies of Change Media) episode from April 25, 2018, marking the 45th anniversary of the September 1971 Attica prison uprising and documenting the September 2016 strike at Michigan's Kinross Correctional Facility. Hundreds of imprisoned people at Kinross refused to report to work or lock down, joining the coordinated nationwide September 9, 2016 prison strike. Includes first-person accounts from inside Kinross and analysis from prisoner-support organizers including Diana Block (PFOC) and others. A core document of the 2016 national prison strike record and the modern strand of Attica-anniversary movement memory.",
                'record_type' => 'audio',
                'source_format' => 'radio episode',
                'file' => '/pdfs/pfoc-adjacent/making-contact-specters-of-attica-2018.mp3',
                'collection' => 'PFOC Adjacent — Radio / Audio',
                'authors' => 'Frequencies of Change Media',
                'publisher' => 'Making Contact / Frequencies of Change Media',
                'year' => 2018,
                'date' => '2018-04-25',
                'subjects' => ['Attica', 'Michigan Kinross Strike 2016', 'National Prison Strike 2016', 'Prison Organizing', 'Diana Block', 'PFOC'],
                'is_digitized' => true,
                'published' => true,
            ],
            [
                'slug' => 'diana-block-interference-archive-2016',
                'title' => "Audio Interference 17: Diana Block (June 23, 2016)",
                'description' => "Interference Archive's 2016 podcast interview with Diana Block — longtime Prairie Fire Organizing Committee member, author of *Arm the Spirit: A Woman's Journey Underground and Back* (AK Press, 2009), and longtime political-prisoner support organizer. The interview covers Block's nine years living clandestinely after a 1985 FBI sting, her decades of work supporting U.S. political prisoners through the California Coalition for Women Prisoners, the post-1993 PFOC reorganization, and the practice of anti-imperialist solidarity from the inside.",
                'record_type' => 'audio',
                'source_format' => 'podcast interview',
                'file' => '/pdfs/pfoc-adjacent/diana-block-interference-archive-2016.mp3',
                'collection' => 'PFOC Adjacent — Radio / Audio',
                'authors' => 'Interference Archive (interviewer); Diana Block (interviewee)',
                'publisher' => 'Interference Archive',
                'year' => 2016,
                'date' => '2016-06-23',
                'subjects' => ['Diana Block', 'PFOC', 'Prairie Fire Organizing Committee', 'California Coalition for Women Prisoners', 'Arm the Spirit', 'Political-Prisoner Support'],
                'is_digitized' => true,
                'published' => true,
            ],
        ];

        $added = 0; $updated = 0;
        foreach ($records as $r) {
            $slug = $r['slug']; unset($r['slug']);
            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) { $existing->update($r); $updated++; }
            else { ArchiveRecord::create(['slug' => $slug] + $r); $added++; }
        }
        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
