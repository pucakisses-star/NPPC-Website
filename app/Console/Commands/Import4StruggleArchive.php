<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class Import4StruggleArchive extends Command {
    protected $signature = 'archive:import-4struggle';
    protected $description = 'Import the 4StruggleMag PDFs in public/pdfs/4strugglemag/ as ArchiveRecord rows (idempotent by slug)';

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
                $this->line("updated: {$r['slug']}");
            } else {
                ArchiveRecord::create(['slug' => $r['slug']] + $payload);
                $created++;
                $this->line("created: {$r['slug']}");
            }
        }

        $this->info("Done. {$created} created, {$updated} updated.");

        return self::SUCCESS;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function records(): array {
        $issues = [
            ['n' => 11, 'season' => 'Fall 2008', 'year' => 2008, 'date' => '2008-09-01', 'file' => '4sm11collated.pdf', 'cover' => '4sm11-cover.jpg'],
            ['n' => 12, 'season' => 'Winter 2008–09', 'year' => 2008, 'date' => '2008-12-01', 'file' => '4sm12collated.pdf', 'cover' => '4sm12-cover.jpg'],
            ['n' => 13, 'season' => 'Spring 2009', 'year' => 2009, 'date' => '2009-03-01', 'file' => '4sm13collated.pdf', 'cover' => '4sm13-cover.jpg'],
            ['n' => 14, 'season' => 'Winter 2009', 'year' => 2009, 'date' => '2009-12-01', 'file' => '4sm14collated.pdf', 'cover' => '4sm14-cover.jpg'],
            ['n' => 15, 'season' => 'Spring 2010', 'year' => 2010, 'date' => '2010-03-01', 'file' => '4sm15collated1.pdf', 'cover' => '4sm15-cover.jpg'],
            ['n' => 16, 'season' => 'Summer 2010', 'year' => 2010, 'date' => '2010-08-01', 'file' => '4sm16collated.pdf', 'cover' => '4sm16-cover.jpg'],
            ['n' => 17, 'season' => 'Fall 2010', 'year' => 2010, 'date' => '2010-11-01', 'file' => '4sm17collated.pdf', 'cover' => '4sm17-cover.jpg'],
            ['n' => 18, 'season' => 'Spring 2011', 'year' => 2011, 'date' => '2011-03-01', 'file' => '4sm18collated.pdf', 'cover' => '4sm18-cover.jpg'],
            ['n' => 19, 'season' => 'Summer 2011', 'year' => 2011, 'date' => '2011-07-01', 'file' => '4sm19collated.pdf', 'cover' => '4sm19-cover.jpg'],
            ['n' => 20, 'season' => 'Fall 2012', 'year' => 2012, 'date' => '2012-10-01', 'file' => '4sm20collated2.pdf', 'cover' => '4sm20-cover.jpg'],
            ['n' => 21, 'season' => 'Late 2012', 'year' => 2012, 'date' => '2012-10-15', 'file' => '4sm21collated.pdf', 'cover' => '4sm21-cover.jpg'],
        ];

        $rows = [];
        $sort = 0;

        foreach ($issues as $i) {
            $rows[] = [
                'slug' => '4strugglemag-issue-'.$i['n'],
                'title' => '4StruggleMag, Issue #'.$i['n'].' ('.$i['season'].')',
                'description' => '4StruggleMag was an independent, non-sectarian revolutionary magazine produced by the Toronto chapter of the Anarchist Black Cross Federation and edited by anti-imperialist political prisoner Jaan Laaman. It featured writing from North American political prisoners and their supporters on justice, equality, socialism, and national-liberation struggles.',
                'record_type' => 'document',
                'source_format' => 'periodical',
                'file' => '/pdfs/4strugglemag/'.$i['file'],
                'thumbnail' => '/images/archive/4strugglemag/'.$i['cover'],
                'year' => $i['year'],
                'date' => $i['date'],
                'publisher' => 'Toronto ABCF',
                'authors' => 'Jaan Laaman (editor)',
                'collection' => '4StruggleMag',
                'volume' => 'Issue #'.$i['n'],
                'subjects' => ['Political Prisoners', 'Anarchist Black Cross', 'Anti-Imperialism', 'Black Liberation'],
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $sort++,
            ];
        }

        $rows[] = [
            'slug' => '4strugglemag-court-solidarity',
            'title' => 'Court Solidarity',
            'description' => 'A booklet on court solidarity practice published alongside 4StruggleMag.',
            'record_type' => 'document',
            'source_format' => 'pamphlet',
            'file' => '/pdfs/4strugglemag/courtsolidarity.pdf',
            'collection' => '4StruggleMag',
            'volume' => 'Supplement',
            'subjects' => ['Court Solidarity', 'Movement Defense'],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => $sort++,
        ];

        $rows[] = [
            'slug' => '4strugglemag-legal-solidarity-handbook',
            'title' => 'Legal Solidarity Handbook',
            'description' => 'Handbook of legal-solidarity tactics for movement defendants, distributed via 4StruggleMag.',
            'record_type' => 'document',
            'source_format' => 'pamphlet',
            'file' => '/pdfs/4strugglemag/legalsolidarityhandbook.pdf',
            'collection' => '4StruggleMag',
            'volume' => 'Supplement',
            'subjects' => ['Legal Defense', 'Movement Defense', 'Grand Jury Resistance'],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => $sort++,
        ];

        $rows[] = [
            'slug' => '4strugglemag-subscription-flyers',
            'title' => '4StruggleMag Subscription Flyers',
            'description' => 'Subscription and outreach flyers for 4StruggleMag.',
            'record_type' => 'document',
            'source_format' => 'flyer',
            'file' => '/pdfs/4strugglemag/4smsubscriptionflyers.pdf',
            'collection' => '4StruggleMag',
            'subjects' => ['Outreach'],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => $sort++,
        ];

        $rows[] = [
            'slug' => 'nycabc-political-prisoner-listing-2015',
            'title' => 'NYC ABC U.S. Political Prisoner and Prisoner of War Listing (Ed. 10.3)',
            'description' => 'NYC Anarchist Black Cross\'s periodically-updated directory of U.S.-held political prisoners and prisoners of war, current as of May 2015. Includes mailing addresses, birthdays, case summaries, and contact information across sections including Black/New Afrikan Liberation, Anarchist Movement, Indigenous Resistance, Hacks/Information Leaks, Green Scare, Puerto Rican Independence, Other National Liberation, GI/War Resisters, Anti-Police, Radical Self-Defense, and the Virgin Island Five.',
            'record_type' => 'document',
            'source_format' => 'pamphlet',
            'file' => '/pdfs/4strugglemag/nycabc_polprisonerlisting_10-3may2015_final.pdf',
            'year' => 2015,
            'date' => '2015-05-11',
            'publisher' => 'NYC Anarchist Black Cross',
            'collection' => 'Movement Directories',
            'volume' => 'Edition 10.3',
            'subjects' => ['Political Prisoners', 'Prisoner Support', 'Anarchist Black Cross'],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => $sort++,
        ];

        $rows[] = [
            'slug' => 'united-freedom-front-pamphlet',
            'title' => 'The Ohio 7: Living For The Revolution',
            'description' => 'Movement pamphlet on the Ohio 7 / United Freedom Front defendants — Ray Luc Levasseur, Tom Manning, Richard Williams, Jaan Laaman, Carol Manning, Patricia Levasseur, and Barbara Curzi-Laaman — the clandestine anti-imperialist formation responsible for the United Freedom Front bombings of corporate and military targets in the early 1980s, indicted and prosecuted in the 1988–89 Springfield, Massachusetts sedition-conspiracy trial. Self-hosted from the Internet Archive ohio-7-living-for-the-revolution item.',
            'record_type' => 'document',
            'source_format' => 'pamphlet',
            'file' => '/pdfs/4strugglemag/ohio-7-living-for-the-revolution.pdf',
            'collection' => '4StruggleMag',
            'subjects' => ['Ohio 7', 'United Freedom Front', 'UFF', 'Anti-Imperialism', 'Armed Struggle', 'Ray Luc Levasseur', 'Tom Manning', 'Richard Williams', 'Jaan Laaman', 'Sedition Conspiracy'],
            'is_digitized' => true,
            'published' => true,
            'sort_order' => $sort++,
        ];

        return $rows;
    }
}
