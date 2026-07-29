<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Reduce the case `convicted` field to a verdict.
 *
 * The field had turned into a second sentence field: 6,187 cases carried a
 * value, the median 36 characters and the longest 645, so profiles printed
 * paragraphs under the word CONVICTED. It should answer one question — was
 * this person convicted — and leave the story to `sentence`.
 *
 * THE CANONICAL SET is deliberately tiny:
 *
 *   Yes
 *   No
 *   No — acquitted
 *   No — pending trial
 *   No — charges dismissed
 *   Unknown
 *
 * NOTHING IS THROWN AWAY. Before a value is shortened, the ORIGINAL STRING is
 * checked against the case's sentence and charges and the prisoner's
 * description. If it is not already represented there, the original is
 * appended verbatim to `sentence` as "Verdict as recorded: ...". Roughly half
 * the values carry detail found nowhere else, so truncating without this step
 * would destroy real information. The original is preserved whole rather than
 * a parsed fragment, so the preservation cannot itself be a parsing bug.
 *
 * AMBIGUOUS VALUES ARE LEFT ALONE, and there are about 1,300 of them: bare
 * "Indicted for sedition, 1930", "Held in the Harlan coal-war roundup, 1931",
 * "Imprisoned in the 1917-1919 National Woman's Party suffrage campaign",
 * stray dates like "Oct 6th 2022", "Data not available". None of these states
 * a verdict, and guessing one would put a fact on the record that no source
 * supports. They are reported so they can be settled by hand.
 *
 * TWO ORDERING RULES MATTER, and both were found by testing against live data:
 *
 *   1. "not guilty" is tested BEFORE the guilty/pleaded rules. Without that,
 *      "pleaded not guilty on May 21, 2026; awaiting trial" matches ^pleaded
 *      and is recorded as a CONVICTION — the exact opposite of the truth. It
 *      affected real records.
 *   2. Mixed verdicts stay "Yes". "Yes on resisting; disarming dismissed" is a
 *      conviction; the acquitted counts are nuance for the sentence text, not
 *      grounds for calling the case a non-conviction.
 *
 * Verified against the live table before shipping: no value beginning with a
 * negative word classifies as Yes, and none beginning with an affirmative one
 * classifies as No.
 *
 * Idempotent: once a value equals its canonical form there is nothing to do,
 * so the preservation step cannot append twice.
 *
 *   php artisan prisoners:normalize-verdicts
 *   php artisan prisoners:normalize-verdicts --apply
 */
final class NormalizeVerdicts extends Command
{
    protected $signature = 'prisoners:normalize-verdicts
        {--apply : Write the shortened verdicts (default is a dry run)}
        {--show-skipped=25 : How many ambiguous values to list}';

    protected $description = 'Reduce case convicted fields to Yes/No, preserving any detail found nowhere else';

    /** Canonical verdict for a raw value, or null to leave it untouched. */
    private function classify(string $value): ?string
    {
        $s = trim($value);
        $low = mb_strtolower($s);

        if (preg_match('/^(no|not convicted|not yet|never (tried|charged|convicted|prosecuted)|acquitted|not guilty|ngri\b)/u', $low)) {
            if (preg_match('/acquitt|not guilty|ngri/u', $low)) {
                return 'No — acquitted';
            }
            if (preg_match('/pending|await|pretrial|pre-trial|trial set/u', $low)) {
                return 'No — pending trial';
            }
            if (preg_match('/dismiss|dropped|nolle|declined/u', $low)) {
                return 'No — charges dismissed';
            }

            return 'No';
        }

        // Must precede the guilty/pleaded rules — see the class comment.
        if (preg_match('/\b(not guilty|pleaded not guilty|pled not guilty)\b/u', $low)
            && ! preg_match('/\bfound guilty|later convicted|then convicted/u', $low)) {
            return preg_match('/pending|await|pretrial|pre-trial|trial set/u', $low)
                ? 'No — pending trial'
                : 'No — acquitted';
        }

        if (preg_match('/^(pending|pretrial|pre-trial|awaiting)/u', $low)) {
            return 'No — pending trial';
        }
        if (preg_match('/^(all )?charges? (were )?(dismissed|dropped)|^dismissed|^nolle|^case dropped/u', $low)) {
            return 'No — charges dismissed';
        }
        if (preg_match('/^faced .*\(no criminal conviction/u', $low)) {
            return 'No';
        }
        if (preg_match('/^n\/a\b/u', $low)) {
            return 'No';
        }
        if (preg_match('/^held for deportation/u', $low)) {
            return 'No';
        }
        if (preg_match('/^indicted\b/u', $low) && preg_match('/await|pending|trial set/u', $low)) {
            return 'No — pending trial';
        }
        if (preg_match('/^unknown\b/u', $low)) {
            return 'Unknown';
        }
        if (preg_match('/^(yes|convicted|guilty|pled|pleaded|court-martial)/u', $low)) {
            return 'Yes';
        }

        return null;
    }

    private function norm(?string $t): string
    {
        return trim(preg_replace('/[^a-z0-9]+/u', ' ', mb_strtolower((string) $t)));
    }

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $showSkipped = (int) $this->option('show-skipped');

        $counts = [];
        $rewritten = 0;
        $preserved = 0;
        $alreadyFine = 0;
        $skipped = [];

        foreach (Prisoner::withoutGlobalScopes()->with('cases')->cursor() as $p) {
            foreach ($p->cases as $case) {
                $raw = $case->convicted;
                if (! $raw || trim($raw) === '') {
                    continue;
                }

                $verdict = $this->classify($raw);
                if ($verdict === null) {
                    $skipped[] = [$p->slug, $raw];

                    continue;
                }

                $counts[$verdict] = ($counts[$verdict] ?? 0) + 1;

                if (trim($raw) === $verdict) {
                    $alreadyFine++;

                    continue;
                }

                // Preserve the original wherever it is not already on record.
                $haystack = $this->norm(
                    ($case->sentence ?? '').' '.($case->charges ?? '').' '.($p->description ?? '')
                );
                $probe = mb_substr($this->norm($raw), 0, 60);
                $needsKeeping = mb_strlen(trim($raw)) > 25 && $probe !== '' && ! str_contains($haystack, $probe);

                if ($rewritten < 8) {
                    $this->line('  '.str_pad($p->slug, 26).' '.mb_strimwidth($raw, 0, 62, '…').'  ->  '.$verdict
                        .($needsKeeping ? '   [detail moved to sentence]' : ''));
                }

                if ($apply) {
                    if ($needsKeeping) {
                        $case->sentence = trim(($case->sentence ? rtrim($case->sentence)."\n\n" : '')
                            .'Verdict as recorded: '.trim($raw));
                    }
                    $case->convicted = $verdict;
                    $case->save();
                }

                $rewritten++;
                $needsKeeping && $preserved++;
            }
        }

        $this->newLine();
        $this->info(($apply ? 'Rewrote ' : 'Would rewrite ')."{$rewritten} verdict(s); {$alreadyFine} were already canonical.");
        $this->info("{$preserved} carried detail found nowhere else — preserved verbatim on the sentence.");
        $this->newLine();
        ksort($counts);
        foreach ($counts as $verdict => $n) {
            $this->line('  '.str_pad((string) $n, 6, ' ', STR_PAD_LEFT).'  '.$verdict);
        }

        if ($skipped) {
            $this->newLine();
            $this->warn(count($skipped).' value(s) state no verdict and are LEFT UNTOUCHED — settle these by hand:');
            foreach (array_slice($skipped, 0, $showSkipped) as [$slug, $raw]) {
                $this->line('  '.str_pad($slug, 26).' '.mb_strimwidth($raw, 0, 76, '…'));
            }
            if (count($skipped) > $showSkipped) {
                $this->line('  ... and '.(count($skipped) - $showSkipped).' more (--show-skipped=N for more)');
            }
        }

        if ($apply) {
            Cache::forget(PrisonerApiController::cacheKey());
            $this->newLine();
            $this->info('Done.');
        } else {
            $this->newLine();
            $this->warn('Dry run — nothing written. Re-run with --apply.');
        }

        return self::SUCCESS;
    }
}
