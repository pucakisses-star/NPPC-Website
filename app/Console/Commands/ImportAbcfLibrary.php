<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportAbcfLibrary extends Command {
    protected $signature = 'archive:import-abcf-library';
    protected $description = 'Import the 42 PDFs from southchicagoabc.org/abcf-library/ as ArchiveRecord rows (idempotent by slug)';

    public function handle(): int {
        $records = $this->records();
        $created = 0;
        $updated = 0;

        foreach ($records as $r) {
            $existing = ArchiveRecord::where('slug', $r['slug'])->first();
            $payload = collect($r)->except('slug')->all();

            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                ArchiveRecord::create(['slug' => $r['slug']] + $payload);
                $created++;
            }
        }

        $this->info("Done. {$created} created, {$updated} updated.");

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(): array {
        // [slug, title, source_format, year, date, volume, subjects?, description?]
        // slug here is the PDF filename stem under public/pdfs/abcf-library/
        $items = [
            // ABCF foundational documents
            ['abcf-constitution', 'ABCF Constitution', 'pamphlet', null, null, null, null, 'Constitution of the Anarchist Black Cross Federation, the continental network of anarchist political-prisoner support collectives.'],
            ['what-is-the-abcf', 'What is the ABCF?', 'pamphlet', null, null, null, null, 'Introductory pamphlet explaining the work and structure of the Anarchist Black Cross Federation.'],
            ['abcf-guide-to-political-prisoners-prisoners-of-war-support', 'ABCF Guide to Political Prisoners and Prisoners of War Support', 'pamphlet', null, null, null, ['Prisoner Support', 'Political Prisoners'], 'Practical guide on supporting political prisoners and prisoners of war held inside U.S. prisons.'],
            ['building-pppow-subsistence-programs', 'Building Political Prisoner and POW Subsistence Programs', 'pamphlet', null, null, null, ['Prisoner Support'], 'Organizing document on building material support (warchest) programs for political prisoners and prisoners of war.'],

            // Plain Words zine
            ['abcf-plain-words-1-fall-2009', 'ABCF Plain Words, Issue #1 (Fall 2009)', 'periodical', 2009, '2009-09-01', 'Issue #1', null, null],
            ['abcf-plain-words-2-summer-2010', 'ABCF Plain Words, Issue #2 (Summer 2010)', 'periodical', 2010, '2010-06-01', 'Issue #2', null, null],

            // ABCF Update newsletter
            ['anarchist-black-cross-federation-update-34-april-2001', 'ABCF Update #34 (April 2001)', 'periodical', 2001, '2001-04-01', 'Issue #34', null, null],
            ['anarchist-black-cross-federation-update-41-winter-2005', 'ABCF Update #41 (Winter 2005)', 'periodical', 2005, '2005-01-01', 'Issue #41', null, null],
            ['anarchist-black-cross-federation-update-42-spring-2005', 'ABCF Update #42 (Spring 2005)', 'periodical', 2005, '2005-04-01', 'Issue #42', null, null],
            ['anarchist-black-cross-federation-update-43-fall-2005', 'ABCF Update #43 (Fall 2005)', 'periodical', 2005, '2005-10-01', 'Issue #43', null, null],
            ['anarchist-black-cross-federation-update-44-winter-2006', 'ABCF Update #44 (Winter 2006)', 'periodical', 2006, '2006-01-01', 'Issue #44', null, null],
            ['anarchist-black-cross-federation-update-45-fall-2006', 'ABCF Update #45 (Fall 2006)', 'periodical', 2006, '2006-10-01', 'Issue #45', null, null],
            ['anarchist-black-cross-federation-update-46-winter-2007', 'ABCF Update #46 (Winter 2007)', 'periodical', 2007, '2007-01-01', 'Issue #46', null, null],
            ['anarchist-black-cross-federation-update-47-spring-2007', 'ABCF Update #47 (Spring 2007)', 'periodical', 2007, '2007-04-01', 'Issue #47', null, null],
            ['anarchist-black-cross-federation-update-48-summer-2007', 'ABCF Update #48 (Summer 2007)', 'periodical', 2007, '2007-07-01', 'Issue #48', null, null],
            ['anarchist-black-cross-federation-update-49-winter-2008', 'ABCF Update #49 (Winter 2008)', 'periodical', 2008, '2008-01-01', 'Issue #49', null, null],
            ['anarchist-black-cross-federation-update-50-summer-2008', 'ABCF Update #50 (Summer 2008)', 'periodical', 2008, '2008-07-01', 'Issue #50', null, null],
            ['anarchist-black-cross-federation-update-51-winter-2008', 'ABCF Update #51 (Winter 2008)', 'periodical', 2008, '2008-12-01', 'Issue #51', null, null],
            ['anarchist-black-cross-federation-update-52-spring-2009', 'ABCF Update #52 (Spring 2009)', 'periodical', 2009, '2009-04-01', 'Issue #52', null, null],

            // NYC ABC trifolds
            ['alex-stokes-trifold-nycabc', 'Alex Stokes — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners'], 'NYC ABC pocket-trifold introducing political prisoner Alex Stokes and how to write to them.'],
            ['bill-dunne-trifold-nycabc', 'Bill Dunne — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Anarchist'], 'NYC ABC pocket-trifold on anti-authoritarian political prisoner Bill Dunne.'],
            ['casey-goonan-trifold-nycabc', 'Casey Goonan — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners'], 'NYC ABC pocket-trifold on political prisoner Casey Goonan.'],
            ['joe-joe-bowen-trifold-nycabc', 'Joe-Joe Bowen — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Black Liberation'], 'NYC ABC pocket-trifold on Black Liberation Army veteran Joseph "Joe-Joe" Bowen.'],
            ['kamau-sadiki-trifold-nycabc', 'Kamau Sadiki — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Black Liberation'], 'NYC ABC pocket-trifold on former Black Panther Kamau Sadiki.'],
            ['marius-mason-trifold-nyc-abc', 'Marius Mason — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Earth Liberation'], 'NYC ABC pocket-trifold on Earth Liberation Front prisoner Marius Mason.'],
            ['muhammad-burton-trifold-nyc-abc', 'Muhammad Burton — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Black Liberation'], 'NYC ABC pocket-trifold on Black Liberation Army veteran Muhammad (Fred) Burton.'],
            ['mumia-abujamal-trifold-nycabc', 'Mumia Abu-Jamal — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Black Liberation'], 'NYC ABC pocket-trifold on journalist and former Black Panther Mumia Abu-Jamal.'],
            ['ronald-reed-trifold-nycabc', 'Ronald Reed — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Black Liberation'], 'NYC ABC pocket-trifold on political prisoner Ronald Reed.'],
            ['virgin-islands-5-abdul-azeez-and-hanif-bey-and-malik-smith-trifold-nycabc', 'Virgin Islands Five (Abdul Azeez, Hanif Bey, Malik Smith) — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Virgin Islands Five'], 'NYC ABC pocket-trifold on the three remaining Virgin Islands Five prisoners — Abdul Azeez, Hanif Shabazz Bey, and Malik Smith.'],
            ['xinachtli-fka-alvaro-luna-hernandez-trifold-nyc-abc', 'Xinachtli (fka Alvaro Luna Hernández) — NYC ABC Trifold', 'flyer', null, null, 'Trifold', ['Political Prisoners', 'Chicano Liberation'], 'NYC ABC pocket-trifold on Chicano political prisoner Xinachtli (formerly Alvaro Luna Hernández).'],

            // LA ABCF flyers
            ['free-peppy-and-krystal-laabcf-flyer', 'Free Peppy and Krystal — LA ABCF Flyer', 'flyer', null, null, null, ['Political Prisoners'], 'Los Angeles ABCF flyer on political prisoners Peppy and Krystal.'],
            ['jessica-reznicek-laabcf-flyer', 'Jessica Reznicek — LA ABCF Flyer', 'flyer', null, null, null, ['Political Prisoners', 'Anti-Pipeline'], 'Los Angeles ABCF flyer on Catholic Worker anti-pipeline activist Jessica Reznicek.'],
            ['joejoe-bowen-laabcf-flyer', 'Joe-Joe Bowen — LA ABCF Flyer', 'flyer', null, null, null, ['Political Prisoners', 'Black Liberation'], 'Los Angeles ABCF flyer on Black Liberation Army veteran Joseph "Joe-Joe" Bowen.'],
            ['kamau-sadiki-laabcf-flyer', 'Kamau Sadiki — LA ABCF Flyer', 'flyer', null, null, null, ['Political Prisoners', 'Black Liberation'], 'Los Angeles ABCF flyer on former Black Panther Kamau Sadiki.'],
            ['kojo-bomani-sababu-laabcf-flyer', 'Kojo Bomani Sababu — LA ABCF Flyer', 'flyer', null, null, null, ['Political Prisoners', 'Black Liberation'], 'Los Angeles ABCF flyer on Black Liberation Army / RNA veteran Kojo Bomani Sababu.'],
            ['muhammad-burton-laabcf-flyer', 'Muhammad Burton — LA ABCF Flyer', 'flyer', null, null, null, ['Political Prisoners', 'Black Liberation'], 'Los Angeles ABCF flyer on Black Liberation Army veteran Muhammad (Fred) Burton.'],
            ['virgin-islands-5-abdul-azeez-and-hanif-bey-and-malik-smith-laabcf-flyer', 'Virgin Islands Five (Azeez, Bey, Smith) — LA ABCF Flyer', 'flyer', null, null, null, ['Political Prisoners', 'Virgin Islands Five'], 'Los Angeles ABCF flyer on the three remaining Virgin Islands Five prisoners.'],
            ['xinachtli-fka-alvaro-luna-hernandez-laabcf-flyer', 'Xinachtli (fka Alvaro Luna Hernández) — LA ABCF Flyer', 'flyer', null, null, null, ['Political Prisoners', 'Chicano Liberation'], 'Los Angeles ABCF flyer on Chicano political prisoner Xinachtli.'],

            // Posters
            ['free-all-pps-poster-1', 'Free All Political Prisoners — Poster #1', 'flyer', null, null, 'Poster', ['Political Prisoners'], 'ABCF "Free All Political Prisoners" poster series, design #1.'],
            ['free-all-pps-poster-2', 'Free All Political Prisoners — Poster #2', 'flyer', null, null, 'Poster', ['Political Prisoners'], 'ABCF "Free All Political Prisoners" poster series, design #2.'],
            ['free-all-pps-poster-3', 'Free All Political Prisoners — Poster #3', 'flyer', null, null, 'Poster', ['Political Prisoners'], 'ABCF "Free All Political Prisoners" poster series, design #3.'],
            ['free-all-pps-poster-4', 'Free All Political Prisoners — Poster #4', 'flyer', null, null, 'Poster', ['Political Prisoners'], 'ABCF "Free All Political Prisoners" poster series, design #4.'],
        ];

        $rows = [];
        $sort = 100; // sort behind 4StruggleMag (which uses 0..)
        $defaultSubjects = ['Anarchist Black Cross', 'Political Prisoners', 'Prisoner Support'];
        $boilerplate = 'Distributed via the Anarchist Black Cross Federation; sourced from the South Chicago ABC zine library (southchicagoabc.org/abcf-library/).';

        foreach ($items as $i) {
            [$slug, $title, $sourceFormat, $year, $date, $volume, $subjects, $desc] = $i + array_fill(0, 8, null);

            $subjects = $subjects ?: $defaultSubjects;
            $description = $desc ? $desc.' '.$boilerplate : $boilerplate;

            $rows[] = [
                'slug' => 'abcf-library-'.$slug,
                'title' => $title,
                'description' => $description,
                'record_type' => 'document',
                'source_format' => $sourceFormat,
                'file' => '/pdfs/abcf-library/'.$slug.'.pdf',
                'thumbnail' => '/images/archive/abcf-library/'.$slug.'-cover.jpg',
                'year' => $year,
                'date' => $date,
                'publisher' => 'Anarchist Black Cross Federation',
                'collection' => 'ABCF Library',
                'volume' => $volume,
                'subjects' => $subjects,
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $sort++,
            ];
        }

        return $rows;
    }
}
