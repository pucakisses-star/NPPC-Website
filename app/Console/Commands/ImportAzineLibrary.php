<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportAzineLibrary extends Command {
    protected $signature = 'archive:import-azine-library';
    protected $description = 'Import 18 US-political-prisoner zines from azinelibrary.org as ArchiveRecord rows (idempotent by slug)';

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
        // [pdf_slug, title, source_format, year, date, volume, subjects, description]
        $items = [
            ['abu-jamal-news-3', 'Abu-Jamal News, Issue #3', 'periodical', null, null, 'Issue #3', ['Mumia Abu-Jamal', 'Political Prisoners', 'Black Liberation'], 'Newsletter issue dedicated to Mumia Abu-Jamal, the journalist and former Black Panther held on Pennsylvania\'s death row from 1982 until 2011, now serving a life sentence.'],
            ['active-solidarity', 'Active Solidarity', 'pamphlet', null, null, null, ['Political Prisoners', 'Prisoner Support', 'Anarchist'], 'Anarchist pamphlet on active solidarity with political prisoners.'],
            ['anarchist-black-cross-information-and-resources', 'Anarchist Black Cross — Information and Resources', 'pamphlet', null, null, null, ['Anarchist Black Cross', 'Political Prisoners', 'Prisoner Support'], 'Introductory resource on the Anarchist Black Cross network and its political-prisoner support work.'],
            ['anarchist-black-cross-an-introduction', 'Anarchist Black Cross — An Introduction', 'pamphlet', null, null, null, ['Anarchist Black Cross', 'Political Prisoners', 'Prisoner Support'], 'Brief introduction to the Anarchist Black Cross tradition and its role in supporting political prisoners and prisoners of war.'],
            ['autonomous-resistance-to-slavery-and-colonization', 'Autonomous Resistance to Slavery and Colonization', 'pamphlet', null, null, null, ['Black Liberation', 'Indigenous Resistance', 'Anti-Colonialism'], 'Anarchist analysis of autonomous resistance to slavery and colonization in the United States.'],
            ['eric-mcdavid-support-zine-for-june-11th-2012', 'Eric McDavid Support Zine — June 11th 2012', 'pamphlet', 2012, '2012-06-11', null, ['Eric McDavid', 'Green Scare', 'Political Prisoners', 'Earth Liberation'], 'Support zine for Eric McDavid, an environmental activist entrapped by FBI informant "Anna" and sentenced in 2008 to 19 years on Earth Liberation Front conspiracy charges. He was released in 2015 after government misconduct was uncovered. Published for the June 11th International Day of Solidarity with Long-Term Anarchist Prisoners.'],
            ['exposinglittle-guantanamoinside-the-cmu', 'Exposing "Little Guantánamo" — Inside the CMU', 'pamphlet', null, null, null, ['Communications Management Unit', 'Political Prisoners', 'Prison Conditions', 'Torture'], 'Investigative pamphlet on the Communications Management Units (CMUs), federal prison units at Terre Haute and Marion that disproportionately hold Muslim and politically active prisoners under severely restricted communications.'],
            ['fire-to-the-prisons-4', 'Fire to the Prisons, Issue #4', 'periodical', null, null, 'Issue #4', ['Anti-Prison', 'Insurrectionary Anarchism', 'Political Prisoners'], 'Insurrectionary anarchist anti-prison periodical published in the United States.'],
            ['fire-to-the-prisons-5', 'Fire to the Prisons, Issue #5', 'periodical', null, null, 'Issue #5', ['Anti-Prison', 'Insurrectionary Anarchism', 'Political Prisoners'], 'Insurrectionary anarchist anti-prison periodical published in the United States.'],
            ['fire-to-the-prisons-6', 'Fire to the Prisons, Issue #6', 'periodical', null, null, 'Issue #6', ['Anti-Prison', 'Insurrectionary Anarchism', 'Political Prisoners'], 'Insurrectionary anarchist anti-prison periodical published in the United States.'],
            ['fire-to-the-prisons-7', 'Fire to the Prisons, Issue #7', 'periodical', null, null, 'Issue #7', ['Anti-Prison', 'Insurrectionary Anarchism', 'Political Prisoners'], 'Insurrectionary anarchist anti-prison periodical published in the United States.'],
            ['five-myths-about-the-asheville-11', 'Five Myths about the Asheville 11', 'pamphlet', 2010, '2010-05-01', null, ['Asheville 11', 'Political Prisoners', 'Anarchist'], 'Defense pamphlet on the Asheville 11, anarchists arrested in May 2010 for property destruction during the May Day demonstrations in Asheville, North Carolina.'],
            ['for-the-pacific-northwest-grand-jury-resisters', 'For the Pacific Northwest Grand Jury Resisters', 'pamphlet', 2012, null, null, ['Grand Jury Resistance', 'Political Prisoners', 'Anarchist'], 'Solidarity zine for the Pacific Northwest grand jury resisters — Matt Duran, KteeO Olejnik, Maddy Pfeiffer, Leah-Lynn Plante — who were imprisoned in 2012 for refusing to testify before federal grand juries investigating anarchist activity in the Pacific Northwest.'],
            ['marie-mason-support-zine-for-june11th-2012', 'Marie Mason Support Zine — June 11th 2012', 'pamphlet', 2012, '2012-06-11', null, ['Marius Mason', 'Green Scare', 'Earth Liberation', 'Political Prisoners'], 'Support zine for Marie Mason (now Marius Mason), the Earth Liberation Front prisoner serving 22 years for two arsons of GMO-research targets. Published for the June 11th International Day of Solidarity with Long-Term Anarchist Prisoners. (Marie was Marius\'s pre-transition name.)'],
            ['midwest-books-to-prisoners-zine-1-march-2010', 'Midwest Books to Prisoners Zine, Issue #1 (March 2010)', 'periodical', 2010, '2010-03-01', 'Issue #1', ['Books to Prisoners', 'Prisoner Support', 'Anti-Prison'], 'First issue of the Midwest Books to Prisoners zine, a Chicago-based volunteer collective that mails free books to incarcerated people.'],
            ['negate-city-go-to-trial-crash-the-justice-system', 'Negate City: Go to Trial, Crash the Justice System', 'pamphlet', null, null, null, ['Court Solidarity', 'Anti-Prison', 'Movement Defense'], 'Movement-defense pamphlet arguing for refusing plea bargains and taking cases to trial as a strategy of overwhelming the criminal-legal system.'],
            ['selected-writings-of-gender-anarky', 'Selected Writings of Gender Anarky', 'pamphlet', null, null, null, ['Gender Anarky', 'Queer Liberation', 'Anti-Prison', 'Political Prisoners'], 'Compilation of writings by Gender Anarky, the radical incarcerated trans women\'s organization rooted in U.S. women\'s prisons.'],
            ['some-people-push-back-on-the-justice-of-roosting-chickens', 'Some People Push Back: On the Justice of Roosting Chickens', 'article', 2001, '2001-09-12', null, ['Ward Churchill', 'Anti-Imperialism'], 'Ward Churchill\'s controversial 2001 essay arguing that the September 11 attacks were a response to U.S. foreign policy. The essay made Churchill the target of a sustained right-wing campaign that resulted in his 2007 termination from the University of Colorado, widely characterized as political persecution.'],
        ];

        $rows = [];
        $sort = 200; // sort behind 4StruggleMag (0..) and ABCF library (100..)

        foreach ($items as $i) {
            [$slug, $title, $sourceFormat, $year, $date, $volume, $subjects, $desc] = $i;

            $rows[] = [
                'slug' => 'azine-'.$slug,
                'title' => $title,
                'description' => $desc.' Sourced from azinelibrary.org (Anarchist Zine Library).',
                'record_type' => 'document',
                'source_format' => $sourceFormat,
                'file' => '/pdfs/azine-library/'.$slug.'.pdf',
                'thumbnail' => '/images/archive/azine-library/'.$slug.'-cover.jpg',
                'year' => $year,
                'date' => $date,
                'collection' => 'Anarchist Zine Library',
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
