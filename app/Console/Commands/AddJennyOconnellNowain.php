<?php

namespace App\Console\Commands;

use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Jenny O'Connell-Nowain — 41-year-old former preschool teacher
 * from Redding, California, convicted of misdemeanor disrupting a
 * public meeting (Penal Code 403) after silently holding a protest
 * sign in front of the Shasta County Board of Supervisors dais in
 * November 2024. After a weeklong jury trial she was convicted in
 * January 2026; offered probation she rejected as a First Amendment
 * speech restriction, she was sentenced January 28, 2026 to 90 days
 * incarceration, served as roughly 45 days of house arrest with an
 * ankle monitor through early March 2026.
 *
 * Surfaced via r/law thread linking The Guardian and Shasta Scout.
 *
 * Idempotent — re-runs report "already exists."
 */
final class AddJennyOconnellNowain extends Command {
    protected $signature = 'prisoners:add-oconnell-nowain';
    protected $description = "Add Jenny O'Connell-Nowain (Shasta County silent-protest case, 2026 sentencing)";

    public function handle(): int {
        if (Prisoner::where('slug', 'jenny-oconnell-nowain')
            ->orWhere('slug', 'jenny-o-connell-nowain')
            ->orWhere('name', 'Jenny O\'Connell-Nowain')
            ->exists()
        ) {
            $this->warn("Jenny O'Connell-Nowain already in DB — skipping.");
            return self::SUCCESS;
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['name' => 'Shasta County Sheriff\'s Office (home detention)'],
                ['city' => 'Redding', 'state' => 'California']
            );

            $prisoner = Prisoner::create([
                'name'         => "Jenny O'Connell-Nowain",
                'first_name'   => 'Jenny',
                'last_name'    => "O'Connell-Nowain",
                'gender'       => 'Female',
                'race'         => 'White',
                'state'        => 'California',
                'era'          => '2020s',
                'ideologies'   => ['First Amendment', 'Civil Liberties'],
                'affiliation'  => [],
                'in_custody'   => false,
                'released'     => true,
                'description'  => "Jenny O'Connell-Nowain is a 41-year-old former preschool teacher from Redding, California with no prior criminal history. At a Shasta County Board of Supervisors meeting in November 2024 she briefly interrupted former supervisor Patrick Jones, who was criticizing the county elections office, then sat silently on the floor in front of the dais holding a political sign. She was charged with a misdemeanor under California Penal Code §403 (disrupting a public meeting).\n\nAfter a weeklong jury trial in early 2026 she was convicted. Visiting Judge Thomas L. Bender (Madera County) offered her probation; the proposed conditions would have required her to follow Board-of-Supervisors meeting rules going forward. She refused, telling the court she could not in good conscience accept terms that conflicted with her First Amendment rights. On January 28, 2026 Judge Bender sentenced her to 90 days in the custody of the Shasta County Sheriff's Office, which she served as roughly 45 days of house arrest with an ankle monitor before being released in early March 2026. Her husband Benjamin lost his job over the family's protest activity. The case drew national attention as an early test of how aggressively local prosecutors can criminalize silent, sit-in–style protest at public meetings; Deputy DA Emily Mees prosecuted.\n\nSources: r/law (March 2026), The Guardian, Shasta Scout, KRCR, A News Cafe.",
            ]);

            PrisonerCase::create([
                'prisoner_id'        => $prisoner->id,
                'institution_id'     => $institution->id,
                'charges'            => 'California Penal Code §403 — disrupting a public meeting (misdemeanor) at the Shasta County Board of Supervisors, November 2024.',
                'arrest_date'        => '2024-11-19',
                'sentenced_date'     => '2026-01-28',
                'incarceration_date' => '2026-01-28',
                'release_date'       => '2026-03-13',
                'imprisoned_for_days' => 45,
                'plead'              => 'Not guilty (jury trial)',
                'convicted'          => 'Yes — Shasta County, January 2026',
                'judge'              => 'Hon. Thomas L. Bender (visiting from Madera County)',
                'prosecutor'         => 'Deputy DA Emily Mees (Shasta County)',
                'sentence'           => '90 days in Shasta County Sheriff\'s custody; served as ~45 days of home detention with ankle monitor after rejecting probation terms she considered a First Amendment restriction',
            ]);
        });

        $this->info("Added Jenny O'Connell-Nowain.");

        return self::SUCCESS;
    }
}
