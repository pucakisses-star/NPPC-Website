<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds crypto/financial-freedom cases in the same vein as Ian Freeman — liberty
 * and privacy activists prosecuted for operating unlicensed money-transmitting
 * businesses or building cryptocurrency privacy tools:
 *
 *   - Aria DiMezzo        (Freeman's "Crypto Six" co-defendant; NH)
 *   - Thomas Costanzo     ("Morpheus Titania"; AZ agorist, since released)
 *   - Roman Storm         (Tornado Cash dev; convicted 2025, awaiting sentencing)
 *   - Keonne Rodriguez    (Samourai Wallet co-founder; 5 yrs, 2025)
 *   - William Lonergan Hill (Samourai Wallet co-founder; 4 yrs, 2025)
 *
 * Idempotent and update-capable: prisoner:add creates a missing record, then
 * this command backfills the status flags and the case's charge/date fields
 * (prisoner:add itself won't update an existing record), so a re-run enriches
 * records added by an earlier run. Some release dates are the nominal term-end
 * (federal good time may move the actual date earlier).
 */
final class AddCryptoFreedomPrisoners extends Command
{
    protected $signature = 'prisoners:add-crypto-freedom';

    protected $description = 'Add crypto/financial-freedom prisoners (DiMezzo, Costanzo, Storm, Rodriguez, Hill)';

    public function handle(): int
    {
        $payloads = [
            [
                'name' => 'Aria DiMezzo',
                'first_name' => 'Aria',
                'last_name' => 'DiMezzo',
                'description' => "Aria DiMezzo is a transgender anarchist activist from Keene, New Hampshire, and a figure in the Free Keene / Free Talk Live libertarian community who founded the Reformed Satanic Church and, in 2020, won the Republican primary for Cheshire County sheriff. She was one of the \"Crypto Six\" — six libertarian activists arrested in the March 16, 2021 federal raid on the Free Talk Live headquarters. In September 2022 she pleaded guilty to a single count of operating an unlicensed money-transmitting business for selling Bitcoin peer-to-peer, and on April 25, 2023 she was sentenced to 18 months in federal prison, a \$5,000 fine, and one year of supervised release. Supporters viewed the prosecution as the criminalization of running a free-market currency exchange without a license.",
                'state' => 'New Hampshire',
                'gender' => 'Female',
                'ideologies' => ['Anarchism', 'Libertarianism', 'Cryptocurrency freedom'],
                'affiliation' => ['Free Keene', 'Free Talk Live', 'Crypto Six'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => "Operating an unlicensed money-transmitting business (peer-to-peer Bitcoin sales). One of the \"Crypto Six\" libertarian activists arrested in the March 2021 federal raid on the Free Talk Live headquarters in Keene, NH.",
                    'arrest_date' => '2021-03-16',
                    'incarceration_date' => '2023-06-27',
                    'release_date' => '2024-12-27',
                    'convicted' => 'Pleaded guilty to operating an unlicensed money-transmitting business',
                    'sentence' => '18 months in federal prison, 1 year supervised release, and a $5,000 fine; forfeited $14,000 and roughly 2 BTC.',
                    'judge' => 'Judge Joseph N. Laplante',
                ]],
            ],
            [
                'name' => 'Thomas Costanzo',
                'first_name' => 'Thomas',
                'last_name' => 'Costanzo',
                'aka' => 'Morpheus Titania',
                'description' => "Thomas Mario Costanzo, known online as \"Morpheus Titania,\" was an anarchist and agorist activist in Mesa, Arizona, who sold Bitcoin peer-to-peer as a matter of principle, rejecting state licensing of currency. After a federal task force raided his apartment in April 2017 — a case built on an undercover sting in which agents posed as drug dealers exchanging cash for Bitcoin — he was convicted by a jury in March 2018 of money laundering and, in August 2018, sentenced to 41 months in federal prison (with credit for roughly 16 months already served). The Ninth Circuit later affirmed his conviction. An outspoken government critic, Costanzo was championed by liberty and cryptocurrency activists as a political prisoner of the war on unlicensed money.",
                'state' => 'Arizona',
                'gender' => 'Male',
                'ideologies' => ['Anarchism', 'Agorism', 'Cryptocurrency freedom'],
                'affiliation' => ['Agorist movement'],
                'era' => '2010s',
                'in_custody' => false,
                'released' => true,
                'cases' => [[
                    'charges' => 'Money laundering tied to peer-to-peer Bitcoin sales, after an undercover federal sting in which agents posed as drug dealers exchanging cash for Bitcoin.',
                    'arrest_date' => '2017-04-20',
                    'incarceration_date' => '2017-04-20',
                    'release_date' => '2020-09-01',
                    'convicted' => 'Convicted by jury of money laundering (March 2018)',
                    'sentence' => '41 months in federal prison (with credit for ~16 months served); conviction affirmed by the Ninth Circuit.',
                ]],
            ],
            [
                'name' => 'Roman Storm',
                'first_name' => 'Roman',
                'last_name' => 'Storm',
                'description' => "Roman Storm is a Russian-American software developer from Auburn, Washington, and a co-founder of Tornado Cash, an open-source Ethereum privacy tool (\"mixer\"). Arrested on August 23, 2023, he was charged with conspiracy to operate an unlicensed money-transmitting business, conspiracy to commit money laundering, and conspiracy to violate U.S. sanctions. After a four-week trial, a jury on August 6, 2025 convicted him on the unlicensed-money-transmitting conspiracy but deadlocked on the two more serious counts, and the government has sought a retrial. Free on bail and awaiting sentencing, his case became the flagship \"is writing code a crime?\" fight for software developers, with privacy and free-software advocates arguing that publishing open-source code is protected speech.",
                'state' => 'Washington',
                'gender' => 'Male',
                'ideologies' => ['Cryptocurrency freedom', 'Financial privacy', 'Free software'],
                'affiliation' => ['Tornado Cash'],
                'era' => '2020s',
                'in_custody' => false,
                'awaiting_trial' => true,
                'cases' => [[
                    'charges' => 'Conspiracy to operate an unlicensed money-transmitting business, conspiracy to commit money laundering, and conspiracy to violate U.S. sanctions, over his development of the open-source Tornado Cash privacy tool.',
                    'arrest_date' => '2023-08-23',
                    'convicted' => 'Convicted (Aug 2025) of conspiracy to operate an unlicensed money-transmitting business; jury deadlocked on the money-laundering and sanctions counts (retrial sought). Awaiting sentencing.',
                ]],
            ],
            [
                'name' => 'Keonne Rodriguez',
                'first_name' => 'Keonne',
                'last_name' => 'Rodriguez',
                'description' => "Keonne Rodriguez is a co-founder and CEO of Samourai Wallet, a Bitcoin privacy (\"mixing\") wallet. Arrested on April 24, 2024 and charged with conspiracy to operate an unlicensed money-transmitting business and money-laundering conspiracy, he pleaded guilty in July 2025 to the unlicensed-money-transmitting count and was sentenced by U.S. District Judge Denise L. Cote on November 6, 2025 to five years in federal prison and a \$250,000 fine. Privacy and developer-freedom advocates have championed the case as the criminalization of building financial-privacy software; in December 2025 President Trump indicated he would review Rodriguez's case for a possible pardon.",
                'state' => 'Pennsylvania',
                'gender' => 'Male',
                'ideologies' => ['Financial privacy', 'Cryptocurrency freedom', 'Free software'],
                'affiliation' => ['Samourai Wallet'],
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'charges' => 'Conspiracy to operate an unlicensed money-transmitting business and money-laundering conspiracy, over his development of Samourai Wallet, a Bitcoin privacy ("mixing") wallet.',
                    'arrest_date' => '2024-04-24',
                    'incarceration_date' => '2025-11-06',
                    'convicted' => 'Pleaded guilty (July 2025) to operating an unlicensed money-transmitting business',
                    'sentence' => '5 years in federal prison and a $250,000 fine.',
                    'judge' => 'Judge Denise L. Cote',
                ]],
            ],
            [
                'name' => 'William Lonergan Hill',
                'first_name' => 'William',
                'middle_name' => 'Lonergan',
                'last_name' => 'Hill',
                'description' => "William Lonergan Hill is a co-founder and chief technology officer of Samourai Wallet, a Bitcoin privacy (\"mixing\") wallet. Arrested in Portugal on April 24, 2024 and charged with conspiracy to operate an unlicensed money-transmitting business and money-laundering conspiracy, he pleaded guilty in July 2025 to the unlicensed-money-transmitting count and was sentenced by U.S. District Judge Denise L. Cote on November 19, 2025 to four years in federal prison and a \$250,000 fine. Like his co-defendant Keonne Rodriguez, Hill was championed by privacy and developer-freedom advocates who argued the prosecution criminalized the development of financial-privacy software.",
                'gender' => 'Male',
                'ideologies' => ['Financial privacy', 'Cryptocurrency freedom', 'Free software'],
                'affiliation' => ['Samourai Wallet'],
                'era' => '2020s',
                'in_custody' => true,
                'released' => false,
                'cases' => [[
                    'charges' => 'Conspiracy to operate an unlicensed money-transmitting business and money-laundering conspiracy, over his development of Samourai Wallet, a Bitcoin privacy ("mixing") wallet.',
                    'arrest_date' => '2024-04-24',
                    'incarceration_date' => '2025-11-19',
                    'convicted' => 'Pleaded guilty (July 2025) to operating an unlicensed money-transmitting business',
                    'sentence' => '4 years in federal prison and a $250,000 fine.',
                    'judge' => 'Judge Denise L. Cote',
                ]],
            ],
        ];

        $boolFlags = ['in_custody', 'released', 'awaiting_trial', 'in_exile', 'currently_in_exile'];
        $caseFields = ['charges', 'arrest_date', 'incarceration_date', 'release_date', 'convicted', 'sentence', 'judge'];

        foreach ($payloads as $payload) {
            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            // prisoner:add won't update an existing record, so enrich here too
            // (status flags + case fields) for safe, idempotent re-runs.
            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
            if (! $prisoner) {
                continue;
            }
            foreach ($boolFlags as $flag) {
                if (array_key_exists($flag, $payload)) {
                    $prisoner->{$flag} = $payload[$flag];
                }
            }
            $prisoner->save();

            $caseData = $payload['cases'][0] ?? null;
            $case = $prisoner->cases()->first();
            if ($caseData && $case) {
                foreach ($caseFields as $f) {
                    if (! empty($caseData[$f])) {
                        $case->{$f} = $caseData[$f];
                    }
                }
                $case->save();
            }
        }

        $this->info("\nDone. 5 crypto/financial-freedom prisoners ensured (DiMezzo, Costanzo, Storm, Rodriguez, Hill).");

        return self::SUCCESS;
    }
}
