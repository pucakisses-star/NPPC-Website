<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers the 27 net-new digitized audio/video/image items from
 * Freedom Archives Collection 29 that the PDF-only import missed.
 * URLs are kept remote (Vimeo embeds, freedomarchives.org mp3/jpeg);
 * this command does not mirror the assets locally.
 *
 * Dedup notes:
 *   - 4 items were already in c1013 import (David Gilbert "Lifetime
 *     of Struggle", Marilyn Buck tribute, Boston Trial Irish PDF,
 *     Chuck Malone PDF) — excluded here.
 *   - 1 PDF (StopDeathPenaltyLegalLynchingsUSA) is already in
 *     local c29 at a slightly different path — excluded here.
 *   - 2 within-API duplicate mp3 entries were collapsed (Sundiata
 *     Acoli interview, Silvia Baraldini case).
 */
final class RegisterFreedomArchivesC29MissingMedia extends Command {
    protected $signature = 'archive:register-freedom-archives-c29-missing-media';
    protected $description = 'Register 27 audio/video/image items missing from FA c29 import';

    public function handle(): int {
        $payloads = json_decode(file_get_contents(database_path('data/freedom-archives-c29-missing-media.json')), true);

        $registered = 0;
        foreach ($payloads as $payload) {
            $slug = $payload['slug'];
            $record = [
                'title' => $payload['title'],
                'description' => $payload['description'] ?? null,
                'record_type' => $payload['record_type'] ?? 'document',
                'source_format' => $payload['source_format'] ?? 'unknown',
                'file' => $payload['url'],
                'collection' => 'Freedom Archives — Political Prisoners',
                'publisher' => 'Freedom Archives',
                'year' => $payload['year'] ?? null,
                'date' => $payload['date'] ?? null,
                'subjects' => ['Freedom Archives', 'Political Prisoners', 'Audio/Video Archive'],
                'is_digitized' => true,
                'published' => true,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) {
                $existing->update($record);
                $this->info('Updated: '.$payload['title']);
            } else {
                ArchiveRecord::create(['slug' => $slug] + $record);
                $this->info('Added:   '.$payload['title']);
            }
            $registered++;
        }

        $this->info("\nDone. Registered={$registered}");

        return self::SUCCESS;
    }
}
