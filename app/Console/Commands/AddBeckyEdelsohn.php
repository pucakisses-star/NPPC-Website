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
 * IWW and the Ferrer Center. Repeatedly arrested from 1906 on, she is best known
 * for the June 1914 anti-Rockefeller demonstrations in Tarrytown, New York after
 * the Ludlow Massacre. Convicted of disorderly conduct for calling John D.
 * Rockefeller Jr. a "multi-murderer," she was offered a $300 peace bond or 90
 * days in jail; refusing the bond, she was sent to the Workhouse on Blackwell's
 * Island and carried on a hunger strike of more than 27 days — reported by The
 * New York Times as the first hunger strike attempted by a woman in the United
 * States.
 *
 * Idempotent (skips by name). Sources: Wikipedia; Alexander Berkman, "The First
 * Political Hunger Striker in America," Mother Earth vol. 9 no. 6 (Aug. 1914).
 */
class AddBeckyEdelsohn extends Command
{
    protected $signature = 'prisoners:add-becky-edelsohn';

    protected $description = 'Add Becky Edelsohn, the anarchist whose 1914 hunger strike was the first by a woman in the US';

    public function handle(): int
    {
        DB::transaction(function () {
            $name = 'Becky Edelsohn';

            if (Prisoner::where('name', $name)->exists()) {
                $this->warn('Skipped (already exists): '.$name);

                return;
            }

            $prisoner = Prisoner::create([
                'name' => $name,
                'first_name' => 'Becky',
                'last_name' => 'Edelsohn',
                'aka' => 'Rebecca Edelsohn; Becky Edelson',
                'description' => 'Becky Edelsohn (born Rebecca Edelsohn) was an American anarchist agitator. Born in 1892 in Riga in the Russian Empire to a Latvian-Jewish family, she came to New York as a child, lived in Emma Goldman\'s household as a teenager, and from 1906 was the companion of Alexander Berkman. She organized with the Industrial Workers of the World and the Ferrer Center and was arrested repeatedly from a young age — at a 1906 meeting on Leon Czolgosz, at a 1908 Cooper Union meeting where she defended Ben Reitman, and in 1909 for disorderly conduct at an Emma Goldman lecture broken up by police. Her most famous stand came after the 1914 Ludlow Massacre, when she helped lead anti-Rockefeller demonstrations in Tarrytown, New York. Arrested with Arthur Caron and Charles Plunkett for speeches in the public square, she was convicted of disorderly conduct for calling John D. Rockefeller Jr. a "multi-murderer"; at her hearing she rejected counsel and told the court, "This town is owned by John D. Rockefeller. We don\'t expect justice here." After the sentence was sustained on appeal she was, on July 20, 1914, given the choice of a $300 peace bond or ninety days in jail. She refused the bond, was sent to the Workhouse on Blackwell\'s Island, and carried on a hunger strike of more than twenty-seven days, refusing both food and water — reported by The New York Times as the first hunger strike attempted by a woman in the United States — before friends raised $300 to secure her release. She later married Charles Plunkett, and died in 1973.',
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

            $workhouse = Institution::firstOrCreate(
                ['name' => "Workhouse, Blackwell's Island"],
                ['city' => 'New York', 'state' => 'New York']
            );

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'institution_id' => $workhouse->id,
                'charges' => 'Disorderly conduct — for anti-Rockefeller speeches in the public square at Tarrytown, New York during the demonstrations following the Ludlow Massacre, including calling John D. Rockefeller Jr. a "multi-murderer."',
                'convicted' => 'Yes — convicted of disorderly conduct; the sentence was sustained on appeal by Justice Crane of the Appellate Division.',
                'sentence' => 'Offered a $300 peace bond or 90 days in jail; she refused the bond and was sent to the Workhouse on Blackwell\'s Island, where she carried on a hunger strike of more than 27 days — reported by The New York Times as the first hunger strike attempted by a woman in the United States — before friends raised $300 to secure her release.',
                'arrest_date' => '1914-06-06',
                'sentenced_date' => '1914-07-20',
                'imprisoned_for_days' => 90,
            ]);

            $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });

        return self::SUCCESS;
    }
}
