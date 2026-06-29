<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds the two San Francisco AIDS activists Michael Petrelis and David
 * Pasquarelli, arrested together in November 2001 and charged with conspiracy,
 * stalking, and terrorist threats over a campaign of harassing phone calls to
 * San Francisco Chronicle reporters and public-health officials — a case whose
 * unusually high bail and felony terrorism charges for activist speech drew
 * free-speech concern and supporters who called them political prisoners.
 * Idempotent: prisoner:add refuses duplicates.
 */
final class AddPetrelisPasquarelli extends Command
{
    protected $signature = 'prisoner:add-petrelis-pasquarelli';

    protected $description = 'Add Michael Petrelis and David Pasquarelli (SF ACT UP / 2001 case)';

    public function handle(): int
    {
        $payloads = [
            [
                'name' => 'Michael Petrelis',
                'first_name' => 'Michael',
                'last_name' => 'Petrelis',
                'description' => "Michael Petrelis (born January 26, 1959) is a longtime AIDS and LGBTQ-rights activist known for confrontational direct action. After being diagnosed with AIDS in 1985, he helped found the Lavender Hill Mob (1986) and ACT UP New York (1987), worked to launch ACT UP chapters in several cities, took part in the 1989 'Stop the Church' protest, and later ran accountability and political-transparency campaigns in Washington, D.C. and San Francisco, where he founded the AIDS Accountability Project in 1998. In November 2001 Petrelis and David Pasquarelli were arrested in San Francisco and charged with criminal conspiracy, stalking, and making terrorist threats over a campaign of late-night phone calls to San Francisco Chronicle reporters and public-health officials — calls the activists said were angry protests against the paper's and the health department's coverage of HIV and of rising STD rates among gay men. The activists admitted making the calls but denied making threats. Petrelis was jailed on \$500,000 bail; the unusually high bail and the felony terrorism charges for what supporters characterized as obnoxious but protected political speech drew free-speech concern and divided civil-liberties advocates. In 2003 he pleaded no contest to misdemeanor charges of making threatening phone calls and received a one-year jail sentence, suspended in favor of three years' probation, anger-management classes, stay-away orders, and written apologies. He remains a San Francisco-based activist and blogger.",
                'birthdate' => '1959-01-26',
                'state' => 'California',
                'gender' => 'Male',
                'ideologies' => ['AIDS activism', 'LGBTQ rights', 'Free speech'],
                'affiliation' => ['ACT UP', 'Lavender Hill Mob', 'AIDS Accountability Project'],
                'era' => '2000s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'San Francisco County Jail',
                        'institution_city' => 'San Francisco',
                        'institution_state' => 'California',
                        'charges' => 'Criminal conspiracy, stalking, and making terrorist threats (felonies, later reduced) over a campaign of late-night phone calls to San Francisco Chronicle reporters and public-health officials. Arrested November 28, 2001; held on \$500,000 bail.',
                        'arrest_date' => '2001-11-28',
                        'convicted' => 'Pleaded no contest (2003) to misdemeanor making threatening phone calls',
                        'sentence' => 'One year in jail, suspended; three years probation, anger-management classes, stay-away orders, and written apologies.',
                    ],
                ],
            ],
            [
                'name' => 'David Pasquarelli',
                'first_name' => 'David',
                'last_name' => 'Pasquarelli',
                'description' => "David Pasquarelli (1967–2004) was a San Francisco AIDS activist and a leader of ACT UP/San Francisco, a dissident faction that controversially disputed the link between HIV and AIDS. He co-founded ACT UP/Tampa Bay before moving to San Francisco in 1993 and being diagnosed with HIV in 1995. In November 2001 Pasquarelli and Michael Petrelis were arrested and charged with criminal conspiracy, stalking, and making terrorist threats over a campaign of late-night phone calls to San Francisco Chronicle reporters and public-health officials, whose coverage of HIV and of rising STD rates among gay men the activists were protesting. Pasquarelli was held on \$600,000 bail; the unusually high bail and felony terrorism charges for activist phone harassment drew free-speech concern and divided advocates, and supporters publicly described him as a political prisoner. In 2003 he pleaded no contest to misdemeanor charges of making threatening phone calls and received a one-year jail sentence, suspended in favor of three years' probation, anger management, stay-away orders, and written apologies. He died on March 8, 2004, at age 36, of complications from AIDS.",
                'death_date' => '2004-03-08',
                'state' => 'California',
                'gender' => 'Male',
                'ideologies' => ['AIDS activism', 'LGBTQ rights'],
                'affiliation' => ['ACT UP/San Francisco', 'ACT UP/Tampa Bay'],
                'era' => '2000s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'San Francisco County Jail',
                        'institution_city' => 'San Francisco',
                        'institution_state' => 'California',
                        'charges' => 'Criminal conspiracy, stalking, and making terrorist threats (felonies, later reduced) over a campaign of late-night phone calls to San Francisco Chronicle reporters and public-health officials. Arrested November 28, 2001; held on \$600,000 bail.',
                        'arrest_date' => '2001-11-28',
                        'convicted' => 'Pleaded no contest (2003) to misdemeanor making threatening phone calls',
                        'sentence' => 'One year in jail, suspended; three years probation, anger management, stay-away orders, and written apologies.',
                    ],
                ],
            ],
        ];

        foreach ($payloads as $payload) {
            $this->call('prisoner:add', ['json' => json_encode($payload)]);
        }

        return self::SUCCESS;
    }
}
