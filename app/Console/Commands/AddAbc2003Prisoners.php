<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Adds the prisoners listed in the Anarchist Black Cross Network Newsletter
 * (Summer 2003) prisoner directory who were missing from the database. These
 * are the more obscure entries — most are documented chiefly in that ABC list,
 * so the records carry the newsletter's account and conservative status flags
 * (only the clearly-completed short sentences are marked released; the rest are
 * left without a current-custody claim, since 2003 status can't be verified for
 * 2026). Idempotent; skips anyone already present.
 */
final class AddAbc2003Prisoners extends Command
{
    protected $signature = 'prisoners:add-abc-2003';

    protected $description = 'Add the missing prisoners from the Summer 2003 ABC Network newsletter directory';

    public function handle(): int
    {
        // status: 'released' | 'unknown'  (none confirmed currently in custody)
        $people = [
            ['name' => 'Jerome White-Bey', 'first' => 'Jerome', 'last' => 'White-Bey', 'gender' => 'Male',
                'state' => 'Missouri', 'era' => '2000s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'status' => 'unknown', 'prison' => null, 'inmate' => null,
                'charges' => null,
                'desc' => 'Jerome White-Bey is an anarchist organizer — a "social" prisoner who became a dedicated activist behind bars and founded the Missouri Prison Labor Union. He was profiled among anarchist political prisoners by the Anarchist Black Cross in 2003.'],

            ['name' => 'Charles Hoke', 'first' => 'Charles', 'last' => 'Hoke', 'gender' => 'Male',
                'state' => 'Indiana', 'era' => '2000s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'status' => 'unknown', 'prison' => ['Indiana State Prison', 'Michigan City', 'Indiana'], 'inmate' => '861206',
                'charges' => 'Bank robberies — which he said were meant to support himself and other farmers being forced off their land by developers.',
                'desc' => 'Charles Hoke is described by the Anarchist Black Cross as a rural anarchist imprisoned in Indiana for a series of bank robberies he said were intended to support himself and other farmers being forced from their homes by developers.'],

            ['name' => 'James Johnson', 'first' => 'James', 'last' => 'Johnson', 'gender' => 'Male',
                'state' => 'Oregon', 'era' => '2000s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'status' => 'unknown', 'prison' => ['Snake River Correctional Institution', 'Ontario', 'Oregon'], 'inmate' => '8952263',
                'charges' => null,
                'desc' => 'James Johnson is an Oregon prisoner who, after being jailed as a non-political ("social") prisoner, became an active anti-authoritarian organizer within Oregon\'s prisons and faced sustained retaliation from prison authorities (per the Anarchist Black Cross, 2003). He is a different person from the New York prisoner Mohaman Koti, formerly known as James Carter Johnson.'],

            ['name' => 'Matthew Lamont', 'first' => 'Matthew', 'last' => 'Lamont', 'gender' => 'Male',
                'state' => 'California', 'era' => '2000s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'status' => 'unknown', 'prison' => ['Wasco State Prison', 'Wasco', 'California'], 'inmate' => 'T90251',
                'charges' => 'Imprisoned over an alleged plan to attack a white-supremacist gathering.',
                'desc' => 'Matthew Lamont is a California anarchist who, according to the Anarchist Black Cross (2003), was imprisoned over an alleged plan to attack a white-supremacist gathering.'],

            ['name' => 'Robert Middaugh', 'first' => 'Robert', 'last' => 'Middaugh', 'gender' => 'Male',
                'state' => 'California', 'era' => '2000s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'status' => 'released', 'prison' => null, 'inmate' => null,
                'charges' => 'A confrontation with police during the May Day 2001 protests in Long Beach, California.',
                'desc' => 'Robert Middaugh was an anarchist who served a three-year sentence stemming from a clash with police during the May Day 2001 protests in Long Beach, California.'],

            ['name' => 'Mike Rusniak', 'first' => 'Mike', 'last' => 'Rusniak', 'gender' => 'Male',
                'state' => 'Illinois', 'era' => '2000s', 'ideologies' => ['Anarchism'], 'affiliation' => ['Anarchist movement'],
                'status' => 'released', 'prison' => ['Dixon Correctional Center', 'Dixon', 'Illinois'], 'inmate' => 'K88887',
                'charges' => 'Taking a police car and other acts of anti-government property destruction.',
                'desc' => 'Mike Rusniak was imprisoned in Illinois for taking a police car and other acts of anti-government property destruction (per the Anarchist Black Cross, 2003).'],

            ['name' => 'Benjamin Persky', 'first' => 'Benjamin', 'last' => 'Persky', 'gender' => 'Male',
                'state' => 'New York', 'era' => '2000s', 'ideologies' => ['Animal liberation'], 'affiliation' => ['Animal liberation movement'],
                'status' => 'released', 'prison' => ['Eastern NY Correctional Facility', 'Napanoch', 'New York'], 'inmate' => '03R3916',
                'charges' => 'Property destruction at protests targeting Huntingdon Life Sciences (anti-HLS demonstrations).',
                'desc' => 'Benjamin Persky was an animal-rights activist imprisoned in New York for property destruction during protests against Huntingdon Life Sciences (the anti-HLS campaign).'],

            ['name' => 'Malik Smith', 'first' => 'Malik', 'last' => 'Smith', 'gender' => 'Male',
                'state' => 'U.S. Virgin Islands', 'era' => '1970s', 'ideologies' => ['Black liberation', 'Anti-colonialism'], 'affiliation' => ['Virgin Islands Five'],
                'status' => 'unknown', 'prison' => ['Wallens Ridge State Prison', 'Big Stone Gap', 'Virginia'], 'inmate' => '295935',
                'charges' => 'Convicted in the 1972 Fountain Valley case on St. Croix; imprisoned for actions against U.S. colonial rule in the Virgin Islands.',
                'desc' => 'Malik Smith was imprisoned for decades for actions against U.S. colonial rule in the Virgin Islands — one of the group (with Abdul Aziz and Hanif Shabazz Bey / Beaumont Gereau) convicted in the 1972 Fountain Valley case on St. Croix. He was latterly held far from home at Virginia\'s Wallens Ridge supermax.'],

            ['name' => 'Eric Wildcat Hall', 'first' => 'Eric', 'middle' => 'Wildcat', 'last' => 'Hall', 'gender' => 'Male',
                'state' => 'Pennsylvania', 'era' => '1990s', 'ideologies' => ['Indigenous rights'], 'affiliation' => [],
                'status' => 'unknown', 'prison' => ['SCI Albion', 'Albion', 'Pennsylvania'], 'inmate' => 'BL-5355',
                'charges' => 'Sentenced to 35–75 years for helping ship arms to Indigenous activists in Central America.',
                'desc' => 'Eric Wildcat Hall is an Indigenous-rights activist sentenced to 35 to 75 years for his role in shipping arms to Indigenous activists in Central America (per the Anarchist Black Cross, 2003).'],

            ['name' => 'Andy Riendeau', 'first' => 'Andy', 'last' => 'Riendeau', 'aka' => 'John Two Names', 'gender' => 'Male',
                'state' => 'Alabama', 'era' => '2000s', 'ideologies' => ['Indigenous rights'], 'affiliation' => [],
                'status' => 'unknown', 'prison' => ['Elmore Correctional Facility', 'Elmore', 'Alabama'], 'inmate' => '193786',
                'charges' => 'Held on an arson charge his supporters call a frame-up.',
                'desc' => 'Andy J. Riendeau, known as "John Two Names," is a longtime Native American activist whom the Anarchist Black Cross (2003) described as framed on an arson charge while held in Alabama.'],

            ['name' => 'Tewahnee Sahme', 'first' => 'Tewahnee', 'last' => 'Sahme', 'gender' => 'Male',
                'state' => 'Oregon', 'era' => '2000s', 'ideologies' => ['Indigenous rights'], 'affiliation' => [],
                'status' => 'unknown', 'prison' => ['Snake River Correctional Institution', 'Ontario', 'Oregon'], 'inmate' => '11186353',
                'charges' => 'Given additional prison time over a prison uprising.',
                'desc' => 'Tewahnee Sahme is a Native rights advocate who, per the Anarchist Black Cross (2003), was given additional prison time in Oregon in connection with a prison uprising.'],

            ['name' => 'David Scalera', 'first' => 'David', 'last' => 'Scalera', 'aka' => 'Looks Away', 'gender' => 'Male',
                'state' => 'Oregon', 'era' => '2000s', 'ideologies' => ['Indigenous rights'], 'affiliation' => [],
                'status' => 'unknown', 'prison' => ['Eastern Oregon Correctional Institution', 'Pendleton', 'Oregon'], 'inmate' => '13405480',
                'charges' => 'Given additional prison time over a prison uprising.',
                'desc' => 'David Scalera, known as "Looks Away," is a Native rights advocate who, per the Anarchist Black Cross (2003), was given additional prison time in Oregon in connection with a prison uprising.'],
        ];

        $added = 0;
        foreach ($people as $p) {
            // The existing DB "James Johnson" is a different person (Mohaman
            // Koti), so for him match on his unique inmate number to stay
            // idempotent without colliding with that record; everyone else
            // matches by slug/name.
            if ($p['name'] === 'James Johnson') {
                $existing = Prisoner::withoutGlobalScopes()->where('inmate_number', $p['inmate'])->first();
            } else {
                $existing = Prisoner::withoutGlobalScopes()
                    ->where('slug', Str::slug($p['name']))
                    ->orWhere('name', $p['name'])
                    ->first();
            }

            if ($existing) {
                $this->line("Skipped (already present): {$existing->name}");

                continue;
            }

            $prisoner = new Prisoner([
                'name' => $p['name'], 'first_name' => $p['first'], 'middle_name' => $p['middle'] ?? null,
                'last_name' => $p['last'], 'aka' => $p['aka'] ?? null, 'gender' => $p['gender'],
                'state' => $p['state'], 'inmate_number' => $p['inmate'], 'era' => $p['era'],
                'ideologies' => $p['ideologies'], 'affiliation' => $p['affiliation'],
                'in_custody' => false,
                'released' => $p['status'] === 'released',
                'under_review' => false,
                'description' => $p['desc'],
            ]);
            $prisoner->save();
            $added++;
            $this->info("Created: {$prisoner->name} (/prisoner/{$prisoner->slug})");

            if (! empty($p['prison']) || ! empty($p['charges'])) {
                $institutionId = null;
                if (! empty($p['prison'])) {
                    $institutionId = Institution::firstOrCreate(
                        ['name' => $p['prison'][0]],
                        ['city' => $p['prison'][1], 'state' => $p['prison'][2]],
                    )->id;
                }
                $prisoner->cases()->create([
                    'institution_id' => $institutionId,
                    'charges' => $p['charges'],
                ]);
            }
        }

        $this->info("\nDone. Added {$added} record(s).");

        return self::SUCCESS;
    }
}
