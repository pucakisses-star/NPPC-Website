<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds four 1975 primary-source documents from the Lexington Six grand-jury
 * resistance campaign to the archive. Each was scanned to its own searchable
 * PDF (image + an embedded OCR text layer) under public/pdfs/archive, with a
 * thumbnail alongside. Idempotent — updateOrCreate keyed on slug.
 */
class AddLexingtonSixArchiveRecords extends Command
{
    protected $signature = 'archive:add-lexington-six-records';

    protected $description = 'Add the four 1975 Lexington Six grand-jury-resistance documents to the archive';

    private const SUBJECTS = ['Lexington Six', 'Grand jury resistance', 'FBI harassment', 'Gay liberation', 'Civil liberties', 'Susan Saxe'];

    public function handle(): int
    {
        foreach ($this->records() as $i => $r) {
            $slug = $r['slug'];
            $data = array_merge([
                'record_type' => 'document',
                'source_format' => 'flyer',
                'file' => '/pdfs/archive/'.$slug.'.pdf',
                'thumbnail' => '/pdfs/archive/'.$slug.'-thumb.jpg',
                'year' => 1975,
                'collection' => 'Lexington Six',
                'subjects' => self::SUBJECTS,
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $i,
            ], $r['fields']);

            ArchiveRecord::updateOrCreate(['slug' => $slug], $data);
            $this->info('Archived: '.$data['title']);
        }

        return self::SUCCESS;
    }

    private function records(): array
    {
        return [
            [
                'slug' => 'lexington-grand-jury-defense-fund-news-update-1975-05-07',
                'fields' => [
                    'title' => 'Lexington Grand Jury Defense Fund Committee — News Update (May 7, 1975)',
                    'publisher' => 'Lexington Grand Jury Defense Fund Committee',
                    'date' => '1975-05-07',
                    'description' => 'A May 7, 1975 news update from the Lexington Grand Jury Defense Fund Committee on the resistance to the FBI and the federal grand jury in Lexington, Kentucky. It reports on the "formal request" to interrogate ten people in the Susan Saxe case, the four women then jailed for refusing to testify, the Sixth Circuit\'s April 28 statement questioning the purpose of the grand jury, and the district judge\'s $10,000 cash / $20,000 property bonds set per witness.',
                ],
            ],
            [
                'slug' => 'lexington-grand-jury-defense-fund-committee-statement-1975',
                'fields' => [
                    'title' => 'Statement of the Lexington Grand Jury Defense Fund Committee (1975)',
                    'publisher' => 'Lexington Grand Jury Defense Fund Committee',
                    'description' => 'The Lexington Grand Jury Defense Fund Committee\'s statement on the March 8, 1975 jailing of six people — five women and one man, all gay — held in contempt for refusing to answer a federal grand jury in Lexington, Kentucky (the Lexington Six). It argues that using grand juries to coerce testimony, rather than to return indictments, is an abuse of the system and a violation of civil liberties, and calls for united resistance.',
                ],
            ],
            [
                'slug' => 'fbi-harassment-grand-jury-abuse-chronology-lexington-1975',
                'fields' => [
                    'title' => 'FBI Harassment / Grand Jury Abuse: A Chronology of Events in Lexington (1975)',
                    'publisher' => 'Lexington Grand Jury Defense Fund Committee',
                    'description' => 'A printed chronology of the Lexington grand-jury case: the summer/fall 1974 informant tip that two women resembled the fugitives wanted for a 1970 Boston bank robbery; the January 1975 FBI sweep of the Lexington community; the February 3 subpoenas issued by U.S. Attorney Eugene Siler; Judge Bernard Moynahan\'s refusal to quash them and his grant of "use immunity"; and the March contempt proceedings.',
                ],
            ],
            [
                'slug' => 'national-coalition-of-gay-activists-special-alert-1975',
                'fields' => [
                    'title' => 'National Coalition of Gay Activists — Special Alert on the FBI Grand-Jury Campaign (1975)',
                    'publisher' => 'National Coalition of Gay Activists (New York)',
                    'description' => 'A "Special Alert" from the National Coalition of Gay Activists (New York) warning that an FBI campaign against the feminist and gay movements had emerged in New Haven and Hartford, Connecticut, and Lexington, Kentucky, with the jailing of eight people in the Susan Saxe / Katherine Anne Power investigation. It stresses that none of the jailed were accused of any crime and gives guidance on refusing to talk to the FBI.',
                ],
            ],
        ];
    }
}
