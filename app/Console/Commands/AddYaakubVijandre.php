<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddYaakubVijandre extends Command
{
    protected $signature = 'prisoners:add-yaakub-vijandre';
    protected $description = 'Add Yaakub Ira Vijandre, the Filipino-American DACA recipient and pro-Palestine photojournalist detained by ICE in October 2025.';

    private const BIO = <<<'TXT'
Yaakub Ira Vijandre is a 38-year-old Filipino-American photojournalist, videographer, and longtime pro-Palestinian organizer based in the Dallas–Fort Worth area of Texas. He came to the United States from the Philippines with his family in 2001 at age 14, and has lived in the country lawfully since then; he is a recipient of Deferred Action for Childhood Arrivals (DACA). He has no criminal record. His photography and on-the-ground video coverage of pro-Palestinian protests in North Texas — circulated widely on Instagram and through Drop Site News, the MLFA, and other independent press channels — made him one of the most prolific visual chroniclers of the Palestine solidarity movement in the South.

In late 2023, according to his legal team, he was approached by the Federal Bureau of Investigation with a request that he become a confidential informant inside the pro-Palestinian movement in Texas. He declined. Over the following two years, he continued his photojournalism and his organizing work, including extensive documentation of the ICE detentions of other pro-Palestinian community members across the Dallas area in 2025.

On the night of October 6, 2025, Vijandre filmed a Richardson City Council meeting at which residents and organizers spoke against the ICE detention of a local Palestinian community leader. The next morning, October 7, 2025, ICE agents arrested him at gunpoint at his home in Richardson, Texas. The U.S. Department of Homeland Security has stated that the basis for the detention and for the Department's attempt to revoke his DACA status is his social media activity — Instagram posts and likes critical of U.S. foreign policy, U.S. support for Israel, and U.S. immigration enforcement. He has not been charged with any crime.

He was initially held at the Bluebonnet Detention Center in Anson, Texas, where his attorneys and family could visit. On October 30, 2025, ICE transferred him approximately 1,000 miles away to the Folkston ICE Processing Center in Folkston, Georgia, a remote and overcrowded private facility operated by the GEO Group. His attorneys argued that the transfer was retaliatory and was designed to obstruct his access to counsel and to the press.

His legal team — including the Muslim Legal Fund of America, Asian Americans Advancing Justice–Atlanta, and Asian Americans Advancing Justice–AAJC — has filed a habeas corpus petition seeking his immediate release. They argue that his detention is unconstitutional retaliation for First Amendment-protected speech and journalism, and that the government's use of social media surveillance to strip a DACA recipient of his lawful status sets a dangerous precedent for the criminalization of pro-Palestinian and immigrant journalists. As of this writing he remains in ICE custody at Folkston, awaiting decisions on his habeas petition and his removal proceedings.
TXT;

    public function handle(): int
    {
        if (Prisoner::where('name', 'Yaakub Ira Vijandre')->exists()) {
            $this->error('Yaakub Ira Vijandre already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $folkston = Institution::firstOrCreate(
                ['name' => 'Folkston ICE Processing Center'],
                ['city' => 'Folkston', 'state' => 'Georgia']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Yaakub Ira Vijandre',
                'first_name'     => 'Yaakub',
                'middle_name'    => 'Ira',
                'last_name'      => 'Vijandre',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Asian',
                'birthdate'      => '1987-01-01', // age 38 in late 2025 → birth year 1987 (year only public)
                'state'          => 'Texas',
                'era'            => '2020s',
                'ideologies'     => ['Palestine solidarity', 'Anti-imperialist', 'First Amendment', 'Photojournalism'],
                'affiliation'    => ['Drop Site News (contributor)', 'Muslim Legal Fund of America (legal representation)', 'North Texas Palestine solidarity movement'],
                'in_custody'     => true,
                'released'       => false,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $folkston->id,
                'charges'            => "No criminal charges. Immigration detention and DACA revocation proceedings under Department of Homeland Security authority, based on Instagram posts and likes critical of U.S. foreign policy, U.S. support for Israel, and U.S. immigration enforcement. His attorneys argue the detention is unconstitutional retaliation for First Amendment-protected journalism and political speech",
                'arrest_date'        => '2025-10-07',
                'incarceration_date' => '2025-10-07',
                'release_date'       => null,
                'convicted'          => 'No criminal conviction; habeas corpus petition pending in federal court',
                'sentence'           => "Initially held at the Bluebonnet Detention Center in Anson, Texas; transferred October 30, 2025 to the Folkston ICE Processing Center in Folkston, Georgia (a privately operated GEO Group facility approximately 1,000 miles from his attorneys and family). Held without bond pending the outcome of his habeas petition and removal proceedings",
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
