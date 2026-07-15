<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Catalogs six political-prisoner movement documents (from a David Anthem
 * Bookseller listing) in the site archive. Each scan was cropped to the printed
 * content and run through OCR into a searchable PDF, committed under
 * public/pdfs/<collection>/ with a thumbnail under public/images/archive/. The
 * two multi-page items (the Dhoruba Moore tabloid and the Rose Baron pamphlet)
 * are digitized here from their covers only. Idempotent — each record skips if
 * its slug already exists.
 */
final class ArchiveAddDavidAnthemDocs extends Command
{
    protected $signature = 'archive:add-david-anthem-docs';

    protected $description = 'Add six political-prisoner movement documents (Cleaver, birthday posters, Dhoruba, BPP, ILD) to the archive';

    public function handle(): int
    {
        $records = [
            [
                'title' => 'Eldridge Cleaver Petition Campaign',
                'slug' => 'eldridge-cleaver-petition-campaign',
                'description' => 'An urgent information sheet and petition demanding the release of Black Panther Party '
                    .'Minister of Information Eldridge Cleaver, whose parole was revoked without a hearing after the '
                    .'April 6, 1968 Oakland police shootout that killed Bobby Hutton and wounded Cleaver. It gives '
                    .'background on the case and instructions for gathering signatures; the verso carries the petition '
                    .'itself. Issued by the International Committee to Release Eldridge Cleaver, 301 Broadway, San '
                    .'Francisco. This copy shows the recto information sheet. Digitized as a searchable (OCR) PDF.',
                'source_format' => 'flyer',
                'dir' => 'government-repression',
                'year' => 1968,
                'publisher' => 'International Committee to Release Eldridge Cleaver',
                'collection' => 'Government Repression',
                'subjects' => ['Eldridge Cleaver', 'Black Panther Party', 'Bobby Hutton', 'Parole', 'Political prisoners', 'San Francisco', '1968'],
            ],
            [
                'title' => "Political Prisoners' Birthdays in November & December",
                'slug' => 'political-prisoners-birthdays-november-december',
                'description' => 'A poster from the Chapel Hill Prison Books Collective listing the November–December '
                    .'birthdays — with prison addresses and short case notes — of U.S. political prisoners: Ed '
                    .'Poindexter (the Omaha/"Nebraska Two" Black Panther), Tsutomu Shirosaki (Japanese Red Army), Zolo '
                    .'Agona Azania (Black Panther Party), Fred Burton, and Jerome White-Bey (founder of the Missouri '
                    .'Prison Labour Union). It urges supporters to send birthday cards and gives instructions for '
                    .'corresponding with prisoners to reduce the likelihood of confiscation. Digitized as a searchable '
                    .'(OCR) PDF.',
                'source_format' => 'poster',
                'dir' => 'prisoner-solidarity',
                'year' => null,
                'publisher' => 'Chapel Hill Prison Books Collective',
                'collection' => 'Prisoner Solidarity',
                'subjects' => ['Political prisoners', 'Prisoner solidarity', 'Chapel Hill Prison Books Collective', 'Ed Poindexter', 'Zolo Agona Azania', 'Jerome White-Bey', 'Birthday poster'],
            ],
            [
                'title' => "Political Prisoners' Birthdays in April",
                'slug' => 'political-prisoners-birthdays-april',
                'description' => 'A companion Chapel Hill Prison Books Collective poster listing the April birthdays of '
                    .'U.S. political prisoners — Mumia Abu-Jamal, the MOVE members Chuck Sims Africa, Janet Holloway '
                    .'Africa and Janine Phillips Africa, and the Black Panthers Romaine "Chip" Fitzgerald and Marshall '
                    .'Eddie Conway — with prison addresses, case notes, an illustration of Mumia at a typewriter, and '
                    .'guidance on prison correspondence. Digitized as a searchable (OCR) PDF.',
                'source_format' => 'poster',
                'dir' => 'prisoner-solidarity',
                'year' => null,
                'publisher' => 'Chapel Hill Prison Books Collective',
                'collection' => 'Prisoner Solidarity',
                'subjects' => ['Political prisoners', 'Prisoner solidarity', 'Chapel Hill Prison Books Collective', 'Mumia Abu-Jamal', 'MOVE', 'Romaine Fitzgerald', 'Marshall Eddie Conway', 'Birthday poster'],
            ],
            [
                'title' => 'The Political Conviction of R. Dhoruba Moore and the Repression of the Black Liberation Movement',
                'slug' => 'political-conviction-dhoruba-moore',
                'description' => 'An eight-page tabloid newspaper from the International Committee to Free Dhoruba Moore '
                    .'(Brooklyn, 1980): one long article on the political conviction of Richard "Dhoruba" Moore '
                    .'(Dhoruba Bin Wahad), the New York Black Panther and Black Liberation Army member convicted in the '
                    .'1971 killing of two police officers and freed in 1990 after years of appeals, together with '
                    .'shorter profiles of other Black political prisoners including Geronimo Pratt and Republic of New '
                    .'Afrika prisoners. Digitized here from the cover as a searchable (OCR) PDF.',
                'source_format' => 'periodical',
                'dir' => 'government-repression',
                'year' => 1980,
                'publisher' => 'International Committee to Free Dhoruba Moore',
                'collection' => 'Government Repression',
                'subjects' => ['Dhoruba Bin Wahad', 'Richard Dhoruba Moore', 'Black Panther Party', 'Black Liberation Army', 'COINTELPRO', 'Geronimo Pratt', 'Republic of New Afrika', 'Political prisoners', '1980'],
            ],
            [
                'title' => 'Black Panther Party 35th Anniversary & Reunion Conference (poster)',
                'slug' => 'bpp-35th-anniversary-reunion-conference-poster',
                'description' => 'A poster for the Black Panther Party 35th Anniversary & Reunion Conference in '
                    .'Washington, D.C., April 18–20, 2002, organized by the It\'s About Time Committee on the theme '
                    .'"COINTELPRO & Political Prisoners." It advertises workshops, a banquet, film and photo/art '
                    .'exhibits, and speakers; is illustrated with eight photographs; and frames the gathering around '
                    .'the recent release of Geronimo ji-Jaga (Pratt) and the continuing fight for the Party\'s '
                    .'political prisoners. Digitized as a searchable (OCR) PDF.',
                'source_format' => 'poster',
                'dir' => 'prisoner-solidarity',
                'year' => 2002,
                'publisher' => "It's About Time (Sacramento)",
                'collection' => 'Prisoner Solidarity',
                'subjects' => ['Black Panther Party', 'COINTELPRO', 'Political prisoners', "It's About Time", 'Geronimo Pratt', 'Reunion conference', 'Washington DC', '2002'],
            ],
            [
                'title' => 'They Gave Their Freedom!',
                'slug' => 'rose-baron-they-gave-their-freedom-ild',
                'description' => 'A 1935 International Labor Defense pamphlet by Rose Baron — editor of Labor Defender '
                    .'and New York District Secretary of the ILD — documenting the work of the ILD\'s Prisoners\' Relief '
                    .'Department in supporting class-war prisoners and their families, with photographs and profiles of '
                    .'figures such as Tom Mooney and the Scottsboro defendant Haywood Patterson, and an appeal for '
                    .'funds. 30 pp.; digitized here from the cover as a searchable (OCR) PDF.',
                'source_format' => 'pamphlet',
                'dir' => 'international-labor-defense',
                'year' => 1935,
                'publisher' => 'International Labor Defense',
                'authors' => 'Rose Baron',
                'collection' => 'International Labor Defense',
                'subjects' => ['International Labor Defense', 'Rose Baron', "Prisoners' Relief", 'Tom Mooney', 'Haywood Patterson', 'Scottsboro', 'Political prisoners', '1935'],
            ],
        ];

        $added = 0;
        $skipped = 0;

        foreach ($records as $r) {
            if (ArchiveRecord::where('slug', $r['slug'])->exists()) {
                $this->line('  exists, skipping: '.$r['slug']);
                $skipped++;

                continue;
            }

            ArchiveRecord::create([
                'title' => $r['title'],
                'slug' => $r['slug'],
                'description' => $r['description'],
                'record_type' => 'document',
                'source_format' => $r['source_format'],
                'file' => '/pdfs/'.$r['dir'].'/'.$r['slug'].'.pdf',
                'thumbnail' => '/images/archive/'.$r['dir'].'/'.$r['slug'].'.jpg',
                'year' => $r['year'],
                'publisher' => $r['publisher'],
                'authors' => $r['authors'] ?? null,
                'collection' => $r['collection'],
                'subjects' => $r['subjects'],
                'is_digitized' => true,
                'published' => true,
                'sort_order' => 0,
            ]);
            $this->info('  added: '.$r['title']);
            $added++;
        }

        $this->info("\nDavid Anthem documents — added: {$added}, skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
