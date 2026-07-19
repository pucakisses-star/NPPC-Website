<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds two 1984 grand-jury-resistance source documents to the archive:
 * the John Brown Anti-Klan Committee's "Stop the Grand Jury!" newspaper
 * (November 1984, via the Freedom Archives DOC37 scan) and The Militant
 * of April 6, 1984 (vol. 48 no. 12), whose back page carries the Alberto
 * de Jesús Berríos interview.
 */
final class AddGrandJuryMilitant1984Archive extends Command {
    protected $signature = 'archive:add-grand-jury-militant-1984';
    protected $description = 'Add the JBAKC Stop the Grand Jury (Nov 1984) and The Militant (Apr 6, 1984) to the archive';

    public function handle(): int {
        $records = [
            [
                'title' => 'Stop the Grand Jury! (November 1984)',
                'description' => 'The John Brown Anti-Klan Committee\'s newspaper on the mid-1980s wave of political grand juries: the D.C. grand jury that subpoenaed four JBAKC members (Steven Burke, Julie Nalibov, Christine Rico and Sandra Roland, all later jailed as resisters), the Boston grand jury and Operation BOSLUC manhunt, the Battle Creek grand jury that subpoenaed 200 people from the Black community, the New York 8+ arrests, and a signed statement of non-collaboration from dozens of resisters and political prisoners. Scanned by the Freedom Archives (DOC37).',
                'file' => '/pdfs/freedom-archives/stop-the-grand-jury-jbakc-november-1984.pdf',
                'thumbnail' => '/thumbnails/stop-the-grand-jury-jbakc-november-1984.jpg',
                'record_type' => 'document',
                'source_format' => 'periodical',
                'collection' => 'Freedom Archives',
                'publisher' => 'John Brown Anti-Klan Committee',
                'date' => '1984-11-01',
                'year' => 1984,
                'subjects' => ['Grand jury resistance', 'John Brown Anti-Klan Committee', 'Political internment', 'Black liberation', 'Puerto Rican independence'],
                'is_digitized' => true,
                'published' => true,
            ],
            [
                'title' => 'The Militant, April 6, 1984',
                'description' => 'Issue of the Socialist Workers Party newsweekly (vol. 48 no. 12) with the back-page interview "A Puerto Rican activist fights FBI persecution" — Alberto de Jesús Berríos on his nine months\' jailing for refusing the Sabana Seca grand jury — plus coverage of the Kathy Boudin trial\'s security measures, Pam Fadem\'s criminal-contempt prosecution, the five FALN grand-jury resisters sentenced to three years, and Héctor Marroquín\'s asylum fight.',
                'file' => '/pdfs/periodicals/the-militant-1984-04-06.pdf',
                'thumbnail' => '/thumbnails/the-militant-1984-04-06.jpg',
                'record_type' => 'document',
                'source_format' => 'periodical',
                'collection' => 'Periodicals',
                'publisher' => 'The Militant',
                'volume' => 'Vol. 48, No. 12',
                'date' => '1984-04-06',
                'year' => 1984,
                'subjects' => ['Grand jury resistance', 'Puerto Rican independence', 'Sabana Seca', 'Kathy Boudin', 'Deportation defense'],
                'is_digitized' => true,
                'published' => true,
            ],
        ];

        foreach ($records as $payload) {
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
