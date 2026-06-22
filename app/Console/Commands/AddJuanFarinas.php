<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Juan Pedro Farinas — a Cuban-born member of the Trotskyist Workers League
 * who became a Vietnam-era anti-war cause célèbre. Though opposed to the war, he
 * reported to his New York Army induction center when drafted; outside, he
 * joined an anti-war demonstration and handed leaflets to fellow draftees. For
 * this he was prosecuted under the Selective Service Act, convicted in December
 * 1970 of interfering with the Selective Service System, and sentenced to two
 * years. The Second Circuit affirmed the conviction (United States v. Farinas,
 * 448 F.2d 1334, 1971) and the U.S. Supreme Court declined to hear his appeal on
 * March 1, 1972; a broad "Juan Farinas Defense Committee" campaign supported him.
 *
 * The exact prison entry/release dates are not established in accessible sources,
 * so the case records the charge, the December 1970 conviction, and the two-year
 * sentence without precise custody dates. Idempotent.
 */
final class AddJuanFarinas extends Command
{
    protected $signature = 'prisoners:add-juan-farinas';

    protected $description = 'Add Juan Farinas (Workers League anti-war draft case, Selective Service Act, 1970)';

    public function handle(): int
    {
        $fields = [
            'name' => 'Juan Farinas',
            'first_name' => 'Juan',
            'last_name' => 'Farinas',
            'aka' => 'Juan Pedro Farinas',
            'gender' => 'Male',
            'race' => 'Hispanic',
            'state' => 'New York',
            'era' => '1970s',
            'ideologies' => ['Socialism', 'Anti-war'],
            'affiliation' => ['Workers League'],
            'in_custody' => false,
            'released' => true,
            'awaiting_trial' => false,
            'description' => 'Juan Pedro Farinas was a Cuban-born member of the Trotskyist Workers League who became a Vietnam-era anti-war cause célèbre. Although opposed to the war, he reported to his New York Army induction center when drafted; outside the building he joined an anti-war demonstration and handed leaflets to other draftees, discussing the war with them. For this he was prosecuted under the Selective Service Act and convicted in December 1970 of interfering with the Selective Service System, and sentenced to two years in federal prison. A broad coalition rallied to his defense through the Juan Farinas Defense Committee; the Second Circuit Court of Appeals affirmed his conviction in 1971 (United States v. Farinas, 448 F.2d 1334), and the U.S. Supreme Court declined to hear his appeal on March 1, 1972.',
        ];

        $case = [
            'charges' => 'Interfering with the Selective Service System (Selective Service Act) — anti-war leafleting and protest at the Army induction center',
            'convicted' => 'Yes — convicted December 1970; affirmed by the Second Circuit (United States v. Farinas, 448 F.2d 1334, 1971); U.S. Supreme Court denied review March 1, 1972',
            'sentence' => 'Two years in federal prison (began serving after his appeals were exhausted in 1972); exact custody dates not documented',
        ];

        $existing = Prisoner::withUnderReview()->where('name', 'Juan Farinas')->first();

        if (! $existing) {
            DB::transaction(function () use ($fields, $case) {
                $prisoner = Prisoner::create($fields);
                $case['prisoner_id'] = $prisoner->id;
                PrisonerCase::create($case);
            });
            $this->info('Added Juan Farinas.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($existing, $fields, $case) {
            $existing->fill($fields)->save();
            $row = $existing->cases()->first();
            if ($row) {
                $row->fill($case)->save();
            } else {
                $case['prisoner_id'] = $existing->id;
                PrisonerCase::create($case);
            }
        });
        $this->info('Updated Juan Farinas.');

        return self::SUCCESS;
    }
}
