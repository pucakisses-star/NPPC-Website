<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Enriches Francisco "Kiko" Martínez's record from James Barrera's NACCS paper
 * "The Political Repression of a Chicano Movement Activist" (2004). Sets his
 * birthdate (Nov 26, 1946, Alamosa, CO), flags his roughly seven-year exile in
 * Mexico (1973 – September 3, 1980), and fills the full case timeline — the
 * January 15, 1973 Scottsbluff arrest (acquitted), the October 1973 Denver
 * "package bomb" indictment, the September 3, 1980 capture at Nogales under an
 * alias, the 1981 "Winnergate" hidden-camera mistrial, the dismissals/acquittals
 * clearing the bombing charges (1981–83), and the 1986 border-alias conviction
 * later overturned by the Ninth Circuit (1988) — so he was ultimately cleared of
 * everything. Upsert/idempotent.
 */
final class UpdateKikoMartinez extends Command
{
    protected $signature = 'prisoners:update-kiko-martinez';

    protected $description = 'Update Francisco "Kiko" Martínez from the Barrera NACCS paper (full bio, exile, case timeline)';

    public function handle(): int
    {
        $prisoner = Prisoner::withUnderReview()
            ->where('name', 'like', '%Kiko%')
            ->where('name', 'like', '%Mart%nez%')
            ->first();

        if (! $prisoner) {
            $this->warn('Kiko Martínez record not found, skipping.');

            return self::SUCCESS;
        }

        $description = 'Francisco Eugenio "Kiko" Martínez (born November 26, 1946 in Alamosa, Colorado) was a '
            .'Chicano-movement attorney from southern Colorado who became one of the era\'s most relentlessly '
            .'prosecuted "political targets." The son of a migrant-working family, he graduated from Alamosa High '
            .'School (1964) and Adams State College (1968), joined Rodolfo "Corky" González\'s Crusade for Justice in '
            .'1966, and earned his law degree from the University of Minnesota in 1971. Admitted to the bar after '
            .'publicly refusing to answer a Colorado bar-exam question he condemned as demeaning to Native Americans — '
            .'protesting alongside American Indian Movement activists outside the Colorado Supreme Court — he built a '
            .'practice for those who could not afford one: Chicano students, migrant farmworkers, and prison inmates. '
            .'He organized the Latin American Development Society inside the Colorado penitentiary, challenged the '
            .'abuse of Chicano prisoners, and in 1972 worked through MALDEF and the La Raza Legal Association on the '
            .'Ricardo Falcón murder case — activism that drew intensifying law-enforcement and reported COINTELPRO '
            ."scrutiny.\n\n"
            .'Martínez\'s persecution unfolded amid Denver\'s early-1970s "bomb hysteria" aimed at the Crusade for '
            .'Justice — a period in which his own 25-year-old brother, Reyes Martínez, was among the six Chicano '
            .'activists killed in the May 1974 "Los Seis de Boulder" car bombings. He was arrested in Scottsbluff, '
            .'Nebraska on January 15, 1973 for an alleged Molotov cocktail (acquitted after the search was ruled '
            .'unconstitutional), then indicted in Denver in October 1973 for allegedly mailing three package bombs — '
            .'to Black policewoman Carol Hogue, a school board member, and a motorcycle shop — charges his supporters '
            .'and later court findings treated as a frame-up. His law license was suspended and a reward posted; '
            .'fearing he would be shot "on sight," he exiled himself to Mexico for seven years. He was captured '
            .'re-entering at Nogales, Arizona on September 3, 1980 under the alias José Reynoso Díaz, and — after U.S. '
            .'District Judge Fred M. Winner set a $1 million bond, later cut to $400,000 — was released that October '
            ."when his family and friends pledged sixteen homes.\n\n"
            .'His first federal trial (Pueblo, January 1981) collapsed into a mistrial after it emerged that Judge '
            .'Winner had secretly arranged with the FBI to install a hidden courtroom camera and had met privately '
            .'with prosecutors — a scandal dubbed "Winnergate." Winner recused himself; the Hogue charge and all '
            .'Colorado state charges were dismissed in 1981, a federal appeals panel found he had acted improperly, '
            .'Martínez was acquitted of another bombing count in November 1982, and the last federal charge was '
            .'dismissed on August 15, 1983. Weeks later the FBI re-arrested him over the 1980 border alias; in 1986 '
            .'he was ordered to serve 90 days for it, but the Ninth Circuit overturned that conviction in 1988 — '
            .'leaving him cleared of every charge after a fifteen-year ordeal. He resumed practicing law, and later '
            .'reflected that it had been "a heck of a good education about the legal process and about political '
            .'repression in America."';

        DB::transaction(function () use ($prisoner, $description) {
            $prisoner->birthdate = '1946-11-26';
            $prisoner->description = $description;
            $prisoner->in_exile = true;
            $prisoner->currently_in_exile = false;
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->charges = 'Arrested January 15, 1973 in Scottsbluff, Nebraska for an alleged explosive device '
                .'(a "Molotov cocktail"), then indicted in Denver in October 1973 for allegedly mailing three package '
                .'bombs — to Denver policewoman Carol Hogue, school board member Robert Crider, and the Two Wheeler '
                .'Motorcycle Shop — charges widely held to be a political frame-up amid the repression of the Crusade '
                .'for Justice. After returning from exile he was also charged with using a false name (José Reynoso '
                .'Díaz) when crossing the border in 1980.';
            $case->convicted = 'No — ultimately cleared of every charge. The Scottsbluff arrest ended in acquittal '
                .'(unconstitutional search). The Denver bombing prosecutions collapsed: his first trial was declared a '
                .'mistrial in January 1981 after Judge Fred M. Winner was found to have secretly had the FBI install a '
                .'hidden courtroom camera and met privately with prosecutors ("Winnergate"); the Hogue and Colorado '
                .'state charges were dismissed in 1981, he was acquitted of another count in November 1982, and the '
                .'final federal charge was dismissed on August 15, 1983. A 1986 conviction for using a false name at '
                .'the border (90 days) was overturned by the Ninth Circuit in 1988.';
            $case->arrest_date = '1973-01-15';
            $case->in_exile_since = '1973-10-01';
            $case->end_of_exile = '1980-09-03';
            $case->sentence = 'Self-exiled to Mexico in 1973 for about seven years; captured re-entering the U.S. at '
                .'Nogales, Arizona on September 3, 1980 and released on a $400,000 bond on October 24, 1980. The only '
                .'conviction he ever received — 90 days for using a false name at the border (1986) — was overturned '
                .'by the Ninth Circuit in 1988, after which he resumed the practice of law.';
            $case->save();
        });

        $case = $prisoner->cases()->first();
        $this->info("Updated {$prisoner->name}: full bio set, exile flagged (in_exile_for_days={$case->in_exile_for_days}).");

        return self::SUCCESS;
    }
}
