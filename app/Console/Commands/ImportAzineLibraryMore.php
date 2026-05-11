<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportAzineLibraryMore extends Command {
    protected $signature = 'archive:import-azine-library-more';
    protected $description = 'Import a second batch of 14 US-political-prisoner zines from azinelibrary.org (idempotent by slug)';

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
        // [pdf_slug, title, source_format, year, subjects, description]
        $items = [
            ['anarchist-prisoner-penpal-program', 'Anarchist Prisoner Penpal Program', 'pamphlet', null, ['Prisoner Support', 'Anarchist Black Cross', 'Pen-Pal'], 'Guide to corresponding with anarchist and other political prisoners held in U.S. prisons through the Anarchist Black Cross penpal network.'],
            ['control-unit-prisons', 'Control Unit Prisons', 'pamphlet', null, ['Prison Conditions', 'Control Units', 'Marion', 'Political Prisoners'], 'Pamphlet on the U.S. control-unit prison system (Marion, ADX Florence, Lexington HSU), the long-term-isolation facilities where many U.S. political prisoners and prisoners of war have been held.'],
            ['its-like-living-in-a-black-hole', '"It\'s Like Living in a Black Hole" — Women of Color and Solitary Confinement in the Prison-Industrial Complex', 'pamphlet', null, ['Women Prisoners', 'Solitary Confinement', 'Prison Conditions', 'Race'], 'Research zine on the disproportionate use of solitary confinement against women of color in the U.S. prison-industrial complex.'],
            ['new-draft-proposal-for-an-abc-network', 'New Draft Proposal for an Anarchist Black Cross Network', 'pamphlet', null, ['Anarchist Black Cross', 'Prisoner Support', 'Organizing'], 'Proposal for restructuring the continental Anarchist Black Cross network in the United States and Canada.'],
            ['political-prisoners-and-black-liberation', 'Political Prisoners and Black Liberation', 'pamphlet', null, ['Political Prisoners', 'Black Liberation', 'New Afrikan'], 'Pamphlet on the relationship between Black liberation struggle in the United States and the existence of Black political prisoners, including the Black Panther Party, Black Liberation Army, and Republic of New Afrika prisoners.'],
            ['richmond-abc-trifold', 'Richmond Anarchist Black Cross Trifold', 'flyer', null, ['Anarchist Black Cross', 'Prisoner Support'], 'Trifold introduction to the Richmond, Virginia chapter of the Anarchist Black Cross.'],
            ['solidarity-with-5e3-trifold', 'Solidarity with the 5e3 — Trifold', 'flyer', null, ['Anarchist', 'Political Prisoners', 'Movement Defense'], 'Trifold pamphlet supporting the "5e3" defendants.'],
            ['up-against-the-wall-philadelphia', 'Up Against the Wall: A History of Resistance to Policing in Philadelphia', 'pamphlet', null, ['Anti-Police', 'Black Liberation', 'MOVE', 'Philadelphia'], 'Historical zine on resistance to the Philadelphia Police Department — covering MOVE, Frank Rizzo\'s tenure, the killing of Black Panther leaders, the 1985 MOVE bombing, and the continuing struggle against police violence in the city.'],
            ['we-will-not-cooperate', 'We Will Not Cooperate', 'pamphlet', null, ['Grand Jury Resistance', 'Movement Defense', 'Anti-Repression'], 'Statement and guide on refusing to cooperate with federal grand juries, written from the U.S. anarchist tradition of non-collaboration with state investigations.'],
            ['what-better-time-than-now', 'What Better Time Than Now: Notes on Consciousness and Unity in US Cities and Prisons', 'pamphlet', null, ['Prison Strikes', 'Black Liberation', 'Anti-Prison'], 'Notes on the relationship between U.S. urban rebellions and prisoner organizing — connecting the 2010s Black Lives Matter uprisings with prison work-stoppages and hunger strikes.'],
            ['why-misogynists-make-great-informants', 'Why Misogynists Make Great Informants — How Gender Violence on the Left Enables State Violence in Radical Movements', 'pamphlet', null, ['Anti-Repression', 'Movement Defense', 'Gender', 'Informants'], 'Influential 2010 essay by Courtney Desiree Morris on how patriarchy within U.S. radical movements creates conditions for state infiltration and informant recruitment.'],
            ['police-state-funnies-columbus-2010', 'Police State Funnies — Columbus, OH, June 2010', 'pamphlet', 2010, ['Anti-Police', 'Columbus Ohio', 'Comics'], 'Anti-police comic zine documenting Columbus, Ohio police violence and protest repression around the June 2010 anarchist gathering.'],
            ['attacking-prisons-at-point-of-production', 'Attacking Prisons at the Point of Production: A Brief Look at Militant Actions Against the Military-Industrial Complex', 'pamphlet', null, ['Anti-Imperialism', 'Anti-Militarism', 'Earth Liberation', 'Plowshares'], 'Historical survey of U.S. militant direct actions against weapons manufacturers, military bases, and prison-construction companies — from the Plowshares movement through the Earth Liberation Front and the United Freedom Front.'],
            ['ongoing-police-repression-central-valley', 'Ongoing Police Repression in the Central Valley', 'pamphlet', null, ['Anti-Police', 'California', 'State Repression'], 'Account of ongoing police repression in California\'s Central Valley targeting organizers, farmworkers, and immigrant communities.'],
        ];

        $rows = [];
        $sort = 220; // sit just behind the first azine batch (200..)

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
