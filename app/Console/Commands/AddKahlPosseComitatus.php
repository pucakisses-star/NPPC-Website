<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds Gordon Wendell Kahl and his son Yorie von Kahl, tax protesters
 * associated with the Posse Comitatus movement, from the USP Leavenworth
 * political-prisoners list. Gordon served a 1977 tax term at Leavenworth and
 * was later killed in the 1983 confrontations in which two U.S. Marshals died;
 * Yorie is serving a life sentence for his role in the same events.
 *
 * Recorded with the same neutral, factual framing as the rest of the database.
 * Idempotent: prisoner:add refuses duplicates by name and the variant-name
 * guard skips anyone already recorded.
 */
final class AddKahlPosseComitatus extends Command
{
    protected $signature = 'prisoners:add-kahl-posse-comitatus';

    protected $description = 'Add Gordon Wendell Kahl and Yorie von Kahl (Posse Comitatus tax protesters, Leavenworth list)';

    public function handle(): int
    {
        $people = [
            [
                'payload' => [
                    'name' => 'Gordon Wendell Kahl',
                    'first_name' => 'Gordon',
                    'middle_name' => 'Wendell',
                    'last_name' => 'Kahl',
                    'description' => "Gordon Wendell Kahl was a North Dakota farmer, World War II veteran, and tax protester associated with the Posse Comitatus movement. He served eight months at the United States Penitentiary, Leavenworth in 1977 after being convicted of failing to pay federal income tax on his 1973 and 1974 earnings. In February 1983 a confrontation with U.S. Marshals attempting to arrest him for a probation violation near Medina, North Dakota left two marshals dead; Kahl fled a nationwide manhunt and was killed in a shootout with law enforcement in Arkansas in June 1983, in which a local sheriff also died. He was the father of Yorie von Kahl.",
                    'state' => 'North Dakota',
                    'gender' => 'Male',
                    'death_date' => '1983-06-03',
                    'ideologies' => ['Tax protest', 'Posse Comitatus'],
                    'affiliation' => ['Posse Comitatus'],
                    'era' => '1970s',
                    'in_custody' => false,
                    'released' => true,
                    'cases' => [[
                        'charges' => 'Convicted of failing to pay federal income tax on his 1973 and 1974 earnings.',
                        'convicted' => 'Convicted of tax offenses, 1977',
                        'sentence' => 'Served eight months at USP Leavenworth in 1977.',
                        'institution_name' => 'United States Penitentiary, Leavenworth',
                        'institution_city' => 'Leavenworth',
                        'institution_state' => 'Kansas',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1977, null, null], 'release_date' => [1977, null, null]],
            ],
            [
                'payload' => [
                    'name' => 'Yorie von Kahl',
                    'first_name' => 'Yorie',
                    'last_name' => 'von Kahl',
                    'description' => "Yorie von Kahl is the son of Gordon Wendell Kahl and, like his father, was associated with the Posse Comitatus tax-protest movement. He was convicted for his role in the February 1983 shootout near Medina, North Dakota, in which two U.S. Marshals were killed, and is serving a life sentence. He was held at USP Leavenworth, transferred to the maximum-security penitentiary at Terre Haute, Indiana when Leavenworth was downgraded to medium security, and is currently held at FCI Pekin, Illinois.",
                    'state' => 'North Dakota',
                    'gender' => 'Male',
                    'inmate_number' => '04565-059',
                    'ideologies' => ['Tax protest', 'Posse Comitatus'],
                    'affiliation' => ['Posse Comitatus'],
                    'era' => '1980s',
                    'in_custody' => true,
                    'released' => false,
                    'cases' => [[
                        'charges' => 'Convicted for his role in the February 1983 shootout near Medina, North Dakota, in which two U.S. Marshals were killed.',
                        'convicted' => 'Convicted, 1983; sentenced to life',
                        'sentence' => 'Serving a life sentence; held at USP Leavenworth, then USP Terre Haute, and currently FCI Pekin.',
                        'institution_name' => 'Federal Correctional Institution, Pekin',
                        'institution_city' => 'Pekin',
                        'institution_state' => 'Illinois',
                    ]],
                ],
                'dates' => ['incarceration_date' => [1983, null, null]],
            ],
        ];

        $added = 0;
        foreach ($people as $person) {
            $payload = $person['payload'];
            $inCustody = $payload['in_custody'] ?? false;
            $released = $payload['released'] ?? ! $inCustody;
            $payload['in_custody'] = $inCustody;
            $payload['released'] = $released;

            $existing = Prisoner::withoutGlobalScopes()
                ->where('name', 'like', '%'.$payload['first_name'].'%')
                ->where('name', 'like', '%'.$payload['last_name'].'%')
                ->first();
            if ($existing) {
                $this->line("  already in database as \"{$existing->name}\" — skipping {$payload['name']}.");

                continue;
            }

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = $inCustody;
            $prisoner->released = $released;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case && ! empty($person['dates'])) {
                foreach ($person['dates'] as $field => [$y, $m, $d]) {
                    $case->setPartialDate($field, $y, $m, $d);
                }
                $case->save();
            }
            $added++;
        }

        $this->info("\nDone. Processed {$added} prisoner(s).");

        return self::SUCCESS;
    }
}
