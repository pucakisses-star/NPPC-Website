<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * Adds Ian Freeman (born Ian Bernard) — libertarian talk-radio host (Free Talk
 * Live) and Free State Project / Free Keene activist serving an eight-year
 * federal sentence for running an unlicensed bitcoin business, one of the New
 * Hampshire "Crypto Six." Idempotent: prisoner:add refuses duplicates.
 */
final class AddIanFreeman extends Command
{
    protected $signature = 'prisoner:add-ian-freeman';

    protected $description = 'Add Ian Freeman (Free Talk Live host, Crypto Six bitcoin case) as a prisoner';

    public function handle(): int
    {
        $description = 'Ian Freeman (born Ian Bernard) is a libertarian talk-radio host and activist serving an eight-year federal sentence for running an unlicensed bitcoin business. Co-founder and host of the nationally syndicated program Free Talk Live and a prominent figure in New Hampshire\'s Free State Project and the Free Keene movement — known for "Robin Hooding," feeding strangers\' expired parking meters as a protest — Freeman was arrested on March 16, 2021 as one of a group of New Hampshire crypto activists dubbed the "Crypto Six." Federal prosecutors charged him with operating an unlicensed money-transmitting business (selling bitcoin through kiosks and the LocalBitcoins platform without registering with FinCEN), conspiracy, money laundering, and tax evasion, alleging he moved more than $10 million — some of it proceeds of romance scams routed through cash donations to Keene-area churches. He was convicted at a jury trial in the U.S. District Court for the District of New Hampshire on December 22, 2022, and sentenced on October 2, 2023 to 96 months (eight years) in federal prison, two years of supervised release, and a $40,000 fine; in February 2024 he was ordered to pay $3,502,708.69 in restitution to 29 victims. A federal appeals court has upheld his conviction. Freeman and his supporters frame the prosecution as the targeting of a cryptocurrency and libertarian activist over a victimless, regulatory offense.';

        $payload = [
            'name' => 'Ian Freeman',
            'first_name' => 'Ian',
            'last_name' => 'Freeman',
            'aka' => 'Ian Bernard',
            'description' => $description,
            'state' => 'New Hampshire',
            'gender' => 'Male',
            'ideologies' => ['Libertarianism', 'Voluntaryism', 'Cryptocurrency freedom'],
            'affiliation' => ['Free Talk Live', 'Free State Project', 'Free Keene'],
            'era' => 'Contemporary',
            'in_custody' => true,
            'released' => false,
            'cases' => [
                [
                    'charges' => 'Conspiracy to operate an unlicensed money-transmitting business; operating an unlicensed money-transmitting business; conspiracy to commit money laundering; money laundering; and tax evasion. Arrested March 16, 2021 as one of the New Hampshire "Crypto Six."',
                    'arrest_date' => '2021-03-16',
                    'convicted' => 'Convicted at jury trial, December 22, 2022 (U.S. District Court, District of New Hampshire)',
                    'sentence' => '96 months (8 years) in federal prison, 2 years of supervised release, and a $40,000 fine (sentenced October 2, 2023). Ordered in February 2024 to pay $3,502,708.69 in restitution to 29 victims. Conviction upheld on appeal.',
                ],
            ],
        ];

        $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);

        return $exit === 0 ? self::SUCCESS : self::FAILURE;
    }
}
