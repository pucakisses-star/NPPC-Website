<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the three Ridglan Farms "open rescue" co-defendants (Aditya Aswani,
 * Michelle Lunsky, Dean Wyrzykowski) as awaiting-trial prisoner records, and
 * appends the 2026 Ridglan case to Wayne Hsiung's existing record. All four
 * were charged in Dane County, WI over the March 15, 2026 open rescue of ~22
 * beagles from Ridglan Farms (Blue Mounds). Idempotent: prisoners are matched
 * by name and the case is skipped if one already exists at the same institution.
 */
class AddRidglanBeagleDefendants extends Command {
    protected $signature = 'prisoners:add-ridglan-beagle-defendants';
    protected $description = 'Add the Ridglan Farms open-rescue co-defendants and append Wayne Hsiung\'s 2026 Ridglan case';

    private const CHARGES = 'Four felony counts — burglary (as party to a crime), criminal damage to property, theft, and attempted theft — stemming from the March 15, 2026 "open rescue" of about 22 beagles from Ridglan Farms, a beagle-breeding and research facility in Blue Mounds, Wisconsin; combined maximum exposure roughly 31 years.';
    private const CONVICTED = 'No — pleaded not guilty on May 21, 2026; awaiting trial (jury selection September 28, 2026, before Dane County Circuit Judge John Hyland).';

    public function handle(): int {
        DB::transaction(function () {
            $jail = Institution::firstOrCreate(
                ['name' => 'Dane County Jail'],
                ['city' => 'Madison', 'state' => 'Wisconsin']
            );

            $defendants = [
                ['name' => 'Aditya Aswani',     'first_name' => 'Aditya',  'last_name' => 'Aswani',      'state' => 'New York',   'city' => 'Brooklyn',      'age' => 29, 'bond' => '$10,000'],
                ['name' => 'Michelle Lunsky',   'first_name' => 'Michelle', 'last_name' => 'Lunsky',     'state' => 'Arizona',    'city' => 'Mesa',          'age' => 33, 'bond' => '$10,000'],
                ['name' => 'Dean Wyrzykowski',  'first_name' => 'Dean',    'last_name' => 'Wyrzykowski', 'state' => 'California', 'city' => 'San Francisco', 'age' => 29, 'bond' => '$10,000'],
            ];

            foreach ($defendants as $d) {
                $prisoner = Prisoner::firstOrCreate(
                    ['name' => $d['name']],
                    [
                        'first_name'     => $d['first_name'],
                        'last_name'      => $d['last_name'],
                        'description'    => $this->bio($d),
                        'state'          => $d['state'],
                        'era'            => '2020s',
                        'ideologies'     => ['Animal rights', 'Open rescue'],
                        'affiliation'    => ['Direct Action Everywhere (DxE)'],
                        'in_custody'     => false,
                        'released'       => false,
                        'awaiting_trial' => true,
                    ]
                );

                $this->addRidglanCase($prisoner, $jail, '2026-03-15', self::CONVICTED);
                $this->info("Ensured: {$prisoner->name} (slug: {$prisoner->slug})");
            }

            // Append the 2026 Ridglan case to Wayne Hsiung's existing record.
            $hsiung = Prisoner::where('name', 'Wayne Hsiung')->first();
            if ($hsiung) {
                $this->addRidglanCase(
                    $hsiung,
                    $jail,
                    '2026-04-18',
                    'No — pleaded not guilty on May 21, 2026 and is representing himself; released on a $20,000 bond; awaiting trial (jury selection September 28, 2026, before Dane County Circuit Judge John Hyland).'
                );
                $this->info('Appended 2026 Ridglan case to existing record: Wayne Hsiung');
            } else {
                $this->warn('Wayne Hsiung not found — run prisoners:add-wayne-hsiung first to attach his 2026 Ridglan case.');
            }
        });

        return self::SUCCESS;
    }

    private function addRidglanCase(Prisoner $prisoner, Institution $jail, string $arrestDate, string $convicted): void {
        $exists = PrisonerCase::where('prisoner_id', $prisoner->id)
            ->where('institution_id', $jail->id)
            ->exists();
        if ($exists) {
            return;
        }

        PrisonerCase::create([
            'prisoner_id'    => $prisoner->id,
            'institution_id' => $jail->id,
            'charges'        => self::CHARGES,
            'arrest_date'    => $arrestDate,
            'convicted'      => $convicted,
            'prosecutor'     => 'Dane County District Attorney',
            'judge'          => 'John Hyland',
        ]);
    }

    private function bio(array $d): string {
        return "{$d['name']}, {$d['age']}, of {$d['city']}, {$d['state']}, is an animal-rights activist and one of four co-defendants — alongside Direct Action Everywhere (DxE) co-founder Wayne Hsiung — charged in Dane County, Wisconsin over the March 15, 2026 \"open rescue\" at Ridglan Farms, a beagle-breeding and research facility in Blue Mounds. About 27 people were arrested at the scene and the sheriff later referred charges against dozens more. {$d['last_name']} was charged in April 2026 with four felonies — burglary (as party to a crime), criminal damage to property, theft, and attempted theft — carrying a combined maximum of roughly 31 years, and pleaded not guilty on May 21, 2026 after release on a {$d['bond']} bond. Trial is set for September 28–October 9, 2026 before Dane County Circuit Judge John Hyland.";
    }
}
