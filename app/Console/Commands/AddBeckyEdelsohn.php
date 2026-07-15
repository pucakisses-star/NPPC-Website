<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Becky (Rebecca) Edelsohn (1892–1973) — anarchist agitator who grew up in Emma
 * Goldman's household, was Alexander Berkman's companion, and organized with the
 * IWW and the Ferrer Center. She was jailed twice in 1914, each time staging a
 * hunger strike:
 *   1. Spring 1914 — held at the Queens County Jail in Long Island City after a
 *      disorderly-conduct arrest at an IWW free-speech meeting, where she made
 *      what The New York Times called the first hunger strike attempted by a
 *      woman in the United States, and was freed on April 27, 1914.
 *   2. Summer 1914 — arrested June 6, 1914 at the anti-Rockefeller
 *      demonstrations in Tarrytown after the Ludlow Massacre; sentenced July 20;
 *      sent to the Workhouse on Blackwell's Island, where she carried on a
 *      hunger strike of more than 27 days (publicized by Alexander Berkman in
 *      Mother Earth as "the first political hunger striker in America") before
 *      being freed on the $300 bond about August 21, 1914.
 *
 * Idempotent: creates the prisoner if missing and creates-or-updates each of the
 * two cases (matched by a distinctive charge phrase), so it is safe to re-run.
 * Sources: Wikipedia; NYT, "An I.W.W. Heroine, Although She Ate" (Apr. 28, 1914);
 * Alexander Berkman, "The First Political Hunger Striker in America," Mother
 * Earth vol. 9 no. 6 (Aug. 1914).
 */
class AddBeckyEdelsohn extends Command
{
    protected $signature = 'prisoners:add-becky-edelsohn';

    protected $description = 'Add Becky Edelsohn and her two 1914 hunger-strike imprisonments (Queens County Jail; Blackwell\'s Island)';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Becky Edelsohn';

            $queensJail = Institution::firstOrCreate(
                ['name' => 'Queens County Jail'],
                ['city' => 'Long Island City', 'state' => 'New York']
            );
            $workhouse = Institution::firstOrCreate(
                ['name' => "Workhouse, Blackwell's Island"],
                ['city' => 'New York', 'state' => 'New York']
            );

            $prisoner = Prisoner::withUnderReview()->where('name', $name)->first();

            if (! $prisoner) {
                $prisoner = Prisoner::create([
                    'name' => $name,
                    'first_name' => 'Becky',
                    'last_name' => 'Edelsohn',
                    'aka' => 'Rebecca Edelsohn; Becky Edelson',
                    'description' => 'Becky Edelsohn (born Rebecca Edelsohn) was an American anarchist agitator. Born in 1892 in Riga in the Russian Empire to a Latvian-Jewish family, she came to New York as a child, lived in Emma Goldman\'s household as a teenager, and from 1906 was the companion of Alexander Berkman. She organized with the Industrial Workers of the World and the Ferrer Center and was arrested repeatedly from a young age — at a 1906 meeting on Leon Czolgosz, at a 1908 Cooper Union meeting where she defended Ben Reitman, and on May 23, 1909, alongside Leopold Bergman, for disorderly conduct at an Emma Goldman lecture broken up by police. She was jailed twice in 1914. That spring, arrested for disorderly conduct at an Industrial Workers of the World free-speech meeting, she was held at the Queens County Jail in Long Island City and staged what The New York Times called the first hunger strike attempted by a woman in the United States, winning her release on April 27, 1914. Then, after the Ludlow Massacre, she helped lead the anti-Rockefeller demonstrations at Tarrytown, New York. Arrested there on June 6, 1914 with Arthur Caron and Charles Plunkett for speeches in the public square, she was convicted of disorderly conduct for calling John D. Rockefeller Jr. a "multi-murderer"; at her hearing she rejected counsel and told the court, "This town is owned by John D. Rockefeller. We don\'t expect justice here." Sentenced on July 20, 1914 to a $300 peace bond or ninety days, she refused the bond, was sent to the Workhouse on Blackwell\'s Island, and carried on a hunger strike of more than twenty-seven days — publicized by Alexander Berkman in Mother Earth as "the first political hunger striker in America" — before friends raised the $300 bond that freed her about August 21, 1914. She later married Charles Plunkett, and died in 1973.',
                    'gender' => 'Female',
                    'race' => 'White',
                    'state' => 'New York',
                    'era' => '1910s',
                    'ideologies' => ['Anarchism', 'Free speech'],
                    'affiliation' => ['Industrial Workers of the World', 'Ferrer Center'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);

                // Only years are certain -> year precision (shows "1892" / "1973").
                $prisoner->setPartialDate('birthdate', 1892);
                $prisoner->setPartialDate('death_date', 1973);
                $prisoner->save();

                $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            } else {
                $this->line('Already exists: '.$name.' — updating the 1914 cases.');
            }

            // The model computes imprisoned_for_days from incarceration_date and
            // release_date on save; hand-set day counts are ignored. So the dates
            // below drive how long she is shown as jailed.

            // Case 1 — Spring 1914: Queens County Jail hunger strike. The exact
            // arrest/entry date is not documented, so incarceration_date is left
            // null (no computed duration) and only the April 27, 1914 release is
            // recorded.
            $queensCase = $prisoner->cases()->where('charges', 'like', '%free-speech meeting%')->first()
                ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $queensCase->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $queensJail->id,
                'charges' => 'Disorderly conduct — arrested at an Industrial Workers of the World free-speech meeting in New York City.',
                'convicted' => 'Jailed at the Queens County Jail in Long Island City.',
                'sentence' => 'Held at the Queens County Jail, where she made what The New York Times called the first hunger strike attempted by a woman in the United States; she was freed on April 27, 1914.',
                'release_date' => '1914-04-27',
            ]);
            $queensCase->save();

            // Case 2 — Summer 1914: Tarrytown arrest, Blackwell's Island term.
            // Arrested June 6, 1914; jailed after the July 20 sentencing; freed on
            // the $300 bond about August 21, 1914 — roughly a month, matching her
            // 27+ day hunger strike.
            $tarrytownCase = $prisoner->cases()->where('charges', 'like', '%Tarrytown%')->first()
                ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $tarrytownCase->fill([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $workhouse->id,
                'charges' => 'Disorderly conduct — for anti-Rockefeller speeches in the public square at Tarrytown, New York during the demonstrations following the Ludlow Massacre, including calling John D. Rockefeller Jr. a "multi-murderer."',
                'convicted' => 'Yes — convicted of disorderly conduct; the sentence was sustained on appeal by Justice Crane of the Appellate Division.',
                'sentence' => 'Sentenced to a $300 peace bond or 90 days in jail; she refused the bond and was sent to the Workhouse on Blackwell\'s Island, where she carried on a hunger strike of more than 27 days — publicized by Alexander Berkman in Mother Earth as "the first political hunger striker in America." She served roughly a month before friends raised the $300 bond that freed her about August 21, 1914.',
                'arrest_date' => '1914-06-06',
                'sentenced_date' => '1914-07-20',
                'incarceration_date' => '1914-07-20',
                'release_date' => '1914-08-21',
            ]);
            $tarrytownCase->save();

            $this->info('Cases set: Queens County Jail (freed 1914-04-27); Blackwell\'s Island 1914-07-20 to 1914-08-21 ('.$tarrytownCase->imprisoned_for_days.' days).');
        });

        return self::SUCCESS;
    }
}
