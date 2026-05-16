<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Roman Sterlingov — Russian-Swedish dual national convicted in
 * March 2024 in the U.S. District Court for the District of Columbia
 * of operating Bitcoin Fog, the long-running Bitcoin mixing service.
 * Sentenced November 2024 to 150 months (12.5 years) federal prison.
 * He maintains he never operated Bitcoin Fog and the government's
 * case rested on Chainalysis blockchain-tracing output with no
 * published error rate. Appeal at oral argument before the D.C.
 * Circuit on May 12, 2026.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddRomanSterlingov extends Command {
    protected $signature = 'prisoners:add-roman-sterlingov';
    protected $description = 'Add Roman Sterlingov (Bitcoin Fog case, 2024 conviction, 2026 appeal)';

    public function handle(): int {
        if (Prisoner::where('slug', 'roman-sterlingov')
            ->orWhere('name', 'Roman Sterlingov')
            ->exists()
        ) {
            $this->warn('Roman Sterlingov already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Federal Bureau of Prisons (location varied)']
            );

            $prisoner = Prisoner::create([
                'name'         => 'Roman Sterlingov',
                'first_name'   => 'Roman',
                'last_name'    => 'Sterlingov',
                'gender'       => 'Male',
                'race'         => 'White',
                'state'        => 'District of Columbia',
                'era'          => '2020s',
                'ideologies'   => ['Financial Privacy', 'Cypherpunk'],
                'affiliation'  => [],
                'in_custody'   => true,
                'released'     => false,
                'description'  => "Roman Sterlingov is a Russian-Swedish dual national who has been in U.S. federal custody since his April 2021 arrest at Los Angeles International Airport. In March 2024 a jury in the U.S. District Court for the District of Columbia convicted him of money laundering, operating an unlicensed money-transmitting business, and District of Columbia money-transmission violations, on the government's theory that he was the operator of **Bitcoin Fog**, a Bitcoin-mixing service that ran from 2011 to 2021. In November 2024 U.S. District Judge Randolph Moss sentenced him to **150 months (12.5 years)** in federal prison and ordered the forfeiture of approximately 1,354 BTC.\n\nSterlingov maintains his innocence. His defense, and a substantial body of outside commentary from privacy advocates, blockchain forensics researchers, and the Electronic Frontier Foundation, argues that the conviction rests almost entirely on proprietary blockchain-tracing output from the firm **Chainalysis** that has no published error rate and cannot be independently audited. The defense notes that the government's search of his computers, hard drives, diaries, notes, and password logs at the time of his arrest produced **no server logs, no Bitcoin Fog private keys, no admin credentials, and no internal communications** linking him to the service. Sterlingov has consistently stated that he was a Bitcoin Fog *customer*, not its operator.\n\nThe case has been closely watched by the digital-privacy and cypherpunk communities as a test of whether federal prosecutors can obtain criminal convictions based on closed-source forensic software whose methodology defendants cannot challenge — a question the Sixth Amendment Confrontation Clause and Daubert evidentiary standards both touch on. Sterlingov's appeal is scheduled for oral argument before the U.S. Court of Appeals for the D.C. Circuit on **May 12, 2026**.\n\nThe entry is neutral. The government's allegation is that Bitcoin Fog laundered roughly \$400 million in cryptocurrency over a decade, including funds tied to darknet drug and contraband markets; supporters frame the prosecution as a misidentification powered by black-box forensic software that sets a dangerous precedent for digital-privacy infrastructure and pro-se / under-resourced defendants generally.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'Money laundering, operating an unlicensed money-transmitting business, and District of Columbia money-transmission violations — alleged operation of the Bitcoin Fog cryptocurrency mixer, 2011–2021.',
                'arrest_date'        => '2021-04-27',
                'incarceration_date' => '2021-04-27',
                'sentenced_date'     => '2024-11-08',
                'plead'              => 'Not guilty (jury trial)',
                'convicted'          => 'Yes — March 12, 2024, jury verdict, D.D.C.',
                'sentence'           => '150 months (12.5 years) federal prison + forfeiture of approximately 1,354 BTC; appeal pending in D.C. Circuit (oral argument May 12, 2026)',
                'judge'              => 'Hon. Randolph D. Moss (D.D.C.)',
            ]);
        });

        $this->info('Added Roman Sterlingov.');

        return self::SUCCESS;
    }
}
