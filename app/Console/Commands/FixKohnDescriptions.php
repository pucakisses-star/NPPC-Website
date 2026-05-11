<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Cleans up the description text on every 1910s Kohn prisoner:
 *
 * 1. Prepends the prisoner's name if the description starts with a
 *    fragment (e.g. "was arrested in ..." → "George Adams was arrested
 *    in ..."). The original parser stripped the leading "Name, " from
 *    Kohn's prose, so most rows need the name re-added.
 *
 * 2. Removes the trailing source-attribution sentence
 *    "Sourced from Stephen M. Kohn, ..." that the importer appended.
 */
final class FixKohnDescriptions extends Command {
    protected $signature = 'archive:fix-kohn-descriptions {--dry : preview only, do not write} {--sample=3 : show N before/after samples}';
    protected $description = 'Prepend prisoner name and strip the Kohn attribution from 1910s prisoner descriptions';

    private const ERA = '1910s';

    public function handle(): int {
        $dry = (bool) $this->option('dry');
        $sampleN = max(0, (int) $this->option('sample'));

        $query = Prisoner::query()->where('era', self::ERA);
        $total = (clone $query)->count();
        $this->info("Inspecting {$total} 1910s prisoners…");

        $changed = 0;
        $unchanged = 0;
        $samples = [];

        $query->chunk(100, function ($batch) use (&$changed, &$unchanged, &$samples, $sampleN, $dry) {
            foreach ($batch as $prisoner) {
                $orig = (string) $prisoner->description;
                $clean = $this->cleanDescription((string) $prisoner->name, $orig);

                if ($clean === $orig) {
                    $unchanged++;

                    continue;
                }

                if (count($samples) < $sampleN) {
                    $samples[] = [$prisoner->name, $orig, $clean];
                }

                if (! $dry) {
                    $prisoner->description = $clean;
                    $prisoner->save();
                }
                $changed++;
            }
        });

        $verb = $dry ? 'would update' : 'updated';
        $this->info("{$verb}: {$changed}");
        $this->info("already clean: {$unchanged}");

        foreach ($samples as [$name, $before, $after]) {
            $this->line("\n--- {$name} ---");
            $this->line('BEFORE: '.mb_substr($before, 0, 220).(mb_strlen($before) > 220 ? '…' : ''));
            $this->line('AFTER:  '.mb_substr($after, 0, 220).(mb_strlen($after) > 220 ? '…' : ''));
        }

        return self::SUCCESS;
    }

    /**
     * Public so it can be unit-tested in isolation.
     */
    public function cleanDescription(string $name, string $text): string {
        $out = $text;

        // 1. Strip the trailing source-attribution sentence (any spacing/punct
        //    variations). It's always at the end and always contains the
        //    "Stephen M. Kohn" phrase + "Praeger, 1994", so anchor on those
        //    with a permissive .*? between them.
        $out = preg_replace(
            '/\s*(?:Sourced from )?Stephen M\.\s+Kohn,?.*?Praeger,?\s*1994\)?\.?\s*$/isu',
            '',
            $out
        );
        $out = rtrim($out);

        // 2. Prepend the prisoner's name if the description doesn't already
        //    open with it. The original parser dropped "Name, " from the head
        //    of each paragraph, so most rows now start with "was arrested..."
        //    or "an anarchist from..." etc.
        $nameTrimmed = trim($name);
        if ($nameTrimmed !== '') {
            $startsWithName = preg_match(
                '/^\s*'.preg_quote($nameTrimmed, '/').'\b/i',
                $out
            );

            if (! $startsWithName) {
                // If the remainder begins with a noun-phrase opener
                // ("a", "an", "the", "from", "of", "who", "which",
                // "secretary", etc.), then the original Kohn sentence had
                // "Name, an anarchist from..." so we add a comma after the
                // name. Otherwise (verb-led: "was arrested...", "served..."),
                // a space is correct.
                $needsComma = preg_match(
                    '/^\s*(a|an|the|from|of|who|whose|which|secretary|president|editor|originally|a former)\b/i',
                    $out
                );
                $sep = $needsComma ? ', ' : ' ';
                $out = $nameTrimmed.$sep.ltrim($out);
            }
        }

        // 3. Tidy any double-space introduced by the strip.
        $out = preg_replace('/\s{2,}/', ' ', $out);

        return trim($out);
    }
}
