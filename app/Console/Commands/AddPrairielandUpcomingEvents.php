<?php

namespace App\Console\Commands;

use App\Models\Event;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds the next wave of "Prairieland Defendant Support" events, grounded in
 * where the case actually stands after the June 23 – July 6, 2026 federal
 * sentencings (16 defendants, 22 months to 100 years): trial defendants have
 * filed Fifth Circuit appeals (SCSJ and the Texas Civil Rights Project are
 * on Savanna Batten's), Johnson County state prosecutions are expected later
 * in 2026 with bonds up to $15 million, and the DFW support committee's
 * standing call is for letter-writing nights, fundraisers, and teach-ins.
 *
 * Idempotent — re-runs update by slug. Images reuse the series' existing
 * storage art when present.
 */
final class AddPrairielandUpcomingEvents extends Command {
    protected $signature = 'events:add-prairieland-upcoming';
    protected $description = 'Add upcoming Prairieland defendant support events';

    public function handle(): int {
        $disk = Storage::disk('public');
        $letterArt = $disk->exists('events/prairieland-defendants.png') ? 'events/prairieland-defendants.png' : null;
        $solidarityArt = $disk->exists('events/prairieland-international-day-of-solidarity-2026-04-04.jpg')
            ? 'events/prairieland-international-day-of-solidarity-2026-04-04.jpg' : null;

        $events = [
            [
                'slug'        => 'prairieland-letter-writing-2026-07-23',
                'title'       => 'Letter-Writing Night: After the Sentences — Writing to the Prairieland Defendants',
                'description' => "Between June 23 and July 6, sixteen federal Prairieland defendants were sentenced to terms ranging from 22 months to 100 years. As BOP designations come through, mail is what reaches them first. We'll write letters together, share the support committee's current mailing addresses and mail rules, and set up ongoing pen-pal pairings. Materials provided; drop in any time."
                    ."\n\nThe DFW Support Committee's standing call: \"We have a long journey ahead of us... We are here and we won't give up.\"",
                'image'       => $letterArt,
                'event_date'  => '2026-07-23',
                'time'        => '7:00 PM CT',
                'location'    => 'Online — hosted by NPPC',
                'event_url'   => 'https://prairielanddefendants.com/get-involved/',
            ],
            [
                'slug'        => 'prairieland-commissary-appeals-fund-2026-07-31',
                'title'       => 'Commissary & Appeals Fund Drive for the Prairieland Defendants',
                'description' => 'A one-day push to stock commissary accounts for the sixteen people now serving federal time and to seed the appellate war chest. All trial defendants have noticed appeals to the U.S. Fifth Circuit; the Southern Coalition for Social Justice and the Texas Civil Rights Project have taken on Savanna Batten\'s 50-year-sentence appeal. Give through the support committee\'s verified channels — every dollar is split evenly across defendants unless earmarked.',
                'image'       => $solidarityArt,
                'event_date'  => '2026-07-31',
                'time'        => null,
                'location'    => 'Online (all day)',
                'event_url'   => 'https://prairielanddefendants.com/',
            ],
            [
                'slug'        => 'prairieland-teach-in-appeals-2026-08-13',
                'title'       => 'Teach-In: The Prairieland Appeals and the State Trials Ahead',
                'description' => "Where does the case go now? A community briefing on the road ahead: the Fifth Circuit appeals of the June–July federal sentences, and the Johnson County state prosecutions — including terrorism counts — expected to reach trial later in 2026, with defendants held on bonds as high as \$15 million. With updates from case observers and the defendant support network; time for questions and for organizing the next round of court support."
                    ."\n\nBackground reading: the NPPC case files on the Prairieland defendants and the coalition's newswire coverage of the sentencings.",
                'image'       => $solidarityArt,
                'event_date'  => '2026-08-13',
                'time'        => '7:00 PM CT',
                'location'    => 'Online — hosted by NPPC',
                'event_url'   => 'https://prairielanddefendants.com/category/court-notes/',
            ],
        ];

        foreach ($events as $data) {
            $slug = $data['slug'];
            unset($data['slug']);
            $data += ['body' => null, 'series' => 'Prairieland Defendant Support', 'published' => true];

            $existing = Event::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($data);
                $this->info('Updated event: '.$data['title']);
            } else {
                Event::create(['slug' => $slug] + $data);
                $this->info('Created event: '.$data['title']);
            }
        }

        return self::SUCCESS;
    }
}
