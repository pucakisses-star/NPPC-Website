<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Additional Civil War political prisoners connected to Fort Delaware, drawn
 * from the Fort Delaware State Park's 2021 series on the fort's political
 * prisoners. Adds only the people not already in the database (Vallandigham,
 * Pleasants, Joice, Shanks, De La Mar, Handy, and Francis "Frank" Richardson
 * were already present) and deliberately excludes the Conrad Kuhl murder case.
 *
 * Several of these survive only as "recovered snippets," so the details are thin
 * and stated conservatively — where a charge or disposition is not recoverable,
 * that is said outright rather than invented. Mirrors the existing Fort Delaware
 * cohort (era "1800s"). Idempotent — skips any name already present.
 */
class AddFortDelawarePrisoners extends Command
{
    protected $signature = 'prisoners:add-fort-delaware-prisoners';

    protected $description = 'Add six more Fort Delaware Civil War political prisoners (excludes the Conrad Kuhl murder case)';

    public function handle(): int
    {
        foreach ($this->people() as $p) {
            DB::transaction(function () use ($p) {
                if (Prisoner::where('name', $p['name'])->exists()) {
                    $this->warn('Skipped (already exists): '.$p['name']);

                    return;
                }

                $prisoner = Prisoner::create(array_merge([
                    'name' => $p['name'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                    'era' => '1800s',
                ], $p['fields']));

                PrisonerCase::create(array_merge(['prisoner_id' => $prisoner->id], $p['case']));

                $this->info('Added: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            });
        }

        return self::SUCCESS;
    }

    private function people(): array
    {
        return [
            [
                'name' => 'Daniel Flanagan',
                'fields' => [
                    'first_name' => 'Daniel', 'last_name' => 'Flanagan',
                    'gender' => 'Male', 'state' => 'Ohio', 'ideologies' => ['Anti-war'],
                    'description' => 'Daniel Flanagan was the editor of the Mason Democrat in Mason, Ohio. In February 1865 he was tried by a military commission on charges that included discouraging enlistments — one of the Northern antiwar "Copperhead" newspaper editors prosecuted for their opposition to the Civil War. He is featured in the Fort Delaware State Park\'s series on the fort\'s Civil War political prisoners.',
                ],
                'case' => [
                    'charges' => 'Tried by a military commission in February 1865 on charges including discouraging enlistments, as editor of the Mason Democrat (Mason, Ohio).',
                    'convicted' => 'Tried by military commission; the disposition is not fully documented in the available sources.',
                ],
            ],
            [
                'name' => 'Levin L. Waters',
                'fields' => [
                    'first_name' => 'Levin', 'middle_name' => 'L.', 'last_name' => 'Waters',
                    'gender' => 'Male', 'state' => 'Maryland', 'ideologies' => ['Confederate sympathies'],
                    'description' => 'Levin L. Waters was a printer in Princess Anne, Maryland, who raised a Confederate flag in front of his print shop in April 1861, at the outbreak of the Civil War, and was arrested amid the pro-Confederate political activity on Maryland\'s Eastern Shore. He is among the Civil War political prisoners featured in the Fort Delaware State Park series.',
                ],
                'case' => [
                    'charges' => 'Arrested for raising a Confederate flag in front of his print shop in Princess Anne, Maryland, in April 1861.',
                    'convicted' => 'Arrested amid pro-Confederate political activity; the disposition is not fully documented in the available sources.',
                ],
            ],
            [
                'name' => 'Lawrence H. Mathews',
                'fields' => [
                    'first_name' => 'Lawrence', 'middle_name' => 'H.', 'last_name' => 'Mathews',
                    'gender' => 'Male', 'state' => 'Florida', 'ideologies' => ['Confederate sympathies'],
                    'description' => 'Lawrence H. Mathews was a journalist for the Pensacola Observer in Florida who, in the Fort Delaware State Park\'s series on the fort\'s Civil War political prisoners, is described as the "first Confederate political prisoner." The full details of his charge are not recoverable from the available snippets.',
                ],
                'case' => [
                    'charges' => 'Held as a political prisoner in connection with his work as a journalist for the Pensacola Observer; described as the "first Confederate political prisoner." Full charge details are not recoverable from the available sources.',
                    'convicted' => 'Disposition not documented in the available sources.',
                ],
            ],
            [
                'name' => 'Wesley Shields',
                'fields' => [
                    'first_name' => 'Wesley', 'last_name' => 'Shields',
                    'gender' => 'Male', 'race' => 'Black', 'state' => 'Maryland', 'ideologies' => [],
                    'description' => 'Wesley Shields claimed to be the first African American in Baltimore to volunteer for U.S. Army service during the Civil War. A recovered account from the Fort Delaware State Park series links Shields and George Butler to fines and imprisonment at Fort Delaware; the full circumstances of the charge are not clear from the available snippets.',
                ],
                'case' => [
                    'charges' => 'Fined and imprisoned at Fort Delaware; the full charge is not clear from the available sources. He had claimed to be the first African American in Baltimore to volunteer for U.S. Army service.',
                    'convicted' => 'Fined; the full disposition is not documented in the available sources.',
                ],
            ],
            [
                'name' => 'George Butler',
                'fields' => [
                    'first_name' => 'George', 'last_name' => 'Butler',
                    'gender' => 'Male', 'state' => 'Maryland', 'ideologies' => [],
                    'description' => 'George Butler was linked, in a recovered account from the Fort Delaware State Park series, with Wesley Shields — the man who claimed to be the first African American in Baltimore to volunteer for U.S. Army service — in connection with fines and imprisonment at Fort Delaware during the Civil War. The full circumstances of his case are not documented in the available snippets.',
                ],
                'case' => [
                    'charges' => 'Fined and imprisoned at Fort Delaware in connection with Wesley Shields; the full charge is not documented in the available sources.',
                    'convicted' => 'Fined; the full disposition is not documented in the available sources.',
                ],
            ],
            [
                'name' => 'Henry Sack',
                'fields' => [
                    'first_name' => 'Henry', 'last_name' => 'Sack',
                    'gender' => 'Male', 'ideologies' => ['Confederate sympathies'],
                    'description' => 'Henry Sack was listed as a resident of the Confederate States and tried in May 1864 as a political prisoner connected to Fort Delaware. The full details of his case are not recoverable from the available Fort Delaware State Park snippets.',
                ],
                'case' => [
                    'charges' => 'Tried in May 1864 as a resident of the Confederate States; held as a political prisoner connected to Fort Delaware. Full charge details are not recoverable from the available sources.',
                    'convicted' => 'Disposition not documented in the available sources.',
                ],
            ],
        ];
    }
}
