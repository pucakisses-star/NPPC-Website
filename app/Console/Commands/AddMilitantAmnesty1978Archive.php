<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds two source documents: The Militant of July 1, 1977 (vol. 41
 * no. 25) and the Americas-section excerpt of the Amnesty International
 * Report 1978 (POL 10/01/1978), whose USA pages cover the Charlotte
 * Three, the Wilmington Ten, Peltier, Skyhorse-Mohawk and Johnny Imani
 * Harris.
 */
final class AddMilitantAmnesty1978Archive extends Command {
    protected $signature = 'archive:add-militant-amnesty-1978';
    protected $description = 'Add The Militant (Jul 1, 1977) and the Amnesty International Report 1978 excerpt to the archive';

    public function handle(): int {
        $records = [
            [
                'title' => 'The Militant, July 1, 1977',
                'description' => 'Issue of the Socialist Workers Party newsweekly (vol. 41 no. 25). Coverage includes the first anniversary of the Soweto uprising and the U.S. solidarity actions, the campaign to open the FBI\'s Rosenberg files (with Morton Sobell), the grand-jury investigations of the Puerto Rican independence movement, and the post-Franco amnesty struggles in Spain.',
                'file' => '/pdfs/periodicals/the-militant-1977-07-01.pdf',
                'thumbnail' => '/thumbnails/the-militant-1977-07-01.jpg',
                'source_format' => 'periodical',
                'collection' => 'Periodicals',
                'publisher' => 'The Militant',
                'volume' => 'Vol. 41, No. 25',
                'date' => '1977-07-01',
                'year' => 1977,
                'subjects' => ['Soweto uprising solidarity', 'Rosenberg case', 'Grand jury resistance', 'Morton Sobell'],
            ],
            [
                'title' => 'Amnesty International Report 1978 (excerpt)',
                'description' => 'Excerpt of Amnesty International\'s annual report covering July 1977 – June 1978 (POL 10/01/1978), including the United States section: the organization\'s work on the Charlotte Three (James Earl Grant, Charles Parker, T.J. Reddy), its adoption of the Wilmington Ten as Prisoners of Conscience and the campaign for their pardon, trial observation for Leonard Peltier and for Paul Skyhorse and Richard Mohawk (with appeals over their ill-treatment in the Ventura and Los Angeles county jails), and the Urgent Actions against Johnny Harris\'s scheduled execution in Alabama.',
                'file' => '/pdfs/reports/amnesty-international-report-1978-excerpt.pdf',
                'thumbnail' => '/thumbnails/amnesty-international-report-1978-excerpt.jpg',
                'source_format' => 'other',
                'collection' => 'Reports',
                'publisher' => 'Amnesty International',
                'date' => '1978-12-01',
                'year' => 1978,
                'subjects' => ['Prisoners of conscience', 'Wilmington Ten', 'Charlotte Three', 'Leonard Peltier', 'Skyhorse-Mohawk case', 'Johnny Imani Harris', 'Death penalty'],
            ],
        ];

        foreach ($records as $payload) {
            $payload += ['record_type' => 'document', 'is_digitized' => true, 'published' => true];
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
