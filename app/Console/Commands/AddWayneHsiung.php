<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddWayneHsiung extends Command
{
    protected $signature = 'prisoners:add-wayne-hsiung';
    protected $description = 'Add Wayne Hsiung, the Direct Action Everywhere co-founder convicted in the Sonoma open-rescue case.';

    private const BIO = <<<'TXT'
Wayne Hsiung is an American lawyer, animal rights organizer, and the co-founder of Direct Action Everywhere (DxE), the international grassroots network that has built much of its strategy around "open rescue" — entering factory farms and slaughterhouses, removing visibly sick or injured animals, and publicly claiming responsibility under the legal theory of a "right to rescue." Born in 1981 to Taiwanese immigrants and trained as a lawyer at the University of Chicago Law School, he previously practiced at DLA Piper and Steptoe & Johnson and taught at Northeastern Law before leaving the legal profession to organize full-time. He co-founded DxE in 2013 and ran for mayor of Berkeley, California, in 2020 on a largely animal-rights platform, finishing second to incumbent Jesse Arreguín with 24% of the vote.

Hsiung has been arrested more than a dozen times in connection with open-rescue actions across the United States. Most cases against him have ended in acquittals (most notably the 2022 Utah jury acquittal in the Smithfield Foods piglet rescue case), in dismissals (the 2024 Wisconsin Ridglan Farms beagle case was dismissed ten days before trial), or in suspended sentences (a 2021 North Carolina conviction for rescuing a sick baby goat carried no incarceration). The Sonoma County prosecution was the major exception.

In May 2018 and again in 2019, Hsiung helped lead two of DxE's largest open-rescue actions in Sonoma County, California: a mass action at Sunrise Farms, a Sunrise Farms egg-laying facility supplying Whole Foods, in which roughly 60 chickens were removed; and a follow-on action at Reichardt Duck Farm. After a six-week jury trial in fall 2023 he was convicted on November 2, 2023 of one felony count of conspiracy and two misdemeanor counts of trespass; the jury hung on a second felony conspiracy count from the duck-farm action.

On November 30, 2023, Sonoma County Superior Court Judge Laura Passaglia sentenced him to 90 days in the Sonoma County Jail, two years of formal probation, and (in a later restitution order) approximately $191,704 payable to the affected farms. He surrendered in December 2023 and served his sentence; this was the first time a U.S. open-rescue activist had been incarcerated under the modern DxE legal-strategy framework, and his case became the legal precedent that the prosecution of Zoe Rosenberg in 2025 was largely built on.

In May 2026, the California Court of Appeal overturned the felony conspiracy conviction and one of the misdemeanor trespass convictions, reversing the most serious component of the case. Hsiung continues to organize through The Simple Heart Initiative.
TXT;

    public function handle(): int
    {
        if (Prisoner::where('name', 'Wayne Hsiung')->exists()) {
            $this->error('Wayne Hsiung already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $jail = Institution::firstOrCreate(
                ['name' => 'Sonoma County Jail'],
                ['city' => 'Santa Rosa', 'state' => 'California']
            );

            $prisoner = Prisoner::create([
                'name'           => 'Wayne Hsiung',
                'first_name'     => 'Wayne',
                'last_name'      => 'Hsiung',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Asian American',
                'birthdate'      => '1981-06-18',
                'state'          => 'California',
                'era'            => '2020s',
                'ideologies'     => ['Animal rights', 'Vegan', 'Open rescue'],
                'affiliation'    => ['Direct Action Everywhere (DxE)', 'The Simple Heart Initiative'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $jail->id,
                'charges'            => 'One felony count of conspiracy (California Penal Code § 182(a)(1)); two misdemeanor counts of trespass — arising from the May 2018 open-rescue action at Sunrise Farms and the 2019 action at Reichardt Duck Farm in Sonoma County (the jury hung on a second felony conspiracy count from the duck-farm action)',
                'arrest_date'        => '2019-09-29',
                'incarceration_date' => '2023-12-01',
                'release_date'       => '2024-02-29',
                'convicted'          => 'Yes — Sonoma County jury verdict, November 2, 2023; the felony conspiracy and one misdemeanor conviction were overturned by the California Court of Appeal in May 2026',
                'sentence'           => '90 days in Sonoma County Jail (served); 2 years formal probation; approximately $191,704 in restitution',
                'judge'              => 'Laura Passaglia',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
