<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Alex Markley and Antonio "Tony" Suares, co-defendants in
 * United States v. Markley & Suares, 567 F.2d 523 (1st Cir. 1977). Markley was
 * the Western Massachusetts organizer for the United Electrical Workers (UE) and
 * a member of UE Local 259 in Holyoke; the case grew out of a 1975 strike by
 * ~500 Local 259 members at the Worthington Compressor plant in Holyoke. Both
 * were convicted under the National Firearms Act of possessing/transferring
 * homemade "destructive devices" meant to destroy trucks of Martin Brothers
 * Trucking Co. (which the union accused of undermining the strike), transferred
 * to an undercover FBI agent.
 *
 * Bios draw on the appellate opinion plus contemporaneous UE / "Out Front" (1976)
 * coverage (union name, the 11-week strike, Markley's July 8, 1976 arrest and the
 * union defense campaign). No sentence appears in any located source, so that
 * field is left null rather than invented. Uses updateOrCreate (matched by name /
 * prisoner_id), so it can be re-run to refresh the records in place.
 */
class AddMarkleySuares extends Command {
    protected $signature = 'prisoners:add-markley-suares';
    protected $description = 'Add/refresh Alex Markley and Antonio "Tony" Suares (United States v. Markley & Suares, 1st Cir. 1977)';

    private const MARKLEY_BIO = <<<'TXT'
Alex Markley was the Western Massachusetts organizer for the United Electrical, Radio and Machine Workers of America (UE) and a member of UE Local 259 in Holyoke. The case against him grew out of an 11-week strike in 1975 by some 500 UE Local 259 members at the Worthington Compressor plant (Worthington Corporation, Holyoke Works) in Holyoke, Massachusetts.

According to the federal appellate record, the charged conduct involved homemade explosive "destructive devices" — cardboard tubes packed with black powder and fitted with fuses — intended to destroy trucks of Martin Brothers Trucking Company, which the union regarded as undermining the strike; on November 7, 1975 Markley gave such a device to a man who turned out to be an undercover FBI agent. Markley was arrested on July 8, 1976 and, according to the UE, held incommunicado for twelve hours before being indicted by a federal grand jury. The UE International and Local 259 publicly defended him, calling the defeat of the charges "a responsibility not only to Mr. Markley, but to the entire labor movement."

He was convicted in the U.S. District Court for the District of Massachusetts of one count of possessing and four counts of transferring unregistered destructive devices under the National Firearms Act (26 U.S.C. §§ 5861(d) and 5861(e)); a related conspiracy count was dismissed after trial. The U.S. Court of Appeals for the First Circuit affirmed the convictions on December 16, 1977 (United States v. Markley, 567 F.2d 523, No. 77-1209); the appeal contested only whether the items were statutory "destructive devices," and no entrapment or political-prosecution defense was raised. The sentence is not stated in the available record.
TXT;

    private const SUARES_BIO = <<<'TXT'
Antonio Suares — known as Tony — was a co-defendant of UE union organizer Alex Markley in the case arising from the 1975 strike by United Electrical (UE) Local 259 members at the Worthington Compressor plant in Holyoke, Massachusetts. According to the federal appellate record, Suares assembled and delivered three homemade explosive "destructive devices" to an undercover FBI agent for $75 on December 8, 1975; the devices were meant to be used against trucks of Martin Brothers Trucking Company, which the union accused of undermining the strike.

He was convicted in the U.S. District Court for the District of Massachusetts of three counts of possessing and three counts of transferring unregistered destructive devices under the National Firearms Act (26 U.S.C. §§ 5861(d) and 5861(e)); a related conspiracy count was dismissed after trial. The First Circuit affirmed the convictions on December 16, 1977 (United States v. Suares, 567 F.2d 523, No. 77-1210). No entrapment or political-prosecution defense was raised on appeal, and the record does not state his sentence. Beyond the court record, little about Suares is publicly documented.
TXT;

    private const CONVICTED = 'Yes — convicted in the U.S. District Court for the District of Massachusetts (a related conspiracy count was dismissed after trial); convictions affirmed by the U.S. Court of Appeals for the First Circuit on December 16, 1977 (567 F.2d 523).';

    public function handle(): int {
        DB::transaction(function () {
            $this->upsertPrisoner(
                'Alex Markley', 'Alex', 'Markley', self::MARKLEY_BIO,
                ['Labor', 'Trade unionism'],
                ['United Electrical, Radio and Machine Workers (UE) Local 259'],
                'One count of possessing an unregistered destructive device (26 U.S.C. § 5861(d)) and four counts of transferring unregistered destructive devices (26 U.S.C. §§ 5812, 5861(e); 18 U.S.C. § 2) — homemade black-powder explosive devices tied to the 1975 UE Local 259 strike at the Worthington Compressor plant in Holyoke, Massachusetts and intended to destroy trucks of Martin Brothers Trucking Company; transferred to an undercover FBI agent. Indicted by a federal grand jury after a July 8, 1976 arrest.',
                '1976-07-08'
            );

            $this->upsertPrisoner(
                'Antonio Suares', 'Antonio', 'Suares', self::SUARES_BIO,
                ['Labor'],
                [],
                'Three counts of possessing and three counts of transferring unregistered destructive devices (26 U.S.C. §§ 5861(d), 5861(e)) — assembled and delivered three homemade black-powder explosive devices to an undercover FBI agent for $75 on December 8, 1975, for use against Martin Brothers Trucking Company trucks during the UE Local 259 strike in Holyoke, Massachusetts.',
                null
            );
        });

        return self::SUCCESS;
    }

    private function upsertPrisoner(string $name, string $first, string $last, string $bio, array $ideologies, array $affiliation, string $charges, ?string $arrestDate): void {
        $prisoner = Prisoner::updateOrCreate(
            ['name' => $name],
            [
                'first_name'     => $first,
                'last_name'      => $last,
                'description'    => $bio,
                'gender'         => 'Male',
                'state'          => 'Massachusetts',
                'era'            => '1970s',
                'ideologies'     => $ideologies,
                'affiliation'    => $affiliation,
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]
        );

        PrisonerCase::updateOrCreate(
            ['prisoner_id' => $prisoner->id],
            [
                'charges'     => $charges,
                'arrest_date' => $arrestDate,
                'convicted'   => self::CONVICTED,
            ]
        );

        $verb = $prisoner->wasRecentlyCreated ? 'Added' : 'Updated';
        $this->info("{$verb}: {$prisoner->name} (slug: {$prisoner->slug})");
    }
}
