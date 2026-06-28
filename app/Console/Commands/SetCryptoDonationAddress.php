<?php

namespace App\Console\Commands;

use App\Models\SiteSetting;
use Illuminate\Console\Command;

/**
 * Sets (or clears) the cryptocurrency receiving address shown on the /donate
 * page for a given coin. Addresses are stored as SiteSettings rather than in
 * code, so real wallet addresses are never committed to the repository. The
 * donate page shows a coin only when its address is set.
 *
 * Usage:
 *   php artisan donate:set-crypto-address btc  bc1q...
 *   php artisan donate:set-crypto-address eth  0x...
 *   php artisan donate:set-crypto-address usdc 0x...
 *   php artisan donate:set-crypto-address btc  --clear
 */
final class SetCryptoDonationAddress extends Command
{
    protected $signature = 'donate:set-crypto-address {coin : btc|eth|sol|xrp|bch|ltc|doge|ada|avax|dot|usdc|usdt|dai|xmr} {address? : the receiving wallet address} {--clear : remove the stored address}';

    protected $description = 'Set or clear a crypto donation wallet address shown on the donate page';

    private const COINS = ['btc', 'eth', 'sol', 'xrp', 'bch', 'ltc', 'doge', 'ada', 'avax', 'dot', 'usdc', 'usdt', 'dai', 'xmr'];

    public function handle(): int
    {
        $coin = strtolower((string) $this->argument('coin'));
        if (! in_array($coin, self::COINS, true)) {
            $this->error('Coin must be one of: '.implode(', ', self::COINS));

            return self::FAILURE;
        }

        $key = "donate_{$coin}_address";

        if ($this->option('clear')) {
            SiteSetting::set($key, null);
            $this->info(strtoupper($coin).' donation address cleared.');

            return self::SUCCESS;
        }

        $address = trim((string) $this->argument('address'));
        if ($address === '') {
            $this->error('Provide an address, or pass --clear to remove it.');

            return self::FAILURE;
        }

        SiteSetting::set($key, $address);
        $this->info(strtoupper($coin)." donation address set to: {$address}");
        $this->info('It will now appear on /donate.');

        return self::SUCCESS;
    }
}
