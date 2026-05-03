<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddZoeRosenberg extends Command
{
    protected $signature = 'prisoners:add-zoe-rosenberg';
    protected $description = 'Add Zoe Rosenberg, the Direct Action Everywhere animal rights activist convicted in the Petaluma Poultry case.';

    private const BIO = <<<'TXT'
Zoe Rosenberg is an American animal rights activist, animal sanctuary founder, and a leading public face of the open-rescue wing of the U.S. animal liberation movement. Born in 2002 to veterinarian Sherstin Rosenberg, she went vegan at age 11 and at age 12, in 2014, founded the Happy Hen Animal Sanctuary in San Luis Obispo, California, which has since rescued hundreds of chickens, ducks, and other animals from the egg and meat industries. As a teenager she became one of the youngest organizers in Direct Action Everywhere (DxE), a grassroots network that has built much of its public strategy around "open rescue" — entering factory farms and slaughterhouses, removing visibly sick or injured animals, and publicly claiming the action under the legal theory of a "right to rescue." She is a student at the University of California, Berkeley.

On June 13, 2023, Rosenberg helped lead a coordinated DxE action at the Petaluma Poultry processing facility in Sonoma County, a Perdue subsidiary that processes roughly two million chickens a month. Wearing high-visibility vests to blend in with workers, Rosenberg's team entered the operating slaughterhouse and removed four live hens from a newly arrived transport trailer; the birds were later named Poppy, Ivy, Aster, and Azalea and placed at sanctuary. A second team triggered the facility alarms and opened the front gate, allowing a larger group of activists to rush onto the property; a third team, in a separate county, stopped a Petaluma Poultry truck and removed additional birds. Petaluma Poultry shut down its processing line in response.

The Sonoma County District Attorney prosecuted Rosenberg on multiple felony counts. Most of the original counts were narrowed before trial. After a six-week jury trial in fall 2025, a Sonoma County jury convicted her on October 29, 2025 of one felony count of conspiracy under California Penal Code § 182(a)(1) plus two misdemeanor counts of trespass and one misdemeanor count of tampering with a vehicle. On December 3, 2025, Judge Kenneth Gnoss sentenced her to 90 days in the Sonoma County Jail, two years of formal probation, and approximately $102,000 in restitution to Petaluma Poultry. She began her sentence and was approved on or about December 22, 2025 to serve the final 60 days on electronic home monitoring.

Her conviction is one of the rare felony outcomes against an open-rescue activist in the United States — most comparable cases (including the prior Petaluma cases against DxE co-founder Wayne Hsiung and others) have ended in acquittals, hung juries, or misdemeanor pleas. DxE has stated it will appeal. Rosenberg has remained a public spokesperson for the animal liberation movement throughout her prosecution.
TXT;

    public function handle(): int
    {
        if (Prisoner::where('name', 'Zoe Rosenberg')->exists()) {
            $this->error('Zoe Rosenberg already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $jail = Institution::firstOrCreate(
                ['name' => 'Sonoma County Jail'],
                ['city' => 'Santa Rosa', 'state' => 'California']
            );

            $prisoner = Prisoner::create([
                'name'        => 'Zoe Rosenberg',
                'first_name'  => 'Zoe',
                'last_name'   => 'Rosenberg',
                'description' => self::BIO,
                'gender'      => 'Female',
                'birthdate'   => '2002-01-01', // year only public; placeholder day/month
                'state'       => 'California',
                'era'         => '2020s',
                'ideologies'  => ['Animal rights', 'Vegan', 'Open rescue'],
                'affiliation' => ['Direct Action Everywhere (DxE)', 'Happy Hen Animal Sanctuary'],
                'in_custody'  => false,
                'released'    => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $jail->id,
                'charges'            => 'One felony count of conspiracy (California Penal Code § 182(a)(1)); two misdemeanor counts of trespass; one misdemeanor count of tampering with a vehicle — arising from the June 13, 2023 Direct Action Everywhere open-rescue action at the Petaluma Poultry processing facility',
                'arrest_date'        => '2023-06-13',
                'incarceration_date' => '2025-12-03',
                'release_date'       => '2025-12-22',
                'convicted'          => 'Yes — Sonoma County jury verdict, October 29, 2025',
                'sentence'           => '90 days in Sonoma County Jail (served approximately 30 days; remaining 60 days served on electronic home monitoring); 2 years formal probation; approximately $102,000 in restitution',
                'judge'              => 'Kenneth Gnoss',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
