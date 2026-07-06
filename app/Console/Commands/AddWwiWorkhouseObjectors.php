<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * World War I conscientious objectors imprisoned in CIVILIAN workhouses and
 * houses of correction (via the federal courts), rather than the military
 * disciplinary barracks at Fort Leavenworth/Douglas/Alcatraz. The bulk WWI CO
 * loader keyed on the military-prison record and so missed these men, whose
 * imprisonment is recorded only in the notes of Anne Yoder's Swarthmore College
 * Peace Collection database (e.g. "sentenced to the workhouse," "house of
 * correction").
 *
 * Create-or-update by name; rebuilds each single case. Idempotent.
 */
final class AddWwiWorkhouseObjectors extends Command
{
    protected $signature = 'prisoners:add-wwi-workhouse-objectors';

    protected $description = 'Add WWI COs imprisoned in civilian workhouses / houses of correction (Yoder database)';

    public function handle(): int
    {
        $cleveland = Institution::firstOrCreate(['name' => 'Cleveland House of Correction (Warrensville Workhouse)'], ['city' => 'Cleveland', 'state' => 'Ohio'])->id;
        $detroit = Institution::firstOrCreate(['name' => 'Detroit House of Correction'], ['city' => 'Detroit', 'state' => 'Michigan'])->id;

        $people = [
            [
                'name' => 'Aldis Gerber', 'first' => 'Aldis', 'last' => 'Gerber', 'aka' => 'Aldie A. Gerber',
                'state' => 'Ohio', 'denom' => 'Mennonite', 'birth' => [1890, 6, 24],
                'bio' => 'Aldis Gerber was a Mennonite conscientious objector from Wayne County, Ohio. Sent to Camp Sherman, he refused to wear the uniform and worked in the camp hospital until that was disallowed because of his refusal. After a group of objectors was discharged, his local draft board had him brought back and taken to trial in Cleveland, where he appeared without a lawyer — saying "God was defender" — and was sentenced to about eight months in the workhouse (the Cleveland House of Correction at Warrensville). Anne Yoder\'s WWI database also records the case under the name Aldie A. Gerber, of Orrville, Ohio — fined $200 and sentenced to eight to twelve months in the Cleveland House of Correction (U.S. District Court case #4193) in February 1919; the two entries appear to describe the same man.',
                'charges' => 'Refusing military service as a conscientious objector; tried in U.S. District Court at Cleveland, Ohio.',
                'convicted' => 'Yes — convicted in federal court at Cleveland, Ohio.',
                'sentence' => 'About eight to twelve months in the Cleveland House of Correction (Warrensville Workhouse); fined $200.',
                'incarceration' => [1919, 2], 'inst' => $cleveland,
            ],
            [
                'name' => 'David E. Baumgartner', 'first' => 'David', 'middle' => 'E.', 'last' => 'Baumgartner',
                'state' => 'Ohio', 'denom' => 'Mennonite', 'birth' => [1894, 9, 6],
                'bio' => 'David E. Baumgartner was a Mennonite conscientious objector from Orrville, Ohio. Drafted in the fall of 1917 and sent to Camp Sherman, he was put in the guardhouse for refusing to bear arms and would not wear the uniform. He was imprisoned in the workhouse at Warrensville, Ohio — the Cleveland House of Correction — where he was visited by the Mennonite ministers S. E. Allgyer and Eli Frey. He was discharged in the summer of 1918.',
                'charges' => 'Refusing to bear arms or wear the uniform as a Mennonite conscientious objector (Camp Sherman).',
                'convicted' => 'Imprisoned in the Cleveland House of Correction (Warrensville Workhouse).',
                'sentence' => 'Held in the workhouse at Warrensville, Ohio; discharged in the summer of 1918.',
                'incarceration' => [1918], 'inst' => $cleveland,
            ],
            [
                'name' => 'Levi J. Hershberger', 'first' => 'Levi', 'middle' => 'J.', 'last' => 'Hershberger',
                'state' => 'Ohio', 'denom' => 'Amish',
                'bio' => 'Levi J. Hershberger was an Amish conscientious objector from Fredericksburg, Ohio. Family letters preserved in Anne Yoder\'s WWI database record that he was "arrested for $500" and was held "in the workhouse in West Virginia"; little further detail about his imprisonment survives.',
                'charges' => 'Conscientious objection to military service during World War I.',
                'convicted' => 'Held in a workhouse in West Virginia.',
                'sentence' => 'Imprisoned in a workhouse in West Virginia (further details unrecorded).',
                'incarceration' => [1918], 'inst' => null,
            ],
            [
                'name' => 'Ellwood Burdsall Moore', 'first' => 'Ellwood', 'middle' => 'Burdsall', 'last' => 'Moore',
                'state' => 'Michigan',
                'bio' => 'Ellwood Burdsall Moore, of Ann Arbor, Michigan, refused to register for the draft on June 5, 1917 as a conscientious objector. In July 1917 he was taken before a federal court in Detroit, tried, and sentenced to one year of solitary confinement, which he served at the Detroit House of Correction. A contemporary account reported that he was "suffering greatly," his physical condition so poor that it was feared he could not survive the year.',
                'charges' => 'Refusing to register for the draft as a conscientious objector (June 5, 1917).',
                'convicted' => 'Yes — tried in federal court at Detroit, Michigan (July 1917).',
                'sentence' => 'One year of solitary confinement at the Detroit House of Correction.',
                'incarceration' => [1917, 7], 'inst' => $detroit,
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('name', $p['name'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'middle_name' => $p['middle'] ?? null,
                    'last_name' => $p['last'],
                    'aka' => $p['aka'] ?? null,
                    'gender' => 'Male',
                    'race' => 'White',
                    'state' => $p['state'] ?? null,
                    'era' => '1910s',
                    'ideologies' => ['Pacifism', 'Conscientious objection'],
                    'affiliation' => ! empty($p['denom']) ? [$p['denom']] : [],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                if (! empty($p['birth'])) {
                    $prisoner->setPartialDate('birthdate', ...$p['birth']);
                }
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $p['inst'] ?? null,
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);
                if (! empty($p['incarceration'])) {
                    $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                }
                $case->save();

                $this->info(($prisoner->wasRecentlyCreated ? 'Added: ' : 'Filled: ').$prisoner->name.' (slug: '.$prisoner->slug.')');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
