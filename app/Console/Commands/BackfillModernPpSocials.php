<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills verified individual support-campaign websites and
 * socials onto modern (2010s/2020s) political prisoners. Each
 * field is set only if currently empty; --force overwrites.
 *
 * Sources verified by research agent (May 2026) against the live
 * campaign sites; deliberately skips umbrella sites (Atlanta
 * Solidarity Fund, Prairieland Defendants, Anarchist Federation,
 * Samidoun, Peaceful Uprising, etc.) for prisoners who don't have
 * a dedicated single-defendant page.
 */
final class BackfillModernPpSocials extends Command {
    protected $signature = 'archive:backfill-modern-pp-socials {--force : Overwrite existing values}';
    protected $description = 'Backfill verified individual support-campaign socials onto modern PPs';

    public function handle(): int {
        $force = (bool) $this->option('force');

        $updates = [
            'elias-rodriguez' => [
                'website' => 'https://unityoffields.net/free-elias-rodriguez/',
                'twitter' => 'https://x.com/unityoffields',
            ],
            'zoe-rosenberg' => [
                'website' => 'https://www.freezoe.org/',
                'instagram' => 'https://www.instagram.com/zoe_rooster/',
                'facebook' => 'https://www.facebook.com/zoe.rooster/',
            ],
            'tim-dechristopher' => [
                'website' => 'https://www.peacefuluprising.org/tim-dechristopher',
            ],
            'lauren-handy' => [
                'website' => 'https://paaunow.org/support-lauren',
            ],
            'wayne-hsiung' => [
                'website' => 'https://www.waynehsiung.com/',
                'facebook' => 'https://www.facebook.com/wayne.hsiung/',
            ],
            'megan-rice' => [
                'website' => 'https://transformnowplowshares.wordpress.com/',
            ],
            'michael-walli' => [
                'website' => 'https://transformnowplowshares.wordpress.com/',
            ],
        ];

        $hit = 0;
        $miss = 0;
        $changed = 0;
        foreach ($updates as $slug => $fields) {
            $p = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $p) {
                $this->warn(str_pad('MISS', 10).$slug);
                $miss++;

                continue;
            }
            $hit++;
            $dirty = false;
            foreach ($fields as $key => $value) {
                if ($force || empty($p->{$key})) {
                    $old = $p->{$key};
                    $p->{$key} = $value;
                    $dirty = true;
                    $this->info(str_pad('SET', 10).$slug.'  '.$key.' = '.$value.($old ? '  (was: '.$old.')' : ''));
                } else {
                    $this->line(str_pad('SKIP', 10).$slug.'  '.$key.' already set: '.$p->{$key});
                }
            }
            if ($dirty) {
                $p->save();
                $changed++;
            }
        }

        $this->info("\nMatched: {$hit}    Missing: {$miss}    Changed: {$changed}");

        return self::SUCCESS;
    }
}
