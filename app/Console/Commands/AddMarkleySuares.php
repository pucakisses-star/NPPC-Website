<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Alex Markley and Antonio "Tony" Suares, co-defendants in
 * United States v. Markley & Suares, 567 F.2d 523 (1st Cir. 1977). Markley was a
 * United Electrical Workers (UE) organizer and Suares an IBEW member; the case
 * grew out of a 1975 UE strike at the Worthington Compressor plant in Holyoke,
 * MA. Both were convicted under the National Firearms Act of possessing/
 * transferring "destructive devices" sold to an undercover ATF agent (Thomas
 * O'Reilly); the government alleged a plot to blow up strikebreaker trucks, while
 * the UE called it ATF entrapment and the men maintained their innocence.
 *
 * Sourced to the appellate opinion, contemporaneous UE / "Out Front" (1976)
 * coverage, and Labor Today, "Government Uses Entrapment" (Jul–Aug 1977) — which
 * supplied the sentences (Markley 18 months, Suares 1 year), the ATF agency, and
 * Suares's IBEW membership. Uses updateOrCreate, so re-running refreshes records.
 */
class AddMarkleySuares extends Command {
    protected $signature = 'prisoners:add-markley-suares';
    protected $description = 'Add/refresh Alex Markley and Antonio "Tony" Suares (United States v. Markley & Suares, 1st Cir. 1977)';

    private const MARKLEY_BIO = <<<'TXT'
Alex Markley was a field organizer for the United Electrical, Radio and Machine Workers of America (UE) and a member of UE Local 259 in Holyoke. The case against him grew out of a 1975 strike by UE Local 259 members at the Worthington Compressor plant (Worthington Corporation, Holyoke Works) in Holyoke, Massachusetts, during which strikebreaker ("scab") trucks crossed the picket lines, several strikers were assaulted — one hospitalized — and Markley, a strike leader, was reportedly threatened with death.

According to the federal appellate record, an undercover agent of the U.S. Bureau of Alcohol, Tobacco and Firearms (ATF), Thomas O'Reilly, testified that Markley gave him a homemade "destructive device" — a cardboard tube packed with black powder and fitted with a fuse — in November 1975 and spoke of blowing up trucks of Martin Brothers Trucking Company. Markley was arrested on July 8, 1976 and, according to the UE, held incommunicado for twelve hours before being indicted by a federal grand jury. He and Suares maintained their innocence; the UE and labor supporters characterized the prosecution as an ATF entrapment plot — contending that O'Reilly had appeared during the strike, agitated for violence against the strikebreakers, and that the two men refused, the aim being to pressure them into becoming informers. Local 259 and the International Union mounted a defense campaign, calling the defeat of the charges "a responsibility not only to Mr. Markley, but to the entire labor movement."

He was convicted in the U.S. District Court for the District of Massachusetts of one count of possessing and four counts of transferring unregistered destructive devices under the National Firearms Act (26 U.S.C. §§ 5861(d) and 5861(e)) — a related conspiracy count was dismissed after trial — and sentenced to 18 months in prison. The U.S. Court of Appeals for the First Circuit affirmed the convictions on December 16, 1977 (United States v. Markley, 567 F.2d 523, No. 77-1209); the appeal itself was limited to whether the items met the statutory definition of a "destructive device."
TXT;

    private const SUARES_BIO = <<<'TXT'
Antonio Suares — known as Tony — was a member of the International Brotherhood of Electrical Workers (IBEW) and a co-defendant of UE organizer Alex Markley in the case arising from the 1975 UE strike at the Worthington Compressor plant in Holyoke, Massachusetts. According to the federal appellate record, Suares assembled and delivered three homemade "destructive devices" to an undercover ATF agent (Thomas O'Reilly) for $75 on December 8, 1975; the government alleged the devices were meant for use against trucks of Martin Brothers Trucking Company, which had been crossing the union's picket lines. Suares maintained his innocence, and the UE and labor supporters characterized the case as ATF entrapment — saying the agent had agitated for violence and that the men refused.

He was convicted in the U.S. District Court for the District of Massachusetts of three counts of possessing and three counts of transferring unregistered destructive devices under the National Firearms Act (26 U.S.C. §§ 5861(d) and 5861(e)) — a related conspiracy count was dismissed after trial — and sentenced to one year in prison. The First Circuit affirmed the convictions on December 16, 1977 (United States v. Suares, 567 F.2d 523, No. 77-1210). The two men received broad labor support.
TXT;

    private const CONVICTED = 'Yes — convicted in the U.S. District Court for the District of Massachusetts (a related conspiracy count was dismissed after trial); convictions affirmed by the U.S. Court of Appeals for the First Circuit on December 16, 1977 (567 F.2d 523).';

    public function handle(): int {
        DB::transaction(function () {
            $this->upsertPrisoner(
                'Alex Markley', 'Alex', 'Markley', self::MARKLEY_BIO,
                ['Labor', 'Trade unionism'],
                ['United Electrical, Radio and Machine Workers (UE) Local 259'],
                'One count of possessing an unregistered destructive device (26 U.S.C. § 5861(d)) and four counts of transferring unregistered destructive devices (26 U.S.C. §§ 5812, 5861(e); 18 U.S.C. § 2) — homemade black-powder devices tied to the 1975 UE strike at the Worthington Compressor plant in Holyoke, Massachusetts, alleged to be intended against trucks of Martin Brothers Trucking Company; transferred to an undercover ATF agent. Indicted by a federal grand jury after a July 8, 1976 arrest.',
                '1976-07-08',
                '18 months in prison.'
            );

            $this->upsertPrisoner(
                'Antonio Suares', 'Antonio', 'Suares', self::SUARES_BIO,
                ['Labor', 'Trade unionism'],
                ['International Brotherhood of Electrical Workers (IBEW)'],
                'Three counts of possessing and three counts of transferring unregistered destructive devices (26 U.S.C. §§ 5861(d), 5861(e)) — assembled and delivered three homemade black-powder devices to an undercover ATF agent for $75 on December 8, 1975, alleged to be for use against Martin Brothers Trucking Company trucks during the UE strike in Holyoke, Massachusetts.',
                null,
                'One year in prison.'
            );
        });

        return self::SUCCESS;
    }

    private function upsertPrisoner(string $name, string $first, string $last, string $bio, array $ideologies, array $affiliation, string $charges, ?string $arrestDate, string $sentence): void {
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
                'sentence'    => $sentence,
            ]
        );

        $verb = $prisoner->wasRecentlyCreated ? 'Added' : 'Updated';
        $this->info("{$verb}: {$prisoner->name} (slug: {$prisoner->slug})");
    }
}
