<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds two documents: The Black Panther of August 10, 1974 (Vol. XII
 * No. 3, Langston Hughes Memorial Library scan) — the "They Are Trying
 * To Kill Huey P. Newton" issue, with the Bob Wells parole interview,
 * the Atmore-Holman Brothers trial report and the San Quentin Six
 * statements — and the international anarchist prisoners list 2025
 * (solidarity.international, reposted by anarchistfederation.net).
 */
final class AddBpAug1974And2025ListArchive extends Command {
    protected $signature = 'archive:add-bp-aug-1974-and-2025-list';
    protected $description = 'Add The Black Panther (Aug 10, 1974) and the 2025 international anarchist prisoners list to the archive';

    public function handle(): int {
        $records = [
            [
                'title' => 'The Black Panther: Intercommunal News Service — Vol. XII No. 3 (August 10, 1974)',
                'description' => 'Issue of The Black Panther published weeks before Huey P. Newton\'s flight to Cuba, led by Elaine Brown\'s press statement — "They are trying to kill Huey P. Newton" — on the Fox nightclub arrests of Newton and seven other Party members. Also carries part 3 of the parole interview with Wesley Robert "Bob" Wells after 47 years in California prisons, the Atmore-Holman Brothers trial report naming all eleven defendants, the San Quentin Six\'s statements against the book The Dragon Has Come, the Nixon-approved BPP spy plan from the impeachment evidence, and the USS Midway courts-martial. Scan from the Langston Hughes Memorial Library, Lincoln University.',
                'file' => '/pdfs/bpp-newspaper/the-black-panther-vol-12-no-3-1974-08-10.pdf',
                'thumbnail' => '/thumbnails/the-black-panther-vol-12-no-3-1974-08-10.jpg',
                'record_type' => 'newspaper',
                'source_format' => 'newspaper',
                'collection' => 'The Black Panther Newspaper',
                'authors' => 'Black Panther Party',
                'publisher' => 'Black Panther Party',
                'volume' => 'Vol. XII No. 3',
                'date' => '1974-08-10',
                'year' => 1974,
                'subjects' => ['Black Panther Party', 'Huey P. Newton', 'Atmore-Holman Brothers', 'San Quentin Six', 'Wesley Robert Wells', 'Prisoner rights'],
            ],
            [
                'title' => 'International Prisoners List 2025 (International Anarchist Defence Fund)',
                'description' => 'Annual list of imprisoned anarchists and movement-supported prisoners worldwide, compiled for the June 11 international day of solidarity — with case summaries, addresses and birthdays for prisoners in the United States, Belarus, Russia, Germany, the United Kingdom and elsewhere. U.S. entries include Eric King, Marius Mason, Jessica Reznicek, Xinachtli, Michael Kimble, Sean Swain, Casey Goonan and Sofia Johnson. Published at solidarity.international and reposted by the Anarchist Federation.',
                'file' => '/pdfs/prisoner-solidarity/international-prisoners-list-2025.pdf',
                'thumbnail' => '/thumbnails/international-prisoners-list-2025.jpg',
                'record_type' => 'document',
                'source_format' => 'other',
                'collection' => 'Prisoner Solidarity',
                'publisher' => 'International Anarchist Defence Fund / solidarity.international',
                'date' => '2025-06-11',
                'year' => 2025,
                'subjects' => ['Anarchist prisoners', 'Prisoner solidarity', 'June 11', 'Political prisoners'],
            ],
        ];

        foreach ($records as $payload) {
            $payload += ['is_digitized' => true, 'published' => true];
            $existing = ArchiveRecord::query()->where('file', $payload['file'])->first();
            if ($existing) {
                $existing->update($payload);
                $this->info("Updated: {$payload['title']}");
            } else {
                ArchiveRecord::create($payload);
                $this->info("Created: {$payload['title']}");
            }
        }

        return self::SUCCESS;
    }
}
