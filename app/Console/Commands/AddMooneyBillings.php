<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Tom Mooney and Warren Billings — the militant San Francisco labor radicals
 * framed for the July 22, 1916 Preparedness Day parade bombing on perjured
 * testimony, in one of the great international "class-war prisoner" causes of
 * the century. Confirmed not already in the database (they appear only in a
 * newspaper-PDF index, not the prisoner rosters). Sourced to Wikipedia, TIME,
 * and the Northern District of California Historical Society. Idempotent.
 */
class AddMooneyBillings extends Command {
    protected $signature = 'prisoners:add-mooney-billings';
    protected $description = 'Add Tom Mooney and Warren Billings (1916 Preparedness Day bombing frame-up)';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Tom Mooney', 'first' => 'Tom', 'last' => 'Mooney',
                'ideologies' => ['Labor', 'Socialism'],
                'institution' => ['name' => 'San Quentin State Prison', 'city' => 'San Quentin', 'state' => 'California'],
                'bio' => 'Thomas J. Mooney (1882–1942) was a militant San Francisco labor organizer who, with Warren Billings, was framed for the bombing of the city\'s Preparedness Day parade on July 22, 1916, in which a suitcase bomb killed ten people. A socialist and union activist the authorities had long sought to silence, Mooney was convicted in 1917 on the testimony of witnesses later shown to have committed perjury, in a prosecution that became a byword for the frame-up of labor radicals. Sentenced to hang, he won a commutation to life imprisonment from Governor William Stephens in 1918 — two weeks before his scheduled execution — after President Woodrow Wilson and a worldwide protest movement intervened. The "Free Tom Mooney" campaign grew into one of the great international causes of the era. After 22 years in San Quentin, Mooney was finally pardoned by Governor Culbert Olson in January 1939.',
                'charges' => 'The July 22, 1916 bombing of San Francisco\'s Preparedness Day parade (ten killed) — a charge built on perjured testimony that the labor movement and later official investigations recognized as a frame-up of a militant union organizer.',
                'convicted' => 'Yes — convicted in 1917 and sentenced to death; commuted to life in 1918 after international protest, and pardoned by Governor Culbert Olson in January 1939.',
                'sentence' => 'Death, commuted to life imprisonment (1918); served 22 years in San Quentin before his 1939 pardon.',
            ],
            [
                'name' => 'Warren Billings', 'first' => 'Warren', 'last' => 'Billings',
                'ideologies' => ['Labor'],
                'institution' => ['name' => 'Folsom State Prison', 'city' => 'Represa', 'state' => 'California'],
                'bio' => 'Warren K. Billings (1893–1972) was a young shoe-factory worker and labor radical convicted alongside Tom Mooney for the July 22, 1916 Preparedness Day bombing in San Francisco — a prosecution built on perjured testimony that became internationally notorious as a frame-up of labor militants. Tried first, Billings was convicted in 1916 and sentenced to life imprisonment, which he served largely at Folsom State Prison. Though official inquiries repeatedly found the convictions baseless, California authorities resisted freeing the men for two decades. Billings was finally released in 1939 when Governor Culbert Olson commuted his sentence; he received a full pardon in 1961.',
                'charges' => 'The July 22, 1916 Preparedness Day parade bombing in San Francisco — convicted, with Tom Mooney, on perjured testimony in what became a celebrated frame-up of labor radicals.',
                'convicted' => 'Yes — convicted in 1916 and sentenced to life imprisonment.',
                'sentence' => 'Life imprisonment; released in 1939 when Governor Olson commuted his sentence, with a full pardon in 1961.',
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $inst = Institution::firstOrCreate(
                    ['name' => $c['institution']['name']],
                    ['city' => $c['institution']['city'], 'state' => $c['institution']['state']]
                );

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => 'Male',
                    'state'          => 'California',
                    'era'            => '1910s',
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => [],
                    'in_custody'     => false,
                    'released'       => true,
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id'    => $prisoner->id,
                    'institution_id' => $inst->id,
                    'charges'        => $c['charges'],
                    'convicted'      => $c['convicted'],
                    'sentence'       => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
