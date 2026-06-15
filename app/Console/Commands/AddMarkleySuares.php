<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Alex Markley and Antonio "Tony" Suares, co-defendants in
 * United States v. Markley & Suares, 567 F.2d 523 (1st Cir. 1977) — a case
 * arising from a 1975 strike at the Worthington plant in Holyoke, MA. Markley,
 * a union organizer, and Suares were convicted of possessing/transferring
 * unregistered "destructive devices" (homemade black-powder explosives) under
 * the National Firearms Act, transferred to an undercover FBI agent and intended
 * to destroy trucks of Martin Brothers Trucking Company (accused of undermining
 * the strike).
 *
 * Bios are sourced strictly to the appellate opinion (the only documentation
 * located). No sentence, arrest date, or holding facility is stated in that
 * record, so those fields are deliberately left null rather than invented.
 * Idempotent: skips a prisoner who already exists by name.
 */
class AddMarkleySuares extends Command {
    protected $signature = 'prisoners:add-markley-suares';
    protected $description = 'Add Alex Markley and Antonio "Tony" Suares (United States v. Markley & Suares, 1st Cir. 1977)';

    private const MARKLEY_BIO = <<<'TXT'
Alex Markley was a union organizer involved in a 1975 labor strike at the Worthington plant in Holyoke, Massachusetts. The dispute targeted Martin Brothers Trucking Company, which the union regarded as undermining the strike. According to the federal appellate record, on November 7, 1975 Markley gave a homemade explosive "destructive device" — a cardboard tube packed with black powder and fitted with a fuse — to a man who was in fact an undercover FBI agent, telling him to "take care of a couple of trucks"; he later said he intended to blow up two Martin Brothers trucks.

He was convicted in the U.S. District Court for the District of Massachusetts of one count of possessing and four counts of transferring unregistered destructive devices under the National Firearms Act (26 U.S.C. §§ 5861(d) and 5861(e)); a related conspiracy count was dismissed after trial. The U.S. Court of Appeals for the First Circuit affirmed the convictions on December 16, 1977 (United States v. Markley, 567 F.2d 523, No. 77-1209). The appeal turned only on whether the items met the statutory definition of a "destructive device"; no entrapment or political-prosecution defense was raised. The appellate opinion does not state his sentence, and beyond it little about Markley is publicly documented.
TXT;

    private const SUARES_BIO = <<<'TXT'
Antonio Suares — known as Tony — was a co-defendant of union organizer Alex Markley in a case arising from a 1975 strike at the Worthington plant in Holyoke, Massachusetts. According to the federal appellate record, Suares assembled and delivered three homemade explosive "destructive devices" to an undercover FBI agent for $75 on December 8, 1975; the devices were meant to be used against trucks of Martin Brothers Trucking Company, which the union accused of undermining the strike.

He was convicted in the U.S. District Court for the District of Massachusetts of three counts of possessing and three counts of transferring unregistered destructive devices under the National Firearms Act (26 U.S.C. §§ 5861(d) and 5861(e)); a related conspiracy count was dismissed after trial. The First Circuit affirmed the convictions on December 16, 1977 (United States v. Suares, 567 F.2d 523, No. 77-1210). No entrapment or political-prosecution defense was raised on appeal, and the opinion does not state his sentence. Beyond the court opinion, little about Suares is publicly documented.
TXT;

    private const CONVICTED = 'Yes — convicted in the U.S. District Court for the District of Massachusetts (a related conspiracy count was dismissed after trial); convictions affirmed by the U.S. Court of Appeals for the First Circuit on December 16, 1977 (567 F.2d 523).';

    public function handle(): int {
        DB::transaction(function () {
            $this->addPrisoner(
                'Alex Markley', 'Alex', 'Markley', self::MARKLEY_BIO,
                ['Labor', 'Trade unionism'],
                'One count of possessing an unregistered destructive device (26 U.S.C. § 5861(d)) and four counts of transferring unregistered destructive devices (26 U.S.C. §§ 5812, 5861(e); 18 U.S.C. § 2) — homemade black-powder explosive devices made during a 1975 strike at the Worthington plant in Holyoke, Massachusetts and intended to destroy trucks of Martin Brothers Trucking Company; transferred to an undercover FBI agent.'
            );

            $this->addPrisoner(
                'Antonio Suares', 'Antonio', 'Suares', self::SUARES_BIO,
                ['Labor'],
                'Three counts of possessing and three counts of transferring unregistered destructive devices (26 U.S.C. §§ 5861(d), 5861(e)) — assembled and delivered three homemade black-powder explosive devices to an undercover FBI agent for $75 on December 8, 1975, for use against Martin Brothers Trucking Company trucks during the Holyoke strike.'
            );
        });

        return self::SUCCESS;
    }

    private function addPrisoner(string $name, string $first, string $last, string $bio, array $ideologies, string $charges): void {
        if (Prisoner::where('name', $name)->exists()) {
            $this->warn("Skipped (already exists): {$name}");
            return;
        }

        $prisoner = Prisoner::create([
            'name'           => $name,
            'first_name'     => $first,
            'last_name'      => $last,
            'description'    => $bio,
            'gender'         => 'Male',
            'state'          => 'Massachusetts',
            'era'            => '1970s',
            'ideologies'     => $ideologies,
            'in_custody'     => false,
            'released'       => true,
            'awaiting_trial' => false,
        ]);

        PrisonerCase::create([
            'prisoner_id' => $prisoner->id,
            'charges'     => $charges,
            'convicted'   => self::CONVICTED,
        ]);

        $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
    }
}
