<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Ronald Louis Humphrey, the U.S. Information Agency officer convicted
 * alongside David Truong in the 1978 Truong–Humphrey espionage case. Humphrey
 * supplied the classified cables; his defense was that he acted to win the
 * release of his Vietnamese common-law wife and her four children — a
 * humanitarian motive distinct from Truong's antiwar activism. Sourced to the
 * Wikipedia profiles, contemporaneous Washington Post trial coverage, and the
 * reported appellate decision (629 F.2d 908). Idempotent.
 */
class AddRonaldHumphrey extends Command {
    protected $signature = 'prisoners:add-ronald-humphrey';
    protected $description = 'Add Ronald Humphrey, USIA officer convicted in the 1978 Truong–Humphrey espionage case';

    private const BIO = <<<'TXT'
Ronald Louis Humphrey, born around 1936, was a United States Information Agency (USIA) foreign-service officer and the co-defendant of anti–Vietnam War activist David Truong in the 1978 Truong–Humphrey espionage case. While serving in Vietnam, Humphrey had taken a Vietnamese common-law wife, Kim; the abrupt end of the war in 1975 left her and her four children stranded in Vietnam, unable to leave.

Humphrey gave Truong USIA materials, including classified State Department diplomatic cables, which Truong passed toward the government of Vietnam. Humphrey's defense was that he acted to win the release of his common-law wife and her four children — and, he said, to support diplomatic reconciliation between the United States and Vietnam — rather than to harm the United States. Truong served as the courier; the materials were routed through an intermediary who was secretly cooperating with the FBI.

Humphrey and Truong were arrested on January 31, 1978 and charged with six counts including conspiracy, espionage, theft of classified information, and failing to register as agents of a foreign government. Both were convicted in May 1978 in the U.S. District Court for the Eastern District of Virginia and sentenced in July 1978 to 15 years in prison. The case is often described as the only espionage case to come out of the Vietnam War, and the roughly year-long warrantless FBI surveillance that preceded the arrests produced the landmark "Truong doctrine" (United States v. Truong Dinh Hung, 629 F.2d 908 (4th Cir. 1980)); the Supreme Court declined to hear the final appeal in January 1982. Humphrey began serving his sentence in 1982 and was paroled in 1986. Sympathetic accounts at the time — including a 1982 Sojourners article titled "Scapegoated" — portrayed him as a man punished for trying to rescue his family.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Ronald Humphrey')->exists()) {
            $this->error('Ronald Humphrey already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'Ronald Humphrey',
                'first_name'     => 'Ronald',
                'last_name'      => 'Humphrey',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'state'          => 'Virginia',
                'era'            => '1970s',
                'ideologies'     => [],
                'affiliation'    => [],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges'     => 'Six counts including conspiracy, espionage, theft of classified information, and failing to register as an agent of a foreign government — for supplying classified U.S. State Department diplomatic cables (obtained through his USIA position) to co-defendant David Truong, who passed them toward the government of Vietnam.',
                'arrest_date' => '1978-01-31',
                'convicted'   => 'Yes — convicted in May 1978 in the U.S. District Court for the Eastern District of Virginia; sentenced in July 1978 to 15 years. Conviction upheld in United States v. Truong Dinh Hung, 629 F.2d 908 (4th Cir. 1980); the Supreme Court declined review in January 1982.',
                'sentence'    => '15 years in federal prison — began serving in 1982; paroled in 1986.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
