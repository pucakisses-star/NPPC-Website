<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddPRNationalistsAndDay extends Command
{
    protected $signature = 'prisoners:add-pr-nationalists-and-day';
    protected $description = 'Add Pedro Albizu Campos, the four 1954 Capitol attackers, and Dorothy Day.';

    public function handle(): int
    {
        $created = 0;
        $skipped = 0;

        $atlanta = Institution::firstOrCreate(
            ['name' => 'United States Penitentiary, Atlanta'],
            ['city' => 'Atlanta', 'state' => 'Georgia']
        );

        $laPrincesa = Institution::firstOrCreate(
            ['name' => 'La Princesa Prison'],
            ['city' => 'San Juan', 'state' => 'Puerto Rico']
        );

        $alderson = Institution::firstOrCreate(
            ['name' => 'Federal Reformatory for Women, Alderson'],
            ['city' => 'Alderson', 'state' => 'West Virginia']
        );

        $leavenworth = Institution::firstOrCreate(
            ['name' => 'United States Penitentiary, Leavenworth'],
            ['city' => 'Leavenworth', 'state' => 'Kansas']
        );

        $marion = Institution::firstOrCreate(
            ['name' => 'United States Penitentiary, Marion'],
            ['city' => 'Marion', 'state' => 'Illinois']
        );

        $springfield = Institution::firstOrCreate(
            ['name' => 'U.S. Medical Center for Federal Prisoners, Springfield'],
            ['city' => 'Springfield', 'state' => 'Missouri']
        );

        $nyJail = Institution::firstOrCreate(
            ['name' => 'New York City House of Detention for Women'],
            ['city' => 'New York', 'state' => 'New York']
        );

        $fresno = Institution::firstOrCreate(
            ['name' => 'Fresno County Jail'],
            ['city' => 'Fresno', 'state' => 'California']
        );

        $defendants = [];

        // Pedro Albizu Campos
        $defendants[] = [
            'data' => [
                'name'        => 'Pedro Albizu Campos',
                'first_name'  => 'Pedro',
                'last_name'   => 'Albizu Campos',
                'birthdate'   => '1891-09-12',
                'death_date'  => '1965-04-21',
                'gender'      => 'Male',
                'state'       => 'Puerto Rico',
                'era'         => '1950s',
                'ideologies'  => ['Puerto Rican independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Pedro Albizu Campos was a Puerto Rican lawyer, Harvard graduate, and from 1930 until his death the president of the Puerto Rican Nationalist Party. Across three separate imprisonments he spent approximately 26 years of his life in U.S. federal custody for his unrelenting campaign for Puerto Rican independence — making him the foundational figure that the entire later cluster of Puerto Rican political prisoners (the FALN era and the Macheteros era both included) traced their lineage back to.\n\nIn 1937, after the Ponce Massacre in which Insular Police killed 19 unarmed Nationalists and bystanders, federal authorities prosecuted Albizu Campos and other party leaders for seditious conspiracy. He was sentenced to ten years and held at the United States Penitentiary in Atlanta from 1937 until 1947. His health collapsed during this imprisonment, a pattern that would repeat.\n\nIn October 1950, days after the Jayuya Uprising and the attempt on President Truman's life by two Nationalists at Blair House, he was arrested again. He was convicted under Puerto Rico's notorious Ley de la Mordaza (Law 53, the 'Gag Law') of inciting violent revolt and sentenced to 80 years. Held at La Princesa Prison in San Juan, he and his supporters charged that he was being subjected to radiation experiments — a claim long dismissed by U.S. authorities and later substantially corroborated by independent medical examinations of the burns and lesions on his body. Governor Luis Muñoz Marín pardoned him in 1953 in deteriorating health, but the pardon was revoked in 1954 immediately after the Capitol attack carried out by his followers.\n\nA pardon was issued again in November 1964 as he lay dying. He died in San Juan on April 21, 1965. He remains the central political figure in the Puerto Rican independence movement.",
            ],
            'cases' => [
                [
                    'institution_id' => $atlanta->id,
                    'charges'        => 'Seditious conspiracy (Ponce Massacre era prosecution)',
                    'arrest_date'    => '1936-03-04',
                    'release_date'   => '1947-12-15',
                    'convicted'      => 'Yes — federal court, Puerto Rico, 1937',
                    'sentence'       => 'Ten years; served the full sentence',
                ],
                [
                    'institution_id' => $laPrincesa->id,
                    'charges'        => 'Inciting violent revolt (Puerto Rico Law 53, the "Gag Law"); attempted murder',
                    'arrest_date'    => '1950-11-02',
                    'release_date'   => '1953-09-30',
                    'convicted'      => 'Yes — Puerto Rico courts, 1951',
                    'sentence'       => '80 years; pardoned by Governor Muñoz Marín in 1953 after roughly three years',
                ],
                [
                    'institution_id' => $laPrincesa->id,
                    'charges'        => 'Pardon revoked after the March 1, 1954 Capitol attack carried out by Nationalist Party members',
                    'arrest_date'    => '1954-03-06',
                    'release_date'   => '1964-11-15',
                    'convicted'      => 'Pardon revoked, original 80-year sentence reinstated',
                    'sentence'       => 'Continuing service of original sentence; pardoned again November 15, 1964 in failing health',
                ],
            ],
        ];

        // The four 1954 Capitol attackers
        $capitolAttack = "On March 1, 1954, four members of the Puerto Rican Nationalist Party — Lolita Lebrón, Rafael Cancel Miranda, Andrés Figueroa Cordero, and Irvin Flores Rodríguez — entered the U.S. Capitol, took seats in the visitors' gallery overlooking the House chamber, unfurled a Puerto Rican flag, and opened fire on the Representatives below. Thirty rounds were fired in roughly thirty seconds; five Representatives were wounded but all survived. The attack was timed to draw international attention to the Inter-American Conference of the Organization of American States then opening in Caracas, and to challenge the U.S. claim that Puerto Rico had become a self-governing 'commonwealth.' All four were arrested at the scene, tried in federal court in Washington, D.C. and again in New York for related conspiracy charges, and sentenced to consecutive terms amounting effectively to life in federal prison. President Jimmy Carter commuted Andrés Figueroa Cordero's sentence in October 1977 (he was terminally ill with cancer and died in 1979); Carter commuted the remaining three sentences on September 6, 1979. They returned to Puerto Rico to a crowd estimated at 5,000 at San Juan International Airport.";

        $defendants[] = [
            'data' => [
                'name'        => 'Lolita Lebrón',
                'first_name'  => 'Dolores',
                'last_name'   => 'Lebrón Sotomayor',
                'aka'         => 'Lolita Lebrón',
                'birthdate'   => '1919-11-19',
                'death_date'  => '2010-08-01',
                'gender'      => 'Female',
                'state'       => 'Puerto Rico',
                'era'         => '1950s',
                'ideologies'  => ['Puerto Rican independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Dolores 'Lolita' Lebrón Sotomayor led the four-person commando team that attacked the U.S. House of Representatives on March 1, 1954, shouting '¡Viva Puerto Rico libre!' from the visitors' gallery as she opened fire. She was 34 years old. Tried first in federal court in Washington and then in New York, she was sentenced to 56 years in federal prison and held at the Federal Reformatory for Women in Alderson, West Virginia, where she served 25 years before her sentence was commuted by President Carter in 1979. Returning to Puerto Rico, she remained a leading figure in the independence movement until her death in 2010, including a final imprisonment at age 81 for civil disobedience against the U.S. Navy bombing of Vieques.\n\n".$capitolAttack,
            ],
            'cases' => [
                [
                    'institution_id' => $alderson->id,
                    'charges'        => 'Assault with intent to kill; seditious conspiracy',
                    'arrest_date'    => '1954-03-01',
                    'release_date'   => '1979-09-10',
                    'convicted'      => 'Yes — federal court, Washington, D.C., 1954',
                    'sentence'       => '56 years; commuted by President Carter and released after 25 years',
                ],
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'        => 'Rafael Cancel Miranda',
                'first_name'  => 'Rafael',
                'last_name'   => 'Cancel Miranda',
                'birthdate'   => '1930-07-18',
                'death_date'  => '2020-03-02',
                'gender'      => 'Male',
                'state'       => 'Puerto Rico',
                'era'         => '1950s',
                'ideologies'  => ['Puerto Rican independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Rafael Cancel Miranda was 23 years old at the time of the March 1, 1954 attack on the U.S. House of Representatives, the youngest of the four. He had previously served two years in federal prison for refusing the draft in protest against U.S. colonial rule of Puerto Rico. After the Capitol attack he was sentenced to 84 years in federal prison; he served 25 years and was held at the United States Penitentiary in Leavenworth and at Marion before President Carter commuted his sentence in 1979. He spent the rest of his life as one of the most articulate spokespersons for Puerto Rican independence and prisoner solidarity worldwide.\n\n".$capitolAttack,
            ],
            'cases' => [
                [
                    'institution_id' => $leavenworth->id,
                    'charges'        => 'Assault with intent to kill; seditious conspiracy',
                    'arrest_date'    => '1954-03-01',
                    'release_date'   => '1979-09-10',
                    'convicted'      => 'Yes — federal court, Washington, D.C., 1954',
                    'sentence'       => '84 years; commuted by President Carter and released after 25 years',
                ],
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'        => 'Andrés Figueroa Cordero',
                'first_name'  => 'Andrés',
                'last_name'   => 'Figueroa Cordero',
                'birthdate'   => '1925-06-07',
                'death_date'  => '1979-03-07',
                'gender'      => 'Male',
                'state'       => 'Puerto Rico',
                'era'         => '1950s',
                'ideologies'  => ['Puerto Rican independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Andrés Figueroa Cordero was one of the four Puerto Rican Nationalists who attacked the U.S. House of Representatives on March 1, 1954. Sentenced to a long federal prison term, he served 23 years before President Carter commuted his sentence on October 6, 1977 on humanitarian grounds — Figueroa Cordero was dying of cancer. He returned to Puerto Rico and died on March 7, 1979.\n\n".$capitolAttack,
            ],
            'cases' => [
                [
                    'institution_id' => $springfield->id,
                    'charges'        => 'Assault with intent to kill; seditious conspiracy',
                    'arrest_date'    => '1954-03-01',
                    'release_date'   => '1977-10-06',
                    'convicted'      => 'Yes — federal court, Washington, D.C., 1954',
                    'sentence'       => 'Long federal sentence; commuted by President Carter on October 6, 1977 on humanitarian grounds (terminal cancer)',
                ],
            ],
        ];

        $defendants[] = [
            'data' => [
                'name'        => 'Irvin Flores Rodríguez',
                'first_name'  => 'Irvin',
                'last_name'   => 'Flores Rodríguez',
                'birthdate'   => '1924-09-09',
                'death_date'  => '1994-06-22',
                'gender'      => 'Male',
                'state'       => 'Puerto Rico',
                'era'         => '1950s',
                'ideologies'  => ['Puerto Rican independence', 'Anti-colonial'],
                'affiliation' => ['Puerto Rican Nationalist Party'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Irvin Flores Rodríguez was one of the four Puerto Rican Nationalists who attacked the U.S. House of Representatives on March 1, 1954. He served 25 years in federal prison before President Carter commuted his sentence on September 6, 1979. He returned to Puerto Rico and remained active in the independence movement until his death in 1994.\n\n".$capitolAttack,
            ],
            'cases' => [
                [
                    'institution_id' => $marion->id,
                    'charges'        => 'Assault with intent to kill; seditious conspiracy',
                    'arrest_date'    => '1954-03-01',
                    'release_date'   => '1979-09-10',
                    'convicted'      => 'Yes — federal court, Washington, D.C., 1954',
                    'sentence'       => 'Long federal sentence; commuted by President Carter and released after 25 years',
                ],
            ],
        ];

        // Dorothy Day
        $defendants[] = [
            'data' => [
                'name'        => 'Dorothy Day',
                'first_name'  => 'Dorothy',
                'last_name'   => 'Day',
                'birthdate'   => '1897-11-08',
                'death_date'  => '1980-11-29',
                'gender'      => 'Female',
                'state'       => 'New York',
                'era'         => '1950s',
                'ideologies'  => ['Pacifist', 'Anarchist', 'Catholic Worker'],
                'affiliation' => ['Catholic Worker Movement'],
                'in_custody'  => false,
                'released'    => true,
                'description' => "Dorothy Day was a journalist, organizer, and the co-founder, with Peter Maurin, of the Catholic Worker Movement, the radical pacifist Catholic movement that sustained houses of hospitality, voluntary poverty, and a tradition of nonviolent civil disobedience that has continued for nearly a century. She was first arrested in 1917, at age 19, picketing the White House for women's suffrage with the Silent Sentinels — a sentence she served at the Occoquan Workhouse and described as her political baptism. She was arrested repeatedly across the next six decades.\n\nIn the 1950s she was jailed several times for refusing to take shelter during New York's Operation Alert civil-defense drills, an act of conscientious objection she defended as a refusal to lend the cooperation of the city to the rehearsal of nuclear war; her 1957 sentence was thirty days. In the summer of 1973, at age 75, she joined Cesar Chavez and the United Farm Workers on the picket lines in California's Central Valley in defiance of a court injunction against mass picketing of grape and lettuce growers. She was arrested with other UFW supporters and jailed for ten days at the Fresno County Jail. It was her last imprisonment.\n\nShe died in 1980. The Vatican opened her cause for canonization in 2000; she has been declared a Servant of God. Her arrests, taken together, span every major American radical movement of the 20th century — suffrage, IWW solidarity, antiwar, civil defense resistance, and the farm workers — making her one of the most consistently jailed political prisoners in U.S. history.",
            ],
            'cases' => [
                [
                    'institution_id'      => $nyJail->id,
                    'charges'             => 'Refusing to take shelter during Operation Alert civil defense drill',
                    'arrest_date'         => '1957-07-12',
                    'release_date'        => '1957-08-11',
                    'convicted'           => 'Yes — New York, 1957',
                    'sentence'            => '30 days at the New York City House of Detention for Women',
                ],
                [
                    'institution_id'      => $fresno->id,
                    'charges'             => 'Defying court injunction against mass picketing in support of the United Farm Workers grape and lettuce strike',
                    'arrest_date'         => '1973-08-02',
                    'release_date'        => '1973-08-13',
                    'convicted'           => 'Yes — Fresno County, California, 1973',
                    'sentence'            => '10 days at Fresno County Jail (her final imprisonment, at age 75)',
                ],
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

                foreach ($entry['cases'] as $case) {
                    PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $case));
                }

                $this->info("Added {$prisoner->name}");
                $created++;
            });
        }

        $this->info("\nDone. Created {$created}, skipped {$skipped}.");

        return self::SUCCESS;
    }
}
