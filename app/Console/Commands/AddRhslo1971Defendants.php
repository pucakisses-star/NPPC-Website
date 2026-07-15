<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The four University of Wisconsin–Madison activists arrested on May 4, 1971
 * after a disruptive protest in the Gordon Commons dining hall during the
 * Residence Halls Student Labor Organization (RHSLO) strike — a campaign for
 * recognition and collective bargaining for student residence-hall and
 * food-service workers. Tried in summer 1971; all four were free pending appeal
 * as of December 2, 1971 (whether the sentences were ultimately served is
 * undocumented).
 *
 *   Ellen Budow      — disorderly conduct — 2 months
 *   Judy Greenspan   — disorderly conduct — 2 months
 *   David Hofstetter — disorderly conduct + resisting arrest — 6 months
 *   Willard Lenton   — disorderly conduct + resisting arrest — 9 months
 *
 * Idempotent (skips by name).
 */
final class AddRhslo1971Defendants extends Command
{
    protected $signature = 'prisoners:add-rhslo-1971';

    protected $description = 'Add the four 1971 UW–Madison RHSLO / Gordon Commons defendants (Budow, Greenspan, Hofstetter, Lenton)';

    private const SHARED = 'was one of four University of Wisconsin–Madison activists arrested on May 4, 1971 '
        .'following a disruptive protest in the Gordon Commons dining hall during the Residence Halls Student Labor '
        .'Organization (RHSLO) strike — a roughly two-week campaign for recognition, a contract, and improved '
        .'conditions for student residence-hall and food-service workers. Tried in the summer of 1971, all four '
        .'remained free pending appeal as of December 2, 1971; whether the sentence was ultimately served is not '
        .'documented in the available reporting.';

    public function handle(): int
    {
        DB::transaction(function () {
            // Ellen R. Budow — full researched biography.
            $budow = $this->make('Ellen R. Budow', 'Ellen', 'R.', 'Budow', 'Female',
                'Ellen R. Budow was born in 1952 and became active against the Vietnam War while attending Evanston '
                .'Township High School. She later studied art and art history at the University of Wisconsin–Madison and '
                .'participated in the Residence Halls Student Labor Organization (RHSLO)\'s campaign to obtain recognition, '
                .'collective bargaining rights, and improved conditions for student food-service and dormitory workers. '
                .'During the RHSLO strike of May 1971, Budow was arrested with three other activists following a '
                .'disruptive protest in Gordon Commons. Tried during the summer, she was convicted of disorderly conduct '
                .'and sentenced to two months; she remained free pending appeal as of December 2, 1971, and it has not '
                .'yet been established whether she ultimately served the sentence. Budow later moved to Massachusetts, '
                .'where she worked as an organizer or labor activist with the United Electrical, Radio and Machine '
                .'Workers of America (UE). She died in 1974, at approximately twenty-two, reportedly after pneumonia was '
                .'misdiagnosed. She is buried in Dissenters\' Row near the Haymarket Martyrs\' Monument at Forest Home '
                .'Cemetery in Forest Park, Illinois.',
                ['Labor rights', 'Anti-war'],
                ['Residence Halls Student Labor Organization', 'United Electrical Workers']);
            $budow->setPartialDate('birthdate', 1952);
            $budow->setPartialDate('death_date', 1974);
            $budow->save();
            $this->case($budow, 'Disorderly conduct, during the May 1971 RHSLO strike protest at Gordon Commons.',
                'Two months. Free pending appeal as of December 2, 1971; whether she served any of the sentence is not documented.');

            // Co-defendants.
            $greenspan = $this->make('Judy Greenspan', 'Judy', null, 'Greenspan', 'Female',
                'Judy Greenspan '.self::SHARED.' Greenspan was convicted of disorderly conduct and sentenced to two months.',
                ['Labor rights'], ['Residence Halls Student Labor Organization']);
            $this->case($greenspan, 'Disorderly conduct (May 1971 RHSLO strike protest at Gordon Commons).',
                'Two months. Free pending appeal as of December 2, 1971.');

            $hofstetter = $this->make('David Hofstetter', 'David', null, 'Hofstetter', 'Male',
                'David Hofstetter '.self::SHARED.' Hofstetter was convicted of disorderly conduct and resisting arrest and sentenced to six months.',
                ['Labor rights'], ['Residence Halls Student Labor Organization']);
            $this->case($hofstetter, 'Disorderly conduct and resisting arrest (May 1971 RHSLO strike protest at Gordon Commons).',
                'Six months. Free pending appeal as of December 2, 1971.');

            $lenton = $this->make('Willard Lenton', 'Willard', null, 'Lenton', 'Male',
                'Willard Lenton '.self::SHARED.' Lenton was convicted of disorderly conduct and resisting arrest and sentenced to nine months.',
                ['Labor rights'], ['Residence Halls Student Labor Organization']);
            $this->case($lenton, 'Disorderly conduct and resisting arrest (May 1971 RHSLO strike protest at Gordon Commons).',
                'Nine months. Free pending appeal as of December 2, 1971.');
        });

        $this->info('Done. Added the 1971 RHSLO / Gordon Commons defendants (skipping any already present).');

        return self::SUCCESS;
    }

    private function make(string $name, string $first, ?string $middle, string $last, string $gender, string $desc, array $ideologies, array $affiliation): Prisoner
    {
        $existing = Prisoner::withUnderReview()->where('name', $name)->first();
        if ($existing) {
            $this->warn('Skipped (already exists): '.$name);

            return $existing;
        }

        $p = Prisoner::create([
            'name' => $name,
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
            'gender' => $gender,
            'state' => 'Wisconsin',
            'era' => '1970s',
            'ideologies' => $ideologies,
            'affiliation' => $affiliation,
            'description' => $desc,
            'in_custody' => false,
            'released' => true,
            'in_exile' => false,
            'currently_in_exile' => false,
            'awaiting_trial' => false,
        ]);
        $this->info('Added: '.$name);

        return $p;
    }

    private function case(Prisoner $p, string $charges, string $sentence): void
    {
        if ($p->cases()->count() > 0) {
            return;
        }
        $case = new PrisonerCase(['prisoner_id' => $p->id]);
        $case->fill([
            'prisoner_id' => $p->id,
            'charges' => $charges,
            'convicted' => 'Yes — convicted in the summer of 1971.',
            'sentence' => $sentence,
        ]);
        $case->setPartialDate('arrest_date', 1971, 5, 4);
        $case->save();
    }
}
