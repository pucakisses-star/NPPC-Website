<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds two Ángel Rodríguez Cristóbal source documents to the archive:
 * the New Movement in Solidarity with Puerto Rican Independence's
 * memorial Bulletin (Jan-Feb 1980, Freedom Archives DOC41) and his
 * March 4, 1978 Chicago speech as reprinted in Breakthrough (ERO L
 * scan) — the excerpt is not contained in the site's existing
 * Breakthrough issue holdings.
 */
final class AddCristobalDocsArchive extends Command {
    protected $signature = 'archive:add-cristobal-docs';
    protected $description = 'Add the New Movement Bulletin (Jan-Feb 1980) and the 1978 Rodríguez Cristóbal speech to the archive';

    public function handle(): int {
        $records = [
            [
                'title' => 'Bulletin — New Movement in Solidarity with Puerto Rican Independence (Jan–Feb 1980)',
                'description' => 'Memorial issue dedicated to Ángel Rodríguez Cristóbal (1946–1979), published weeks after his death in FCI Tallahassee while serving his six-month Vieques trespass sentence as a self-declared Prisoner of War. Carries his statement to the sentencing court, the account of his funeral in Ciales drawing 8,000 people, the Sabana Seca response, the Vieques campaign and the movement\'s fight over how to answer his death. Scanned by the Freedom Archives (DOC41, New Movement in Solidarity collection).',
                'file' => '/pdfs/freedom-archives/new-movement-bulletin-jan-feb-1980.pdf',
                'thumbnail' => '/thumbnails/new-movement-bulletin-jan-feb-1980.jpg',
                'source_format' => 'periodical',
                'collection' => 'Freedom Archives',
                'publisher' => 'New Movement in Solidarity with Puerto Rican Independence and Socialism',
                'date' => '1980-01-01',
                'year' => 1980,
                'subjects' => ['Ángel Rodríguez Cristóbal', 'Vieques', 'Puerto Rican independence', 'Prisoners of war', 'Deaths in custody'],
            ],
            [
                'title' => '¡Que Viva Puerto Rico Libre! — Speech by Ángel Rodríguez Cristóbal (March 4, 1978)',
                'description' => 'Speech by Liga Socialista Puertorriqueña representative Ángel Rodríguez Cristóbal at the March 4, 1978 Chicago demonstration for Puerto Rican independence, freedom for the four Nationalist prisoners and the jailed grand-jury resisters — delivered twenty months before his death in federal custody. As reprinted in Breakthrough, via the Encyclopedia of Anti-Revisionism On-Line scan (marxists.org).',
                'file' => '/pdfs/political-prisoner-library/angel-rodriguez-cristobal-speech-march-1978.pdf',
                'thumbnail' => '/thumbnails/angel-rodriguez-cristobal-speech-march-1978.jpg',
                'source_format' => 'article',
                'collection' => 'Political Prisoner Library',
                'publisher' => 'Prairie Fire Organizing Committee (Breakthrough)',
                'date' => '1978-03-04',
                'year' => 1978,
                'subjects' => ['Ángel Rodríguez Cristóbal', 'Puerto Rican independence', 'Grand jury resistance', 'Liga Socialista Puertorriqueña'],
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
