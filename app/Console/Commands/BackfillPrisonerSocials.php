<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills verified Twitter / Facebook / Instagram accounts onto
 * prisoners whose support-campaign websites were already in the DB.
 * Each field is set only if currently empty — existing values are
 * preserved. Pass --force to overwrite.
 *
 * Sources verified by research agent (Nov 2025) against the live
 * support sites; deliberately skips general ABC chapters, brother
 * accounts, and unverified candidates.
 */
final class BackfillPrisonerSocials extends Command {
    protected $signature = 'archive:backfill-prisoner-socials {--force : Overwrite existing values}';
    protected $description = 'Backfill verified support-campaign Twitter/Facebook/Instagram onto prisoners with websites';

    public function handle(): int {
        $force = (bool) $this->option('force');

        $updates = [
            'cece-mcdonald' => [
                'twitter' => 'https://twitter.com/Free_CeCe',
                'facebook' => 'https://www.facebook.com/people/FreeCece-Mcdonald/100002567181562',
            ],
            'gerald-koch' => [
                'twitter' => 'https://twitter.com/jerryresists',
                'facebook' => 'https://www.facebook.com/JerryResists/',
            ],
            'cody-sutherlin' => ['twitter' => 'https://twitter.com/tinleypark5'],
            'dylan-sutherlin' => ['twitter' => 'https://twitter.com/tinleypark5'],
            'alex-stuck' => ['twitter' => 'https://twitter.com/tinleypark5'],
            'john-tucker' => ['twitter' => 'https://twitter.com/tinleypark5'],
            'alexander-contompasis' => [
                'instagram' => 'https://www.instagram.com/freealexstokesproject/',
            ],
            'priscilla-grim' => [
                'twitter' => 'https://x.com/priscillagrim',
            ],
            'jakhi-mccray' => [
                'twitter' => 'https://x.com/FreeJakhi',
                'instagram' => 'https://www.instagram.com/jakhisolidarity/',
            ],
            'yaakub-ira-vijandre' => [
                'instagram' => 'https://www.instagram.com/freeyaakub/',
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
