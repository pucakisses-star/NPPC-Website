<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Three anarchists arrested alongside Becky Edelsohn:
 *   - Leopold Bergman — arrested with her on May 23, 1909 at an Emma Goldman
 *     lecture broken up by police (disorderly conduct); disposition undocumented.
 *   - Arthur Caron and Charles Plunkett — arrested with her on May 30, 1914 in
 *     the Tarrytown free-speech fight against Rockefeller after the Ludlow
 *     Massacre; jailed and released on bail by June 8, 1914.
 *
 * Caron died in the July 4, 1914 Lexington Avenue explosion. Plunkett later left
 * the movement and became a biologist. Jail durations are recorded where the
 * documented arrest-and-bail dates bracket them; where a disposition is unknown
 * it is stated as such rather than guessed. Idempotent (skips existing by name).
 * Sources: Wikipedia; L. D. Abbott, "The Fight for Free Speech in Tarrytown,"
 * Mother Earth vol. 9 no. 4 (June 1914); E. G. Flynn, Voice of the People (July
 * 21, 1914); Library of Congress (Bain Collection); NYT (May 24, 1909).
 */
class AddEdelsohnCodefendants extends Command
{
    protected $signature = 'prisoners:add-edelsohn-codefendants';

    protected $description = 'Add Arthur Caron, Charles Plunkett, and Leopold Bergman (arrested with Becky Edelsohn)';

    public function handle(): int
    {
        $tarrytown = Institution::firstOrCreate(['name' => 'Tarrytown Jail'], ['city' => 'Tarrytown', 'state' => 'New York']);

        $this->addCaron($tarrytown);
        $this->addPlunkett($tarrytown);
        $this->addBergman();

        return self::SUCCESS;
    }

    private function addCaron(Institution $tarrytown): void
    {
        DB::transaction(function () use ($tarrytown) {
            $name = 'Arthur Caron';
            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Arthur',
                'last_name' => 'Caron',
                'description' => 'Arthur Caron was a French-Canadian-born anarchist and member of the Industrial Workers of the World. Born on December 16, 1883 in Quebec, he worked for years as a weaver in Fall River, Massachusetts, and after the deaths of his wife and infant child moved to New York City, where he threw himself into the 1914 unemployed movement led by Frank Tannenbaum. During one demonstration he was seized by detectives, beaten so badly his nose was broken, and hospitalized. In late May 1914, after the Ludlow Massacre, he joined the free-speech fight at Tarrytown, New York against John D. Rockefeller; arrested for trying to speak at Fountain Square, he was jailed with eleven others — among them Becky Edelsohn and Charles Plunkett — and released on bail by June 8, 1914. He was killed on July 4, 1914 in the Lexington Avenue explosion at 1626 Lexington Avenue in Manhattan, when a dynamite bomb — reportedly intended for the Rockefeller estate in retaliation for Ludlow — detonated prematurely as he and fellow anarchists Charles Berg and Carl Hanson assembled it; an uninvolved tenant, Marie Chavez, was also killed. Some 5,000 mourners later gathered in Union Square, addressed by Alexander Berkman and Becky Edelsohn.',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'New York',
                'era' => '1910s',
                'ideologies' => ['Anarchism'],
                'affiliation' => ['Industrial Workers of the World', 'Ferrer Center'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            $prisoner->birthdate = '1883-12-16';
            $prisoner->death_date = '1914-07-04';
            $prisoner->save();

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $tarrytown->id,
                'charges' => 'Disorderly conduct, blocking traffic, and endangering the public health — for attempting to hold an open-air meeting at Fountain Square in Tarrytown, New York denouncing John D. Rockefeller after the Ludlow Massacre.',
                'convicted' => 'Arrested May 30, 1914 with eleven others (among them Becky Edelsohn and Charles Plunkett); the group refused to recognize the court and was jailed, then released on bail by June 8, 1914. No completed sentence is documented for him.',
                'arrest_date' => '1914-05-30',
                'incarceration_date' => '1914-05-30',
                'release_date' => '1914-06-08',
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });
    }

    private function addPlunkett(Institution $tarrytown): void
    {
        DB::transaction(function () use ($tarrytown) {
            $name = 'Charles Plunkett';
            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Charles',
                'middle_name' => 'Robert',
                'last_name' => 'Plunkett',
                'description' => 'Charles Robert Plunkett (1892–1981) was a young anarchist in the Ferrer Center / Modern School circle in New York and a member of the Industrial Workers of the World. After the Ludlow Massacre he was active in the 1914 unemployed movement and the anti-Rockefeller agitation, and wrote the militant essay "Dynamite!" in Mother Earth (July 1914). On May 30, 1914 he was one of twelve arrested in the Tarrytown free-speech fight — with Becky Edelsohn (whom he later married) and Arthur Caron — for attempting to speak at Fountain Square; charged with disorderly conduct, the group was jailed and released on bail by June 8, 1914. He was a conspirator in the Lexington Avenue bomb plot that killed Caron on July 4, 1914 but was not present at the explosion and was never charged for it. He later left the anarchist movement and became a biologist and geneticist, publishing work on Drosophila genetics and a biology textbook, and died in 1981.',
                'gender' => 'Male',
                'state' => 'New York',
                'era' => '1910s',
                'ideologies' => ['Anarchism'],
                'affiliation' => ['Industrial Workers of the World', 'Ferrer Center'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);
            // Only years are certain -> year precision.
            $prisoner->setPartialDate('birthdate', 1892);
            $prisoner->setPartialDate('death_date', 1981);
            $prisoner->save();

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $tarrytown->id,
                'charges' => 'Disorderly conduct (also charged with blocking traffic and endangering the public health) — for attempting to hold an open-air meeting at Fountain Square in Tarrytown, New York denouncing John D. Rockefeller after the Ludlow Massacre.',
                'convicted' => 'Arrested May 30, 1914 with eleven others (among them Becky Edelsohn and Arthur Caron); jailed, then released on bail by June 8, 1914. His individual sentence, if any, is not documented.',
                'arrest_date' => '1914-05-30',
                'incarceration_date' => '1914-05-30',
                'release_date' => '1914-06-08',
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });
    }

    private function addBergman(): void
    {
        DB::transaction(function () {
            $name = 'Leopold Bergman';
            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Leopold',
                'last_name' => 'Bergman',
                'description' => 'Leopold Bergman was a New York anarchist — very likely the Yiddish-language anarchist writer and editor of that name active around 1908–1910 (author of the pamphlet "Scientific Anarchism" and editor of Der frayer gedank, "Free Thought"). On May 23, 1909 he was arrested with the teenage anarchist Becky Edelsohn and charged with disorderly conduct at an Emma Goldman lecture on modern drama at Lexington Hall (109–111 East 116th Street, New York) that police broke up on the pretext that Goldman had strayed from her announced subject. The disposition of the charge against him — conviction, fine, or jail term — is not documented in the available sources.',
                'gender' => 'Male',
                'race' => 'White',
                'state' => 'New York',
                'era' => '1900s',
                'ideologies' => ['Anarchism'],
                'in_custody' => false,
                'released' => true,
                'in_exile' => false,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Disorderly conduct — arrested with Becky Edelsohn at an Emma Goldman lecture at Lexington Hall (109–111 East 116th Street, New York) that police broke up on May 23, 1909.',
                'convicted' => 'Arrested and charged with disorderly conduct; the disposition is not documented in the available sources.',
                'arrest_date' => '1909-05-23',
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });
    }
}
