<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds Right On! Vol. 2 No. 9 (May 1974) — the Black Community News
 * Service of the East Coast Black Panther Party, the faction aligned
 * with Eldridge Cleaver after the 1971 split. Freedom Archives DOC513
 * BPP_Paper scan (State Historical Society of Wisconsin copy).
 */
final class AddRightOnMay1974Archive extends Command {
    protected $signature = 'archive:add-right-on-may-1974';
    protected $description = 'Add Right On! Vol. 2 No. 9 (May 1974) to the archive';

    public function handle(): int {
        $payload = [
            'title' => 'Right On! Black Community News Service — Vol. 2 No. 9 (May 1974)',
            'description' => 'Issue of Right On!, the Black Community News Service of the East Coast Black Panther Party — the paper of the faction aligned with Eldridge Cleaver after the Party\'s 1971 split, closely tied to the Black Liberation Army\'s prisoner-support networks. This issue carries the Symbionese Liberation Army\'s "The S.L.A. Speaks to the People," continued coverage of the Stella Wright rent strike in Newark, "News from the Cotton Curtain" on the Southern prisons, part 1 of "The New Urban Guerrilla," Black labor news, a Trial News section on political cases, and "The F.B.I. File" on COINTELPRO\'s dis-unity campaign. Scanned by the Freedom Archives (DOC513, BPP Paper collection) from the State Historical Society of Wisconsin copy.',
            'file' => '/pdfs/bpp-newspaper/right-on-vol-2-no-9-1974-05.pdf',
            'thumbnail' => '/thumbnails/right-on-vol-2-no-9-1974-05.jpg',
            'record_type' => 'newspaper',
            'source_format' => 'newspaper',
            'collection' => 'The Black Panther Newspaper',
            'authors' => 'Black Panther Party (East Coast)',
            'publisher' => 'Black Panther Party (East Coast)',
            'volume' => 'Vol. 2 No. 9',
            'date' => '1974-05-01',
            'year' => 1974,
            'subjects' => ['Black Panther Party', 'Black Liberation Army', 'Right On!', 'Symbionese Liberation Army', 'Stella Wright rent strike', 'COINTELPRO'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::query()->where('file', $payload['file'])->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('Updated: Right On! Vol. 2 No. 9 (May 1974)');
        } else {
            ArchiveRecord::create($payload);
            $this->info('Created: Right On! Vol. 2 No. 9 (May 1974)');
        }

        return self::SUCCESS;
    }
}
