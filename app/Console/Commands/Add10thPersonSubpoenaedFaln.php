<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds the "10th Person Subpoenaed to Grand Jury" flyer from
 * Freedom Archives' DOC41 New Movement in Solidarity with Puerto
 * Rican Independence collection — coverage of grand-jury resistance
 * during the FALN-era federal prosecutions.
 */
final class Add10thPersonSubpoenaedFaln extends Command {
    protected $signature = 'archive:add-10th-person-subpoenaed-faln';
    protected $description = 'Add the 10th Person Subpoenaed to Grand Jury flyer';

    public function handle(): int {
        $file = '/pdfs/freedom-archives/faln-10th-person-subpoenaed-grand-jury.pdf';
        $payload = [
            'title' => '10th Person Subpoenaed to Grand Jury',
            'description' => 'Flyer documenting the tenth supporter to be subpoenaed to a federal grand jury during the FALN-era investigations of the Puerto Rican independence movement. Part of the Freedom Archives DOC41 "New Movement in Solidarity with Puerto Rican Independence" collection.',
            'file' => $file,
            'record_type' => 'document',
            'source_format' => 'flyer',
            'collection' => 'Freedom Archives',
            'publisher' => 'New Movement in Solidarity with Puerto Rican Independence',
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::query()->where('file', $file)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('Updated existing record.');
        } else {
            ArchiveRecord::create($payload);
            $this->info('Created record.');
        }

        return self::SUCCESS;
    }
}
