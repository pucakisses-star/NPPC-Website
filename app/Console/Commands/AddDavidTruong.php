<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds David Truong (Trương Đình Hùng), the Vietnamese anti-war activist
 * convicted in the 1978 Truong–Humphrey espionage case. The prosecution relied
 * on roughly a year of warrantless FBI surveillance, and the resulting appeal
 * (United States v. Truong Dinh Hung, 629 F.2d 908 (4th Cir. 1980)) became the
 * landmark "Truong doctrine" on foreign-intelligence surveillance. Sourced to
 * the Wikipedia profile and the reported appellate decision. Idempotent.
 */
class AddDavidTruong extends Command {
    protected $signature = 'prisoners:add-david-truong';
    protected $description = 'Add David Truong (Truong Dinh Hung), defendant in the 1978 Truong–Humphrey espionage case';

    private const BIO = <<<'TXT'
David Truong — born Trương Đình Hùng in Saigon on September 2, 1945 — was a Vietnamese anti–Vietnam War activist and the defendant in one of the most significant espionage prosecutions of the era. He was the son of Trương Đình Dzũ, a South Vietnamese lawyer who ran for president in 1967 on a platform of negotiating with the National Liberation Front to end the war. While living in the United States, Truong became active in the antiwar peace movement.

In January 1978 Truong was arrested with co-defendant Ronald Humphrey, a U.S. Information Agency officer, and charged with six counts including conspiracy, espionage, theft of government property, and acting as an unregistered agent of a foreign government. The government alleged that Humphrey supplied classified State Department diplomatic cables that Truong passed toward the government of Vietnam through a courier who was in fact secretly cooperating with the FBI. The FBI had surveilled Truong for roughly a year without a warrant; the resulting appeal, United States v. Truong Dinh Hung, 629 F.2d 908 (4th Cir. 1980), became a landmark ruling recognizing a "foreign intelligence" exception to the Fourth Amendment's warrant requirement — the so-called "Truong doctrine" — that proved influential in later FISA jurisprudence.

Both men were convicted in 1978 in the U.S. District Court for the Eastern District of Virginia and sentenced to 15 years in prison. Truong began serving his sentence in 1982, after his appeals were exhausted, and was paroled in 1986. Supporters viewed the case as a politically motivated prosecution of an antiwar activist, noting that the documents at issue were low-level diplomatic cables rather than military secrets. After his release Truong married the economist Carolyn Gates, taught economics, and consulted in the Netherlands and Malaysia. He died of cancer in Penang, Malaysia, on June 26, 2014.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'David Truong')->exists()) {
            $this->error('David Truong already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'David Truong',
                'first_name'     => 'David',
                'last_name'      => 'Truong',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'race'           => 'Asian',
                'birthdate'      => '1945-09-02',
                'death_date'     => '2014-06-26',
                'state'          => 'Virginia',
                'era'            => '1970s',
                'ideologies'     => ['Anti-war'],
                'affiliation'    => [],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges'     => 'Six counts including conspiracy, espionage, theft of government property, and acting as an unregistered agent of a foreign government — for passing classified U.S. State Department diplomatic cables toward the government of Vietnam, with co-defendant Ronald Humphrey (a U.S. Information Agency officer).',
                'arrest_date' => '1978-01-31',
                'convicted'   => 'Yes — convicted in 1978 in the U.S. District Court for the Eastern District of Virginia; sentenced to 15 years. The conviction was upheld in United States v. Truong Dinh Hung, 629 F.2d 908 (4th Cir. 1980).',
                'sentence'    => '15 years in federal prison — began serving in 1982 after appeals; paroled in 1986.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
