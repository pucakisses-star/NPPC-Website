<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportAzineLibraryThird extends Command {
    protected $signature = 'archive:import-azine-library-third';
    protected $description = 'Third pass: 7 more US-political-prisoner zines from azinelibrary.org';

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

    /** @return list<array<string,mixed>> */
    private function records(): array {
        $items = [
            ['25-years-on-the-move', '25 Years on the MOVE', 'pamphlet', null, ['MOVE', 'Political Prisoners', 'Black Liberation', 'Philadelphia'],
                'Anniversary zine on the MOVE Organization\'s 25 years of struggle in Philadelphia — from the 1978 Powelton Avenue confrontation that produced the MOVE 9 to the 1985 police bombing of the Osage Avenue home.'],
            ['black-anarchism', 'Black Anarchism', 'pamphlet', null, ['Black Liberation', 'Anarchism', 'New Afrikan', 'Political Prisoners'],
                'Foundational zine on Black anarchism in the United States, rooted in the political tradition of Kuwasi Balagoon, Ojore Lutalo, Lorenzo Komboa Ervin, Ashanti Alston, and other New Afrikan anarchist political prisoners.'],
            ['bloody-wake-of-alcatraz', 'The Bloody Wake of Alcatraz', 'pamphlet', null, ['Indigenous Resistance', 'AIM', 'Political Prisoners', 'COINTELPRO'],
                'Historical zine on the consequences of the 1969–71 Native American occupation of Alcatraz Island and the wave of federal repression — culminating in COINTELPRO actions against the American Indian Movement — that followed.'],
            ['bring-the-war-home', 'Bring the War Home', 'pamphlet', null, ['Anti-War', 'Anti-Imperialism', 'Weather Underground', 'Political Prisoners'],
                'Compilation on the U.S. anti-imperialist armed-struggle tradition — Weather Underground, May 19 Communist Organization, United Freedom Front — that took the slogan "Bring the war home" literally during the Vietnam War and after.'],
            ['i-will-not-crawl-robert-f-williams', 'I Will Not Crawl: Excerpts from Robert F. Williams on Black Struggle and Armed Self-Defense in Monroe, NC', 'pamphlet', null, ['Black Liberation', 'Robert F. Williams', 'Monroe NC', 'Political Prisoners'],
                'Excerpts from Black liberation leader Robert F. Williams\'s writings on his Monroe, North Carolina NAACP chapter and his advocacy of armed Black self-defense in the late 1950s and early 1960s, before his FBI-driven flight into exile in Cuba and China.'],
            ['political-pre-history-of-love-and-rage', 'The Political Pre-History of Love and Rage: The Anarchist Struggle in the 1980s and 1990s', 'pamphlet', null, ['Anarchist', 'Love and Rage', 'Movement History'],
                'Historical zine tracing the U.S. anarchist movement of the 1980s and 1990s that produced the Love and Rage Revolutionary Anarchist Federation, including its political-prisoner support and anti-fascist work.'],
            ['wobblies-and-zapatistas', 'Wobblies and Zapatistas', 'pamphlet', null, ['IWW', 'Indigenous Resistance', 'Anti-Imperialism'],
                'Dialogue between the IWW tradition of revolutionary unionism and the Zapatista movement in Chiapas — relevant to U.S. political-prisoner history through the IWW\'s long line of incarcerated organizers from Bill Haywood onward.'],
        ];

        $rows = [];
        $sort = 260;
        foreach ($items as $i) {
            [$slug, $title, $sourceFormat, $year, $subjects, $desc] = $i;
            $rows[] = [
                'slug' => 'azine-'.$slug,
                'title' => $title,
                'description' => $desc.' Sourced from azinelibrary.org (Anarchist Zine Library).',
                'record_type' => 'document',
                'source_format' => $sourceFormat,
                'file' => '/pdfs/azine-library/'.$slug.'.pdf',
                'thumbnail' => '/images/archive/azine-library/'.$slug.'-cover.jpg',
                'year' => $year,
                'collection' => 'Anarchist Zine Library',
                'subjects' => $subjects,
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $sort++,
            ];
        }

        return $rows;
    }
}
