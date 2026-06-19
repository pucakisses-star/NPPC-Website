<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Ludwig Martens — Soviet Russia's first unofficial representative to the
 * United States (1919–1921), head of the Russian Soviet Government Bureau in
 * New York. His office was raided by New York's Lusk Committee in June 1919,
 * and after U.S. Senate and Department of Labor hearings he was deported to
 * Soviet Russia in January 1921 — a consummated first-Red-Scare deportation.
 * Idempotent (skips if he already exists).
 */
final class AddLudwigMartens extends Command
{
    protected $signature = 'prisoners:add-ludwig-martens';

    protected $description = 'Add Ludwig Martens (Soviet Bureau; deported in the first Red Scare, 1921)';

    public function handle(): int
    {
        if (Prisoner::withUnderReview()->where('name', 'Ludwig Martens')->exists()) {
            $this->warn('Ludwig Martens already exists — skipping.');

            return self::SUCCESS;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name' => 'Ludwig Martens',
                'first_name' => 'Ludwig',
                'last_name' => 'Martens',
                'aka' => 'Ludwig Karlovich Martens; Ludwig C. A. K. Martens',
                'gender' => 'Male',
                'race' => 'White',
                'birthdate' => '1875-01-01',
                'death_date' => '1948-10-19',
                'state' => 'New York',
                'era' => '1910s',
                'ideologies' => ['Communism'],
                'affiliation' => ['Russian Soviet Government Bureau'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "Ludwig C. A. K. Martens (1875–1948) was a Russian-born engineer and Bolshevik who, in March 1919, became Soviet Russia's first unofficial representative — a de facto ambassador — to the United States, heading the Russian Soviet Government Bureau in New York City and establishing commercial contacts with hundreds of American firms at a time when the U.S. did not recognize the Soviet government. In June 1919 his offices were raided by New York State's Lusk Committee at the height of the first Red Scare, and he became the target of U.S. Senate and Department of Labor deportation hearings. He was deported to Soviet Russia in January 1921, leaving with his Bureau staff. Back in the USSR he held senior technical and industrial posts — heading metallurgical and diesel-research bodies and editing the Soviet Technical Encyclopedia — before retiring in 1941 and dying in Moscow in 1948. As a young revolutionary he had earlier been imprisoned for three years (from 1896) in Tsarist Russia.",
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => "Federal deportation proceedings as the unofficial representative of Soviet Russia and head of the Russian Soviet Government Bureau in New York; his office was raided by New York's Lusk Committee in June 1919",
                'convicted' => 'Held deportable after U.S. Senate and Department of Labor hearings',
                'sentence' => 'Deported to Soviet Russia in January 1921',
            ]);
        });

        $this->info('Added Ludwig Martens.');

        return self::SUCCESS;
    }
}
