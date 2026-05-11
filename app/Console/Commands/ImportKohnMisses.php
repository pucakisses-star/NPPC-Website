<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Second-pass import: 26 prisoners from Kohn's Part III that the original
 * paragraph parser missed because their entries used unusual formatting
 * (no line break before the name, hyphenated/quoted nicknames, etc.).
 * Identified by diffing the book's Index against the first import.
 */
final class ImportKohnMisses extends Command {
    protected $signature = 'archive:import-kohn-misses';
    protected $description = 'Second-pass import of Kohn (1994) prisoners missed by archive:import-kohn';

    public function handle(): int {
        $created = 0;
        $skipped = 0;

        foreach ($this->prisoners() as $p) {
            if (Prisoner::where('name', $p['name'])->exists()) {
                $skipped++;

                continue;
            }
            $json = json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $exit = Artisan::call('prisoner:add', ['json' => $json]);
            Artisan::output();
            if ($exit === self::SUCCESS) {
                $created++;
            }
        }

        $this->info("Done. Created={$created} Skipped={$skipped}");

        return self::SUCCESS;
    }

    /**
     * Each record contains the prisoner's full Kohn paragraph as `description`.
     * @return list<array<string, mixed>>
     */
    private function prisoners(): array {
        $era = '1910s';
        $charges = 'Federal prosecution under the Espionage Act of 1917 and/or the Sedition Act of 1918.';
        $tag = ' Sourced from Stephen M. Kohn, "American Political Prisoners: Prosecutions Under the Espionage and Sedition Acts" (Praeger, 1994).';

        $items = [
            // [name, first, middle, last, state, ideologies, affiliation, institution_name, institution_state, sentence, arrest_date, bio]
            ['A. M. Dean', 'A.', 'M.', 'Dean', null, ['Anti-Militarism'], [], null, null, null, null,
                'A. M. Dean was released from federal prison on January 14, 1922, after serving time for an Espionage/Sedition Act-era conviction.'],
            ['Alexander Cournos', 'Alexander', null, 'Cournos', 'Pennsylvania', ['Anarcho-Syndicalism', 'Anti-War', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], 'USP Leavenworth', 'Kansas', 'Ten years in prison and a $30,000 fine.', null,
                'Alexander Cournos, from Pittsburgh, Pennsylvania, was convicted at the mass IWW trial in Chicago. He was sentenced to ten years in prison and a $30,000 fine, and served his time in the Cook County Jail and then Leavenworth Penitentiary.'],
            ['Armin Von Harder', 'Armin', 'Von', 'Harder', 'Washington', ['Anti-Militarism'], [], 'USP McNeil Island', 'Washington', 'Three years in prison.', '1918-10-16',
                'Armin Von Harder was sentenced to three years in prison and served his sentence at McNeil Island Penitentiary beginning October 16, 1918.'],
            ['C. H. Kamann', 'C.', 'H.', 'Kamann', 'Illinois', ['Anti-Militarism', 'Anti-War'], [], null, null, null, '1918-05-09',
                'C. H. Kamann, a teacher from Peoria, Illinois, began serving a prison sentence on May 9, 1918, for "alleged seditious remarks to children in his history class."'],
            ['C. W. Davis', 'C.', 'W.', 'Davis', 'Washington', ['Anarcho-Syndicalism', 'Anti-War', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], 'USP Leavenworth', 'Kansas', 'Ten years in prison and a $30,000 fine.', null,
                'C. W. Davis was secretary of the Marine Transport Workers Industrial Union in Seattle, Washington. He was convicted at the mass IWW trial in Chicago, sentenced to ten years in prison and a $30,000 fine, and served his time in Leavenworth Penitentiary.'],
            ['E. J. Sonnenburg', 'E.', 'J.', 'Sonnenburg', 'Washington', ['Anti-Militarism'], [], 'USP McNeil Island', 'Washington', 'Federal Espionage/Sedition Act sentence.', '1918-07-31',
                'E. J. Sonnenburg served a prison sentence at McNeil Island Penitentiary from July 31, 1918, to June 21, 1920.'],
            ['Emil Herman', 'Emil', null, 'Herman', 'Washington', ['Socialism', 'Anti-War', 'Anti-Militarism'], ['Socialist Party of America'], 'USP McNeil Island', 'Washington', 'Federal prison sentence.', '1918-06-06',
                'Emil Herman served his sentence at McNeil Island Penitentiary from June 6, 1918, to December 24, 1921. He was the former state secretary for the Socialist Party in Washington State.'],
            ['Ernest Henning', 'Ernest', null, 'Henning', 'Kansas', ['Anti-Militarism'], [], 'USP Leavenworth', 'Kansas', 'Three years in prison.', '1919-12-18',
                'Ernest Henning was sentenced to three years in prison and served at Leavenworth Penitentiary beginning December 18, 1919.'],
            ['F. E. McClennigan', 'F.', 'E.', 'McClennigan', 'California', ['Anarcho-Syndicalism', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], 'San Quentin State Prison', 'California', null, null,
                'F. E. McClennigan, from Los Angeles, served a prison sentence at San Quentin under California state syndicalism / Sedition Act-era prosecution.'],
            ['G. Terrill', 'G.', null, 'Terrill', 'California', ['Anarcho-Syndicalism', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], 'San Quentin State Prison', 'California', null, null,
                'G. Terrill, an IWW member, served a prison sentence at San Quentin.'],
            ['Gabriel Probes', 'Gabriel', null, 'Probes', 'New York', ['Anarchism', 'Anti-Militarism'], [], 'USP Atlanta', 'Georgia', 'Federal prison sentence.', '1918-08-01',
                'Gabriel Probes was arrested in August 1918 with co-defendants Jacob Abrams, Mollie Steimer, Samuel Lipman, Hyman Lachowsky, Jacob Schwartz and Hyman Rosansky for distributing leaflets in New York critical of U.S. military intervention against the Russian Revolution. They were charged with violating the Espionage Act of 1917, as amended May 16, 1918, in what became the landmark Abrams v. United States case.'],
            ['Harry Breen', 'Harry', null, 'Breen', 'Kansas', ['Anarcho-Syndicalism', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], null, null, 'Thirty years in prison.', '1920-07-09',
                'Harry Breen organized farm workers for the IWW and was arrested during a union drive in Wakeeney, Kansas, on July 9, 1920. He received a thirty-year prison sentence for being a member of the IWW.'],
            ['Harry Daile', 'Harry', null, 'Daile', null, ['Anti-Militarism'], [], 'USP Leavenworth', 'Kansas', null, null,
                'Harry Daile served his sentence at Leavenworth Penitentiary under an Espionage/Sedition Act-era prosecution.'],
            ['Harry Lloyd', 'Harry', null, 'Lloyd', 'Washington', ['Anarcho-Syndicalism', 'Anti-War', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], 'USP Leavenworth', 'Kansas', 'Five years in prison and a $30,000 fine.', null,
                'Harry Lloyd was branch secretary of the Lumber Workers Industrial Union, Local 500, in Seattle, Washington. He was convicted at the mass IWW trial in Chicago, sentenced to five years in prison and a $30,000 fine, and served his time at Leavenworth Penitentiary.'],
            ['James P. Thompson', 'James', 'P.', 'Thompson', 'Washington', ['Anarcho-Syndicalism', 'Anti-War', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], 'USP Leavenworth', 'Kansas', 'Ten years in prison and a $30,000 fine.', null,
                'James P. Thompson, one of the founders of the IWW, from Seattle, Washington, was convicted at the mass IWW trial in Chicago. He was sentenced to ten years in prison and a $30,000 fine and served in the Cook County Jail and Leavenworth Penitentiary.'],
            ['Joe Coya', 'Joe', null, 'Coya', 'California', ['Anarcho-Syndicalism', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], null, 'California', null, '1924-02-01',
                'Joe Coya was arrested in February 1924 with sixteen other IWW members at a union conference in Sacramento, California. While awaiting trial they were confined together in a single cell, a confinement that became a focal point of IWW protests against California state repression.'],
            ['Norris Tucker', 'Norris', null, 'Tucker', null, ['Anti-Militarism'], [], 'USP Atlanta', 'Georgia', null, null,
                'Norris Tucker served a prison term at Atlanta Penitentiary under an Espionage/Sedition Act-era prosecution.'],
            ['Otto Janson', 'Otto', null, 'Janson', 'California', ['Anti-Militarism'], [], null, null, 'Five years in prison.', '1918-05-10',
                'Otto Janson, from Oakland, California, began serving a five-year prison sentence on May 10, 1918.'],
            ['Rev. William Madison Hicks', 'William', 'Madison', 'Hicks', 'Oklahoma', ['Socialism', 'Anti-War', 'Anti-Militarism'], ['Socialist Party of America'], null, null, 'Twenty years in prison.', null,
                'Rev. William Madison Hicks was a preacher, lawyer, pacifist, and Socialist Party member from Guthrie, Oklahoma. He was sentenced to twenty years in prison for opposing the war.'],
            ['Robert Connellan', 'Robert', null, 'Connellan', 'California', ['Anarcho-Syndicalism', 'Anti-War', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], 'USP Leavenworth', 'Kansas', 'Ten years in prison.', '1918-07-01',
                'Robert Connellan was secretary of the IWW local in Stockton, California. He was convicted at the mass IWW trial in Sacramento and sentenced to ten years in prison. He served at Leavenworth Penitentiary from July 1918.'],
            ['Roy Crane', 'Roy', null, 'Crane', 'Oklahoma', ['Anti-Militarism'], [], 'USP Leavenworth', 'Kansas', 'Seven years in prison.', null,
                'Roy Crane, from Oklahoma, was sentenced to seven years in prison and served at Leavenworth Penitentiary.'],
            ['Roy P. Connor', 'Roy', 'P.', 'Connor', 'Georgia', ['Anarcho-Syndicalism', 'Anti-War', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], 'USP Leavenworth', 'Kansas', 'Ten years in prison.', null,
                'Roy P. Connor was an organizer and delegate for the Agricultural Workers Industrial Union, Local 400, from Kennesaw, Georgia. He was convicted at the mass IWW trial in Sacramento and sentenced to ten years in prison.'],
            ['Samuel Lipman', 'Samuel', null, 'Lipman', 'New York', ['Anarchism', 'Anti-Militarism'], [], 'USP Atlanta', 'Georgia', 'Twenty years in prison.', '1918-08-01',
                'Samuel Lipman was a Russian-born anarchist arrested in New York in August 1918 with co-defendants Jacob Abrams, Mollie Steimer, Hyman Lachowsky, Gabriel Probes, Jacob Schwartz, and Hyman Rosansky for distributing leaflets opposing U.S. military intervention against the Russian Revolution. He was convicted under the Espionage Act of 1917 as amended in May 1918, sentenced to twenty years, and held at Atlanta Penitentiary. His case became part of the landmark Abrams v. United States decision in which Justice Holmes wrote a famous dissent on free speech.'],
            ['Simon Hendrickson', 'Simon', null, 'Hendrickson', 'Nebraska', ['Anarcho-Syndicalism', 'Anti-War', 'Anti-Militarism'], ['Industrial Workers of the World (IWW)'], null, null, null, '1917-11-01',
                'Simon Hendrickson was arrested in November 1917 in the roundup of IWW members in Omaha, Nebraska. After over eighteen months in local jails, all charges against him were dropped.'],
            ['Thomas Cornell', 'Thomas', null, 'Cornell', 'Missouri', ['Socialism', 'Anti-War', 'Anti-Militarism'], ['Socialist Party of America'], null, null, 'Two years in prison.', '1917-10-31',
                'Thomas Cornell, a Socialist from St. Louis, Missouri, began serving a two-year prison sentence on October 31, 1917.'],
            ['Walter Crosby', 'Walter', null, 'Crosby', null, ['Anti-Militarism'], [], null, null, null, null,
                'Walter Crosby was released from federal prison on December 25, 1921, after serving an Espionage/Sedition Act sentence.'],
        ];

        $rows = [];
        foreach ($items as $i) {
            [$name, $first, $middle, $last, $state, $ideologies, $affiliation, $instName, $instState, $sentence, $arrest, $bio] = $i;

            $row = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'description' => $bio.$tag,
                'era' => $era,
                'ideologies' => $ideologies,
            ];
            if ($middle) {
                $row['middle_name'] = $middle;
            }
            if ($state) {
                $row['state'] = $state;
            }
            if ($affiliation) {
                $row['affiliation'] = $affiliation;
            }

            $case = ['charges' => $charges];
            if ($instName) {
                $case['institution_name'] = $instName;
            }
            if ($instState) {
                $case['institution_state'] = $instState;
            }
            if ($sentence) {
                $case['sentence'] = $sentence;
            }
            if ($arrest) {
                $case['arrest_date'] = $arrest;
            }
            $row['cases'] = [$case];

            $rows[] = $row;
        }

        return $rows;
    }
}
