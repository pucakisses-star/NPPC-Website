<?php

namespace App\Console\Commands;

use App\Models\Topic;
use Illuminate\Console\Command;

/**
 * Adds more major organizations to the "Organizations" section of /topics —
 * clandestine and movement organizations whose members became political
 * prisoners, complementing the existing nine (Black Panther Party, Black
 * Liberation Army, AIM, FALN, Weather Underground, ELF, ALF, MOVE, Republic of
 * New Afrika). New topics are appended after the existing children by
 * sort_order. Idempotent: skips any slug already present.
 */
final class AddOrganizationTopics extends Command
{
    protected $signature = 'topics:add-organizations';

    protected $description = 'Add more major organizations to the Organizations section of /topics';

    public function handle(): int
    {
        $parent = Topic::where('slug', 'organizations')->first();
        if (! $parent) {
            $this->warn('The "organizations" parent topic was not found — skipping (no-op).');

            return self::SUCCESS;
        }

        $order = (int) Topic::where('parent_id', $parent->id)->max('sort_order');
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Topic::where('slug', $r['slug'])->exists()) {
                $this->warn("Exists, skipping: {$r['title']}");
                $skipped++;

                continue;
            }

            $order++;
            Topic::create([
                'title' => $r['title'],
                'slug' => $r['slug'],
                'parent_id' => $parent->id,
                'body' => $r['body'],
                'published' => true,
                'sort_order' => $order,
            ]);
            $this->info("Added: {$r['title']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        return [
            [
                'title' => 'Symbionese Liberation Army',
                'slug' => 'symbionese-liberation-army',
                'body' => '<p>The Symbionese Liberation Army (SLA) was a small armed revolutionary group active in California from 1973 to 1975, best known for assassinating Oakland schools superintendent Marcus Foster and for kidnapping newspaper heiress Patty Hearst. Six members died in a 1974 Los Angeles police shootout and fire; survivors including Russell Little, Joseph Remiro, and later Sara Jane Olson (Kathleen Soliah), Bill and Emily Harris, and Michael Bortin served prison terms. Its brief, violent run made it one of the most notorious clandestine groups of the 1970s left.</p>',
            ],
            [
                'title' => 'Los Macheteros',
                'slug' => 'los-macheteros',
                'body' => '<p>Los Macheteros — the Ejército Popular Boricua, or Boricua Popular Army — is a clandestine Puerto Rican organization founded in 1976 to fight for the island\'s independence from the United States. It claimed a series of armed actions, most famously the 1983 Wells Fargo depot robbery in Hartford, Connecticut, which triggered a sweeping federal prosecution. Its members became prominent Puerto Rican political prisoners; its founder, Filiberto Ojeda Ríos, was killed by the FBI in 2005.</p>',
            ],
            [
                'title' => 'United Freedom Front',
                'slug' => 'united-freedom-front',
                'body' => '<p>The United Freedom Front (UFF) was a small white anti-imperialist group active from 1975 to 1984 that bombed corporate and military targets in opposition to U.S. policy in Central America and to South African apartheid. Its members — Raymond Luc Levasseur, Thomas Manning, Jaan Laaman, Richard Williams, and their co-defendants, known as the "Ohio 7" — received long federal sentences after their 1984–85 arrests, making them among the longest-held U.S. anti-imperialist political prisoners.</p>',
            ],
            [
                'title' => 'May 19th Communist Organization',
                'slug' => 'may-19th-communist-organization',
                'body' => '<p>The May 19th Communist Organization — named for the shared birthday of Ho Chi Minh and Malcolm X — was a U.S. revolutionary group of the late 1970s and 1980s that supported the Black Liberation Army and Puerto Rican independence. Its members helped free Assata Shakur from prison in 1979 and took part in the 1981 Brink\'s armored-car robbery in Nyack, New York. Marilyn Buck, Susan Rosenberg, Judith Clark, David Gilbert, and others drew long sentences and became enduring political prisoners.</p>',
            ],
            [
                'title' => 'Young Lords',
                'slug' => 'young-lords',
                'body' => '<p>The Young Lords began as a Chicago street organization and, by 1969, became a revolutionary Puerto Rican civil-rights and self-determination movement with chapters in New York and other cities. Modeled in part on the Black Panther Party, they organized around health care, housing, and Puerto Rican independence, staging the 1969–70 occupation of the First Spanish Methodist Church in East Harlem and a takeover of Lincoln Hospital. Many members were surveilled, arrested, and prosecuted, and some went on to the armed independence movement.</p>',
            ],
            [
                'title' => 'George Jackson Brigade',
                'slug' => 'george-jackson-brigade',
                'body' => '<p>The George Jackson Brigade was a small armed revolutionary group active in the Pacific Northwest from 1975 to 1977, named for the imprisoned Black Panther author killed at San Quentin in 1971. A racially integrated, mixed-gender group, it carried out bombings and bank robberies in support of prison-abolition and anti-capitalist aims. Members including Ed Mead and Rita Brown were captured and imprisoned.</p>',
            ],
            [
                'title' => 'Students for a Democratic Society',
                'slug' => 'students-for-a-democratic-society',
                'body' => '<p>Students for a Democratic Society (SDS) was the largest organization of the 1960s New Left, growing from its 1962 Port Huron Statement into a mass campus movement against the Vietnam War and racial injustice. Its members led draft resistance, university occupations, and antiwar demonstrations that produced thousands of arrests. As SDS fractured in 1969, its most militant wing became the Weather Underground.</p>',
            ],
            [
                'title' => 'Student Nonviolent Coordinating Committee',
                'slug' => 'student-nonviolent-coordinating-committee',
                'body' => '<p>The Student Nonviolent Coordinating Committee (SNCC) was a leading civil-rights organization formed in 1960 out of the lunch-counter sit-in movement. Its young organizers led the Freedom Rides, Deep South voter-registration drives, and the 1964 Freedom Summer, enduring mass arrests, beatings, and jailings. Under later chairmen Stokely Carmichael and H. Rap Brown it embraced Black Power, and several veterans were prosecuted as the state cracked down.</p>',
            ],
            [
                'title' => 'Industrial Workers of the World',
                'slug' => 'industrial-workers-of-the-world',
                'body' => '<p>The Industrial Workers of the World (IWW), whose members are known as "Wobblies," is a revolutionary industrial union founded in Chicago in 1905 to organize all workers into "One Big Union." Its militant free-speech fights and strikes drew ferocious repression: songwriter Joe Hill was executed in 1915, organizer Frank Little was lynched in 1917, and during World War I the federal government prosecuted more than a hundred IWW leaders — among them Big Bill Haywood — under the Espionage Act in one of the largest mass political trials in U.S. history. Many served long sentences at Leavenworth, and the union remains a touchstone of American labor radicalism.</p>',
            ],
            [
                'title' => 'Communist Party USA',
                'slug' => 'communist-party-usa',
                'body' => '<p>The Communist Party USA (CPUSA), founded in 1919, was for decades the largest organization of the American radical left, active in labor organizing, civil rights, and anti-fascist work. During the early Cold War its leaders were prosecuted under the Smith Act for "conspiring to advocate" the overthrow of the government: the 1949 Foster–Dennis trial sent Eugene Dennis, Benjamin J. Davis Jr., Henry Winston, Gus Hall, and others to federal prison, followed by a second wave of "second-string" prosecutions across the country. The convictions, upheld in Dennis v. United States (1951), made the party\'s leadership the central political prisoners of the McCarthy era.</p>',
            ],
            [
                'title' => 'Puerto Rican Nationalist Party',
                'slug' => 'puerto-rican-nationalist-party',
                'body' => '<p>The Puerto Rican Nationalist Party (Partido Nacionalista de Puerto Rico), led from 1930 by Pedro Albizu Campos, was the principal force for the island\'s independence and the wellspring of the modern Puerto Rican political-prisoner tradition. Its members were prosecuted again and again: the 1936 seditious-conspiracy trial that sent Albizu and his co-defendants to Atlanta; the 1950 uprisings at Jayuya and elsewhere and the attack on Blair House; and the 1954 armed protest in the U.S. House of Representatives. Figures such as Albizu Campos, Lolita Lebrón, Blanca Canales, and Oscar Collazo became enduring symbols of the independence struggle.</p>',
            ],
        ];
    }
}
