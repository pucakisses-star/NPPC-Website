<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Add Franklin "Beto" Coral Garrido to the prisoner database — the Colombian
 * leftist activist and ally of President Gustavo Petro detained by ICE in
 * Phoenix on June 16, 2026 over a visa overstay, in a removal the Colombian
 * government and supporters call political persecution (a Rubio memo cited his
 * pro-Petro activism). Idempotent: skips if a Beto Coral record already exists
 * with a case.
 */
final class AddBetoCoralPrisoner extends Command
{
    protected $signature = 'prisoners:add-beto-coral';

    protected $description = 'Add Beto Coral (Franklin Coral Garrido), ICE-detained Colombian activist, to the database';

    public function handle(): int
    {
        $description = <<<'DESC'
Franklin Humberto Coral-Garrido, known as Beto Coral, is a Colombian lawyer and leftist activist and an ally of President Gustavo Petro. Born in Medellín — the son of a Colombian police captain killed shortly after taking part in the operation that tracked down Pablo Escobar — he had lived in Arizona, where he drove for Uber. On June 16, 2026, ICE arrested him in Phoenix for overstaying his B1/B2 visa. The detention and deportation were authorized by a memorandum personally signed the same day by U.S. Secretary of State Marco Rubio, which cited Coral's "political activities in support of the Petro government" and his campaigning against U.S.-aligned Colombian presidential candidate Abelardo De la Espriella. President Petro condemned the arrest as "political persecution" and demanded his release, and supporters have described him as a political prisoner. An immigration court hearing was scheduled for June 30, 2026.
DESC;

        DB::transaction(function () use ($description) {
            $prisoner = Prisoner::withoutGlobalScopes()->where('name', 'Beto Coral')->first();
            if ($prisoner && PrisonerCase::where('prisoner_id', $prisoner->id)->exists()) {
                $this->warn('Skipping — Beto Coral already exists with a case.');

                return;
            }

            if (! $prisoner) {
                $prisoner = Prisoner::create([
                    'name' => 'Beto Coral',
                    'first_name' => 'Franklin',
                    'middle_name' => 'Humberto',
                    'last_name' => 'Coral-Garrido',
                    'aka' => 'Franklin Humberto Coral-Garrido',
                    'gender' => 'Male',
                    'description' => $description,
                    'state' => 'Arizona',
                    'era' => '2020s',
                    'ideologies' => ['Leftist'],
                    'in_custody' => true,
                    'released' => false,
                    'imprisoned_or_exiled' => true,
                ]);
                $this->info("Added {$prisoner->name} (slug: {$prisoner->slug})");
            }

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges' => 'Civil immigration detention — overstaying a B1/B2 visa, placed in removal (deportation) proceedings. Detention and deportation authorized by a June 16, 2026 memorandum signed by Secretary of State Marco Rubio citing his political activism in support of Colombian President Gustavo Petro.',
                'arrest_date' => '2026-06-16',
                'incarceration_date' => '2026-06-16',
                'convicted' => 'No — civil immigration detention, not a criminal conviction',
            ]);
        });

        $this->info('Done. View: /prisoner/beto-coral');

        return self::SUCCESS;
    }
}
