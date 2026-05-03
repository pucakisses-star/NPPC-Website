<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddIWWAndAnarchistPrisoners extends Command
{
    protected $signature = 'prisoners:add-iww-anarchist';
    protected $description = 'Add early-20th-century IWW and anarchist political prisoners: Joe Hill, Frank Little, Emma Goldman, Alexander Berkman, Roger Baldwin.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $utahPen = Institution::firstOrCreate(
            ['name' => 'Utah State Prison'],
            ['city' => 'Sugar House', 'state' => 'Utah']
        );

        $jeffersonCity = Institution::firstOrCreate(
            ['name' => 'Missouri State Penitentiary'],
            ['city' => 'Jefferson City', 'state' => 'Missouri']
        );

        $atlantaPen = Institution::firstOrCreate(
            ['name' => 'United States Penitentiary, Atlanta'],
            ['city' => 'Atlanta', 'state' => 'Georgia']
        );

        $newark = Institution::firstOrCreate(
            ['name' => 'Essex County Jail (Newark)'],
            ['city' => 'Newark', 'state' => 'New Jersey']
        );

        $butteVigilantes = Institution::firstOrCreate(
            ['name' => 'Lynched by Anaconda-aligned vigilantes (extrajudicial)'],
            ['city' => 'Butte', 'state' => 'Montana']
        );

        $westernPen = Institution::firstOrCreate(
            ['name' => 'Western Penitentiary'],
            ['city' => 'Pittsburgh', 'state' => 'Pennsylvania']
        );

        $defendants = [];

        // Joe Hill
        $defendants[] = [
            'data' => [
                'name'        => 'Joe Hill',
                'first_name'  => 'Joe',
                'last_name'   => 'Hill',
                'aka'         => 'Joel Hägglund; Joseph Hillström',
                'birthdate'   => '1879-10-07',
                'death_date'  => '1915-11-19',
                'gender'      => 'Male',
                'state'       => 'Utah',
                'era'         => '1910s',
                'ideologies'  => ['Anarchist', 'Labor'],
                'affiliation' => ['Industrial Workers of the World'],
                'in_custody'  => false,
                'released'    => false,
                'description' => "Joe Hill, born Joel Emmanuel Hägglund in Gävle, Sweden, was an itinerant laborer, songwriter, and organizer for the Industrial Workers of the World. After emigrating to the United States in 1902 he traveled the West following construction work and labor disputes, contributing the songs that would define the IWW's culture — among them 'The Preacher and the Slave,' 'There Is Power in a Union,' and 'Rebel Girl.'\n\nIn January 1914 a Salt Lake City grocer named John Morrison and his son Arling were shot to death in their store. Hill, who had arrived a few hours later at a doctor's home with a bullet wound he refused to explain (saying only that it had come from a quarrel over a woman whose name he would not give), was arrested on circumstantial evidence and charged with the murders. The prosecution presented no eyewitness identification of Hill, no direct physical evidence, and no motive. He was convicted in June 1914 and sentenced to death.\n\nA worldwide campaign for his clemency drew appeals from AFL President Samuel Gompers, the Swedish minister to the United States, and President Woodrow Wilson, who twice asked the governor of Utah to reconsider. The governor refused. Hill was executed by firing squad at the Utah State Prison on November 19, 1915. His final telegram to IWW general secretary Bill Haywood read: 'Don't waste any time mourning. Organize.' His last words to the firing squad: 'Let it go — fire.' He has remained the most famous martyr of the American labor movement.",
            ],
            'case' => [
                'institution_id'        => $utahPen->id,
                'charges'               => 'First-degree murder (Salt Lake City)',
                'arrest_date'           => '1914-01-14',
                'death_in_custody_date' => '1915-11-19',
                'convicted'             => 'Yes — Utah jury verdict, June 27, 1914 (case based entirely on circumstantial evidence; conviction widely regarded as a political prosecution)',
                'sentence'              => 'Death by firing squad — executed November 19, 1915',
            ],
        ];

        // Frank Little
        $defendants[] = [
            'data' => [
                'name'        => 'Frank Little',
                'first_name'  => 'Frank',
                'middle_name' => 'Henry',
                'last_name'   => 'Little',
                'birthdate'   => '1878-01-01',
                'death_date'  => '1917-08-01',
                'gender'      => 'Male',
                'race'        => 'Native American', // Cherokee mother
                'state'       => 'Montana',
                'era'         => '1910s',
                'ideologies'  => ['Anarchist', 'Labor', 'Anti-war'],
                'affiliation' => ['Industrial Workers of the World'],
                'in_custody'  => false,
                'released'    => false,
                'description' => "Frank Henry Little was a half-Cherokee American labor organizer who joined the Industrial Workers of the World at its founding in 1905 and spent the next twelve years leading free-speech fights, lumber strikes, oil-field organizing, and mining campaigns across the western United States. He was repeatedly beaten and jailed and on the IWW general executive board he led the faction that opposed U.S. entry into World War I, arguing that workers had no interest in fighting a 'capitalist war.'\n\nIn the summer of 1917 he traveled to Butte, Montana, to support a strike of copper miners against the Anaconda Copper Mining Company in the wake of the June 8 Speculator Mine fire that killed 168 men. He delivered fiery speeches against the war and against the company. On the night of August 1, 1917, six masked men broke into the boarding house where he was staying, dragged him from his bed, tied him by a rope to the rear of an automobile, and dragged him through the streets of Butte before hanging him from a railroad trestle on the edge of town. A note pinned to his body listed initials of other IWW organizers under the threat 'Others take notice! First and last warning.' No one was ever charged with the lynching, which was widely understood to have been carried out at the direction of Anaconda Copper. His funeral procession, with an estimated 6,000–10,000 mourners, remains the largest in the history of Butte. His gravestone reads: 'Slain by capitalist interests for organizing and inspiring his fellow men.'",
            ],
            'case' => [
                'institution_id'        => $butteVigilantes->id,
                'charges'               => 'No legal charges — extrajudicially murdered by masked vigilantes in retaliation for IWW organizing and antiwar speech',
                'death_in_custody_date' => '1917-08-01',
                'convicted'             => 'No — extrajudicial lynching, August 1, 1917; no perpetrators ever charged',
                'sentence'              => 'Death by lynching',
            ],
        ];

        // Emma Goldman
        $defendants[] = [
            'data' => [
                'name'        => 'Emma Goldman',
                'first_name'  => 'Emma',
                'last_name'   => 'Goldman',
                'birthdate'   => '1869-06-27',
                'death_date'  => '1940-05-14',
                'gender'      => 'Female',
                'state'       => 'New York',
                'era'         => '1910s',
                'ideologies'  => ['Anarchist', 'Anti-war', 'Feminist'],
                'affiliation' => ['No-Conscription League', 'Mother Earth (publication)'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Emma Goldman was a Russian-born anarchist writer, editor, and orator who emigrated to the United States in 1885 and became one of the most influential and most prosecuted radicals in American history. She founded and edited the magazine Mother Earth, lectured nationally on anarchism, free love, women's suffrage, birth control, and opposition to war and conscription, and was repeatedly arrested across more than three decades.\n\nIn 1893 she served ten months on Blackwell's Island for telling the unemployed of New York to demand bread by force if necessary. In 1916 she was sentenced to fifteen days in the workhouse for publicly discussing contraception. After Congress declared war on Germany in April 1917, she and her longtime comrade Alexander Berkman organized the No-Conscription League, distributing tens of thousands of antiwar leaflets and holding mass rallies. Both were arrested on June 15, 1917 and tried in federal court in New York under the newly enacted Espionage Act for conspiring to obstruct the draft. Both were convicted, fined the maximum $10,000, and sentenced to two years in federal prison. Goldman served her sentence at the Missouri State Penitentiary in Jefferson City.\n\nReleased in September 1919, she was rearrested by the Bureau of Investigation under the Anarchist Exclusion Act and on December 21, 1919, deported with Berkman and 247 other immigrant radicals aboard the U.S. Army transport Buford — the so-called 'Soviet Ark' — to Russia. Disillusioned by the Bolshevik suppression of dissent, she left Russia in 1921 and lived the rest of her life in exile in Europe and Canada. She died in Toronto in 1940 and was permitted, in death, to be returned to the United States and buried in Chicago at Waldheim Cemetery near the Haymarket Martyrs' Monument.",
            ],
            'case' => [
                'institution_id' => $jeffersonCity->id,
                'charges'        => 'Conspiracy to obstruct the draft (Espionage Act of 1917)',
                'arrest_date'    => '1917-06-15',
                'release_date'   => '1919-09-27',
                'convicted'      => 'Yes — federal jury verdict, July 9, 1917',
                'sentence'       => 'Two years in federal prison and a $10,000 fine; served the full sentence; deported to Soviet Russia on December 21, 1919',
            ],
        ];

        // Alexander Berkman
        $defendants[] = [
            'data' => [
                'name'        => 'Alexander Berkman',
                'first_name'  => 'Alexander',
                'last_name'   => 'Berkman',
                'aka'         => 'Sasha',
                'birthdate'   => '1870-11-21',
                'death_date'  => '1936-06-28',
                'gender'      => 'Male',
                'state'       => 'New York',
                'era'         => '1910s',
                'ideologies'  => ['Anarchist', 'Anti-war'],
                'affiliation' => ['No-Conscription League', 'Mother Earth (publication)', 'The Blast (publication)'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Alexander 'Sasha' Berkman was a Russian-born anarchist writer, editor, and lifelong companion of Emma Goldman whose own imprisonments span more than two decades and two separate political campaigns.\n\nIn 1892, in retaliation for the killing of striking steelworkers at the Homestead strike, Berkman attempted to assassinate Carnegie Steel chairman Henry Clay Frick at Frick's office in Pittsburgh, shooting and stabbing him without killing him. He was tried in Pennsylvania and sentenced to 22 years for attempted murder; he served fourteen years at the Western Penitentiary in Pittsburgh and the Allegheny Workhouse before his release in 1906. He later wrote about the experience in his Prison Memoirs of an Anarchist (1912), one of the foundational texts of American prison literature.\n\nBack in New York, he edited the anarchist magazine The Blast and worked alongside Goldman on Mother Earth and on free-speech and labor defense campaigns. Following U.S. entry into World War I in April 1917 he co-founded the No-Conscription League with Goldman and was arrested with her on June 15, 1917 under the Espionage Act for conspiring to obstruct the draft. Convicted and sentenced to two years, he served his sentence at the United States Penitentiary in Atlanta. Released in October 1919, he was rearrested under the Anarchist Exclusion Act and on December 21, 1919, deported with Goldman aboard the U.S. Army transport Buford to Soviet Russia. He spent the rest of his life in European exile and died in France in 1936.",
            ],
            'case' => [
                'institution_id' => $atlantaPen->id,
                'charges'        => 'Conspiracy to obstruct the draft (Espionage Act of 1917)',
                'arrest_date'    => '1917-06-15',
                'release_date'   => '1919-10-01',
                'convicted'      => 'Yes — federal jury verdict, July 9, 1917',
                'sentence'       => 'Two years in federal prison and a $10,000 fine; served the full sentence; deported to Soviet Russia on December 21, 1919',
            ],
            'extra_case' => [
                'institution_id' => $westernPen->id,
                'charges'        => 'Attempted murder of Henry Clay Frick (Homestead strike retaliation)',
                'arrest_date'    => '1892-07-23',
                'incarceration_date' => '1892-09-19',
                'release_date'   => '1906-05-18',
                'convicted'      => 'Yes — Allegheny County, Pennsylvania, 1892',
                'sentence'       => '22 years; served 14 years before release in 1906',
            ],
        ];

        // Roger Baldwin
        $defendants[] = [
            'data' => [
                'name'        => 'Roger Baldwin',
                'first_name'  => 'Roger',
                'middle_name' => 'Nash',
                'last_name'   => 'Baldwin',
                'birthdate'   => '1884-01-21',
                'death_date'  => '1981-08-26',
                'gender'      => 'Male',
                'state'       => 'New York',
                'era'         => '1910s',
                'ideologies'  => ['Pacifist', 'Civil libertarian'],
                'affiliation' => ['American Civil Liberties Union', 'National Civil Liberties Bureau', 'American Union Against Militarism'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Roger Nash Baldwin was a social worker, civil libertarian, and the founder of the American Civil Liberties Union. After founding the National Civil Liberties Bureau in 1917 to defend conscientious objectors and prosecuted antiwar speakers, Baldwin himself refused to register for the wartime draft on grounds of conscience. He was arrested on October 10, 1918, and on October 30, 1918, sentenced in federal court in New York to one year in prison for refusing to register, refusing the medical examination, and refusing alternative service.\n\nHis statement to the court — published as The Individual and the State and widely reprinted — became one of the canonical American defenses of conscience against state authority. He served roughly nine to ten months at the Essex County Jail in Newark, New Jersey, where he organized inmates and conducted study groups. After his release in 1919 he traveled the country meeting with IWW organizers, then returned to New York in 1920 and converted the National Civil Liberties Bureau into the American Civil Liberties Union, which he led for thirty more years. He lived to age 97 and remained politically active to the end of his life.",
            ],
            'case' => [
                'institution_id' => $newark->id,
                'charges'        => 'Refusing to register for the draft; refusing the medical examination; refusing alternative service (Selective Service Act of 1917)',
                'arrest_date'    => '1918-10-10',
                'release_date'   => '1919-07-19',
                'convicted'      => 'Yes — federal court, Southern District of New York, October 30, 1918',
                'sentence'       => 'One year in federal prison; served approximately nine months',
            ],
        ];

        foreach ($defendants as $entry) {
            DB::transaction(function () use ($entry, &$created, &$skipped) {
                $name = $entry['data']['name'];
                if (Prisoner::where('name', $name)->exists()) {
                    $this->warn("Skipping {$name} — already exists.");
                    $skipped++;
                    return;
                }

                $prisoner = Prisoner::create($entry['data']);

                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['case']));

                if (isset($entry['extra_case'])) {
                    PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $entry['extra_case']));
                }

                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
