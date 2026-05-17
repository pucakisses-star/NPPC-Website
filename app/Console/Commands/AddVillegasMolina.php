<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Isaac Antonio Villegas Molina — Pasadena, California
 * construction worker and lead plaintiff in the ACLU's class-action
 * civil-rights suit Vasquez Perdomo v. Mullin, challenging the LA
 * ICE "roving raids" of summer 2025. Originally detained June 18,
 * 2025 at a Pasadena Metro stop. Re-detained April 16, 2026 at his
 * regular ICE check-in — what his lawyers and supporters call
 * retaliation for the lawsuit, days before a judge was set to rule
 * on a motion to terminate his removal proceedings. Released by
 * order of U.S. District Judge Michelle Williams on April 18, 2026,
 * with the government barred from re-detaining him without notice
 * and a neutral-adjudicator hearing.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddVillegasMolina extends Command {
    protected $signature = 'prisoners:add-villegas-molina';
    protected $description = 'Add Isaac Antonio Villegas Molina (Pasadena Three / Vasquez Perdomo v. Mullin, 2026)';

    public function handle(): int {
        if (Prisoner::where('slug', 'isaac-villegas-molina')
            ->orWhere('slug', 'isaac-antonio-villegas-molina')
            ->orWhere('name', 'Isaac Antonio Villegas Molina')
            ->orWhere('name', 'Isaac Villegas Molina')
            ->exists()
        ) {
            $this->warn('Isaac Antonio Villegas Molina already in DB — skipping.');
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Adelanto ICE Processing Center'],
                ['city' => 'Adelanto', 'state' => 'California']
            );

            $prisoner = Prisoner::create([
                'name'         => 'Isaac Antonio Villegas Molina',
                'first_name'   => 'Isaac',
                'middle_name'  => 'Antonio',
                'last_name'    => 'Villegas Molina',
                'gender'       => 'Male',
                'race'         => 'Latino',
                'state'        => 'California',
                'era'          => '2020s',
                'ideologies'   => ['Immigrant Rights', 'Civil Rights'],
                'affiliation'  => ['Vasquez Perdomo v. Mullin (lead plaintiff)', 'Pasadena Three'],
                'in_custody'   => false,
                'released'     => true,
                'description'  => "Isaac Antonio Villegas Molina is a construction worker and Pasadena, California resident who became the lead plaintiff in the landmark class-action civil-rights lawsuit **Vasquez Perdomo v. Mullin**, brought by the ACLU of Southern California to challenge the Trump administration's summer-2025 ICE \"roving raids\" across Los Angeles County on Fourth Amendment grounds. He and two other day-laborer plaintiffs are known as the **Pasadena Three**.\n\nHis original arrest came on **June 18, 2025**, when he and several coworkers were waiting at a Metro stop in front of a Winchell's Donuts in Pasadena for a pickup truck to take them to a construction job. ICE agents drove up in plainclothes vehicles and detained the entire group without a warrant — agents later testified the basis was that the men \"looked illegal.\" The class action alleges these stops violated the Fourth Amendment's protections against unlawful searches and seizures and amounted to systemic ethnic profiling.\n\nOn **April 16, 2026**, just one week before an immigration judge was scheduled to rule on a motion to terminate Villegas Molina's removal proceedings, ICE re-detained him at his regular check-in appointment and shipped him back to the **Adelanto ICE Processing Center** in San Bernardino County. His lawyers and the National Day Laborer Organizing Network (NDLON) called the re-detention transparent retaliation for his lead-plaintiff role in the federal lawsuit. \"I think 100% this is retaliation for this lawsuit,\" his counsel told reporters. \"He is the main litigant in the national racial-profiling case before the Supreme Court.\"\n\nOn **April 18, 2026**, U.S. District Judge **Michelle Williams** ordered his immediate release and barred the federal government from re-detaining him without prior notice and a hearing before a neutral adjudicator. Villegas Molina walked out of Adelanto that night to a crowd of supporters organized by NDLON, the ACLU SoCal, and the Coalition for Humane Immigrant Rights (CHIRLA). The underlying Vasquez Perdomo v. Mullin litigation continues; the racial-profiling claim is heading to the U.S. Supreme Court on the government's interlocutory appeal.",
            ]);

            PrisonerCase::create([
                'prisoner_id'    => $prisoner->id,
                'institution_id' => $institution->id,
                'charges'        => 'Immigration detention — no criminal charges. Originally detained without warrant during ICE "roving raid" in Pasadena, June 18, 2025; re-detained April 16, 2026 at ICE check-in, allegedly in retaliation for serving as lead plaintiff in Vasquez Perdomo v. Mullin (4th Amendment class-action against LA ICE raids).',
                'arrest_date'    => '2025-06-18',
                'release_date'   => '2026-04-18',
                'convicted'      => 'No — civil immigration detention only',
                'sentence'       => 'No criminal sentence; held in ICE custody twice (June 2025 and April 2026), released by federal court order in both instances',
                'judge'          => 'Hon. Michelle Williams (U.S. District Court, C.D. Cal.) — ordered April 2026 release',
            ]);
        });

        $this->info('Added Isaac Antonio Villegas Molina.');

        return self::SUCCESS;
    }
}
