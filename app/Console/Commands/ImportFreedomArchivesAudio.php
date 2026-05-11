<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

final class ImportFreedomArchivesAudio extends Command {
    protected $signature = 'archive:import-fa-audio';
    protected $description = 'Import 16 political-prisoner MP3s from Freedom Archives';

    public function handle(): int {
        $created = 0;
        $updated = 0;

        foreach ($this->records() as $r) {
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
        // [fa_record_id, filename, title, description, year, subjects]
        $items = [
            [30409, 'fa-30409-george_jackson_intro.mp3', 'George Jackson — Intro [Prisons on Fire]', 'Intro to the Freedom Archives audio documentary "Prisons on Fire" on George Jackson and the Attica/San Quentin prison rebellions.', null, ['George Jackson', 'Black Liberation', 'Prison Strikes', 'Attica']],
            [30419, 'fa-30419-20_peltier.mp3', 'Leonard Peltier — What Warriors Do', 'American Indian Movement leader Leonard Peltier on what warriors do — from the Freedom Archives audio sample collection.', null, ['Leonard Peltier', 'AIM', 'Indigenous Resistance', 'Political Prisoners']],
            [30426, 'fa-30426-ruchell_magee.mp3', 'Ruchell "Cinque" Magee', 'Recording by or about Ruchell "Cinque" Magee, the longest-held U.S. political prisoner before his 2023 release — incarcerated from 1963 to 2023, the latter half for his role in the August 1970 Marin County courthouse uprising.', null, ['Ruchell Magee', 'Black Liberation', 'San Quentin', 'Political Prisoners']],
            [30499, 'fa-30499-georgejackson.mp3', 'George Jackson: 30 Years Later', 'Freedom Archives audio program on the 30th anniversary of George Jackson\'s killing at San Quentin (1971–2001).', 2001, ['George Jackson', 'Black Liberation', 'San Quentin', 'Soledad Brothers']],
            [34436, 'fa-34436-pm208_hbell_interview.mp3', 'Interview with Herman Bell', 'Out of Control Lesbian Committee to Support Women Prisoners interview with Herman Bell, the Black Liberation Army veteran imprisoned from 1973 until his 2018 parole for his role in the BLA\'s 1971 Foster-Laurie case in New York.', null, ['Herman Bell', 'Black Liberation Army', 'New York 3', 'Political Prisoners']],
            [34437, 'fa-34437-pm414_sundiata_acoli.mp3', 'Bonnie Kerness Interviews Sundiata Acoli', 'Out of Control / American Friends Service Committee\'s Bonnie Kerness conducts an interview with Sundiata Acoli at Allenwood Federal Prison surrounding his case and conditions of confinement.', null, ['Sundiata Acoli', 'Black Liberation Army', 'Political Prisoners', 'Allenwood']],
            [34443, 'fa-34443-georgejackson.mp3', 'The Struggle Within: 30th Anniversary of the Murder of George Jackson', 'Freedom Archives feature documenting the 30th anniversary of George Jackson\'s killing at San Quentin — interviews, archival recordings, and reflection on the California prison movement.', 2001, ['George Jackson', 'Black Liberation', 'San Quentin', 'Attica']],
            [34448, 'fa-34448-pm267_baraldini.mp3', 'The Case of Silvia Baraldini', 'Out of Control Lesbian Committee to Support Women Prisoners audio program on Italian anti-imperialist political prisoner Silvia Baraldini, held in the U.S. from 1982 to 1999 (including in the notorious Lexington High Security Unit) before her transfer to Italy.', null, ['Silvia Baraldini', 'Anti-Imperialism', 'Lexington HSU', 'Women Political Prisoners']],
            [34450, 'fa-34450-pm407_political_prisoners_control_units.mp3', 'What is a Political Prisoner? / Definition of Political Prisoners and Control Units', 'Out of Control program defining the term "political prisoner" and discussing the U.S. control-unit prison system that disproportionately holds political prisoners and prisoners of war.', null, ['Political Prisoners', 'Control Units', 'Prison Conditions']],
            [35091, 'fa-35091-hugo-pinell-final.mp3', 'Hugo Pinell — Rest in Power', 'Memorial recording for San Quentin 6 / Black liberation political prisoner Hugo "Yogi" Pinell, killed in August 2015 at age 71 less than a week after his transfer out of solitary confinement.', 2015, ['Hugo Pinell', 'San Quentin 6', 'Black Liberation', 'Political Prisoners']],
            [35096, 'fa-35096-george-on-fascism-final.mp3', 'George Jackson on Fascism', 'Recorded reading of George Jackson\'s writings on fascism, from the Freedom Archives Attica/Soledad collection.', null, ['George Jackson', 'Black Liberation', 'Anti-Fascism']],
            [35097, 'fa-35097-soledad-brothers.mp3', 'George Jackson on the Soledad Brothers', 'Recorded reading of George Jackson on the Soledad Brothers — Jackson, Fleeta Drumgo, and John Clutchette, charged with killing a Soledad guard in 1970.', null, ['George Jackson', 'Soledad Brothers', 'Black Liberation', 'California Prison Struggles']],
            [35098, 'fa-35098-we-are-all-together.mp3', 'George Jackson on Prisoner Unity', 'Recorded reading of George Jackson on prisoner unity across racial and political lines.', null, ['George Jackson', 'Prison Solidarity', 'Black Liberation']],
            [35124, 'fa-35124-pm430_npr_lexington.mp3', 'NPR Report on the Lexington Control Unit', 'NPR radio report on the Federal Bureau of Prisons\' Women\'s High Security Unit at Lexington, Kentucky — the underground control unit Amnesty International condemned as violating UN minimum rules and that was eventually closed in 1988 after sustained national and international human-rights pressure.', null, ['Lexington HSU', 'Women Political Prisoners', 'Control Units', 'Prison Conditions']],
            [35125, 'fa-35125-pm438_s_rosenberg.mp3', 'Interview with Susan Rosenberg about Conditions in Lexington Control Unit', 'Interview with anti-imperialist political prisoner Susan Rosenberg on conditions in the Lexington Women\'s High Security Unit, where she was held from 1986 to 1988.', null, ['Susan Rosenberg', 'Lexington HSU', 'May 19 Communist Organization', 'Anti-Imperialism']],
            [35126, 'fa-35126-pm184_lexington_women.mp3', 'Lexington Prison Interviews (1987)', '1987 interviews with the women held at the Federal Bureau of Prisons\' Lexington High Security Unit — Susan Rosenberg, Silvia Baraldini, and Alejandrina Torres — documenting the underground control unit\'s sensory deprivation, 23-hour cell time, strip searches by male guards, and other conditions condemned by Amnesty International.', 1987, ['Lexington HSU', 'Susan Rosenberg', 'Silvia Baraldini', 'Alejandrina Torres', 'Women Political Prisoners']],
        ];

        $rows = [];
        $sort = 300;
        foreach ($items as $i) {
            [$id, $filename, $title, $desc, $year, $subjects] = $i;
            $rows[] = [
                'slug' => 'freedom-archives-audio-'.$id,
                'title' => $title,
                'description' => $desc.' Sourced from search.freedomarchives.org.',
                'record_type' => 'audio',
                'source_format' => 'mp3',
                'file' => '/audio/freedom-archives/'.$filename,
                'year' => $year,
                'collection' => 'Freedom Archives',
                'subjects' => $subjects,
                'is_digitized' => true,
                'published' => true,
                'sort_order' => $sort++,
            ];
        }

        return $rows;
    }
}
