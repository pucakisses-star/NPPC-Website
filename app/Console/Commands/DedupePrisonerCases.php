<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Collapses duplicated prisoner cases, which inflate the public
 * "Imprisoned for..." counter because the profile page sums
 * imprisoned_for_days across every case on a record.
 *
 * Runs in tiers so the safe work can be applied without gambling on the
 * judgement calls:
 *
 *   exact   (default) — cases with the SAME incarceration date, the SAME
 *                       release date and compatible charges text. These are
 *                       unambiguous copies (e.g. Ralph DiGia carries six
 *                       identical 1942-1945 draft-refusal cases).
 *   sparse            — a case with no dates folded into a dated case whose
 *                       charges it matches. Does not change the counter (an
 *                       undated case contributes null), but removes the
 *                       duplicate card from the page.
 *   overlap           — cases whose custody ranges overlap by 90% or more
 *                       with compatible charges, but whose dates disagree
 *                       slightly (e.g. 1997-02-11 vs 1997-02-14). One set of
 *                       dates is wrong; this keeps the better-populated case.
 *
 * Pairs whose charges describe materially different offences are never
 * touched — they are reported for manual review, because sharing a date does
 * not make two prosecutions the same case.
 *
 * Merging folds any field the survivor is missing across from the copy first,
 * so no recorded detail is lost. Dry-run unless --apply is passed.
 */
final class DedupePrisonerCases extends Command
{
    protected $signature = 'prisoners:dedupe-cases
        {--mode=exact : exact|sparse|overlap|all}
        {--apply : Write the changes (otherwise dry run)}
        {--slug= : Limit to one prisoner}';

    protected $description = 'Collapse duplicated prisoner cases that inflate the imprisonment counter';

    /** Case columns considered when scoring completeness and folding fields. */
    private const FIELDS = [
        'charges', 'arrest_date', 'indicted', 'sentenced_date', 'incarceration_date',
        'release_date', 'death_in_custody_date', 'in_exile_since', 'end_of_exile',
        'convicted', 'plead', 'prosecutor', 'judge', 'sentence', 'institution_id',
    ];

    public function handle(): int
    {
        $mode = (string) $this->option('mode');
        if (! in_array($mode, ['exact', 'sparse', 'overlap', 'all'], true)) {
            $this->error('--mode must be exact, sparse, overlap or all');

            return self::FAILURE;
        }
        $apply = (bool) $this->option('apply');
        $modes = $mode === 'all' ? ['exact', 'sparse', 'overlap'] : [$mode];

        $query = Prisoner::withoutGlobalScopes()->with('cases');
        if ($slug = $this->option('slug')) {
            $query->where('slug', $slug);
        }

        $merged = 0;
        $review = [];

        foreach ($query->get() as $prisoner) {
            $cases = $prisoner->cases->values();
            if ($cases->count() < 2) {
                continue;
            }

            $before = $cases->sum('imprisoned_for_days');
            $lines = [];

            foreach ($modes as $activeMode) {
                // Re-read after each pass so merges compound.
                $cases = $prisoner->cases()->get()->values();

                foreach ($this->pairs($cases) as [$a, $b]) {
                    if (! $a->exists || ! $b->exists) {
                        continue;   // already merged away in this pass
                    }
                    $verdict = $this->classify($a, $b);
                    if ($verdict !== $activeMode) {
                        continue;
                    }

                    [$keep, $dropCase] = $this->score($a) >= $this->score($b) ? [$a, $b] : [$b, $a];
                    $lines[] = sprintf(
                        '  %s: keep %s (%d fields, days=%s), drop %s (%d fields, days=%s)',
                        $activeMode,
                        substr($keep->id, 0, 8), $this->score($keep), $keep->imprisoned_for_days ?? 'null',
                        substr($dropCase->id, 0, 8), $this->score($dropCase), $dropCase->imprisoned_for_days ?? 'null',
                    );

                    if ($apply) {
                        DB::transaction(function () use ($keep, $dropCase) {
                            foreach (self::FIELDS as $field) {
                                if (empty($keep->{$field}) && ! empty($dropCase->{$field})) {
                                    $keep->{$field} = $dropCase->{$field};
                                }
                            }
                            $keep->save();
                            $dropCase->delete();
                        });
                    }
                    $merged++;
                }
            }

            // Anything still overlapping but not classified is a judgement call.
            foreach ($this->pairs($prisoner->cases()->get()->values()) as [$a, $b]) {
                if ($this->classify($a, $b) === 'review') {
                    $review[$prisoner->slug][] = sprintf(
                        '    %s days=%-6s | %s',
                        substr($a->id, 0, 8), $a->imprisoned_for_days ?? 'null', substr((string) $a->charges, 0, 46),
                    ).PHP_EOL.sprintf(
                        '    %s days=%-6s | %s',
                        substr($b->id, 0, 8), $b->imprisoned_for_days ?? 'null', substr((string) $b->charges, 0, 46),
                    );
                }
            }

            if ($lines) {
                $after = $prisoner->cases()->get()->sum('imprisoned_for_days');
                $this->line("\n{$prisoner->name}  [{$prisoner->slug}]  days {$before} -> {$after}");
                foreach ($lines as $line) {
                    $this->line($line);
                }
            }
        }

        if ($review) {
            $this->newLine();
            $this->warn('Left alone — same dates but different offences, needs a human decision ('.count($review).' prisoners):');
            foreach ($review as $slug => $blocks) {
                $this->line("  {$slug}");
                foreach (array_unique($blocks) as $block) {
                    $this->line($block);
                }
            }
        }

        if ($apply) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        $this->newLine();
        $this->info(($apply ? 'Merged ' : 'DRY RUN — would merge ').$merged.' case(s) in mode "'.$mode.'".');
        if (! $apply) {
            $this->warn('Nothing written. Re-run with --apply to commit.');
        }

        return self::SUCCESS;
    }

    /** @return array<int, array{0: PrisonerCase, 1: PrisonerCase}> */
    private function pairs($cases): array
    {
        $out = [];
        for ($i = 0; $i < $cases->count(); $i++) {
            for ($j = $i + 1; $j < $cases->count(); $j++) {
                $out[] = [$cases[$i], $cases[$j]];
            }
        }

        return $out;
    }

    private function score(PrisonerCase $case): int
    {
        $n = 0;
        foreach (self::FIELDS as $field) {
            if (! empty($case->{$field})) {
                $n++;
            }
        }
        // Longer narrative text breaks ties in favour of the richer record.
        return $n * 100 + min(99, (int) (strlen((string) $case->sentence) / 20));
    }

    /** exact | sparse | overlap | review | none */
    private function classify(PrisonerCase $a, PrisonerCase $b): string
    {
        $compatible = $this->chargesCompatible($a->charges, $b->charges);

        $sameDay = fn ($x, $y) => $x && $y && Carbon::parse($x)->isSameDay(Carbon::parse($y));

        if ($sameDay($a->incarceration_date, $b->incarceration_date)
            && $sameDay($a->release_date, $b->release_date)) {
            return $compatible ? 'exact' : 'review';
        }

        // One side undated: it contributes no days, so folding it in is safe
        // whenever the charges line up.
        $aDated = (bool) ($a->incarceration_date || $a->release_date);
        $bDated = (bool) ($b->incarceration_date || $b->release_date);
        if ($aDated !== $bDated || ! $a->incarceration_date || ! $b->incarceration_date) {
            if (! $a->incarceration_date || ! $b->incarceration_date) {
                return $compatible ? 'sparse' : 'review';
            }
        }

        $overlap = $this->overlapRatio($a, $b);
        if ($overlap >= 0.9) {
            return $compatible ? 'overlap' : 'review';
        }

        return $overlap > 0 ? 'review' : 'none';
    }

    /**
     * Minimum length before containment counts as a match. A bare "Murder"
     * must not be treated as the same charge as "Murder of NYPD Officers
     * Gregory Foster and Rocco Laurie" — those are two different prosecutions
     * on the same record.
     */
    private const MIN_CONTAINMENT_LENGTH = 20;

    /**
     * Charges are "compatible" when they clearly describe the same
     * prosecution: identical after normalisation, one side blank, or one
     * sufficiently specific text contained in the other. Deliberately
     * conservative — anything else is sent to manual review rather than
     * merged.
     */
    private function chargesCompatible(?string $a, ?string $b): bool
    {
        $norm = fn (?string $s) => preg_replace('/\s+/', ' ', trim(preg_replace('/[^a-z0-9 ]/', ' ', strtolower((string) $s))));
        $a = $norm($a);
        $b = $norm($b);

        if ($a === '' || $b === '') {
            return true;
        }
        if ($a === $b) {
            return true;
        }

        $shorter = strlen($a) <= strlen($b) ? $a : $b;
        if (strlen($shorter) < self::MIN_CONTAINMENT_LENGTH) {
            return false;
        }

        return str_contains($a, $b) || str_contains($b, $a);
    }

    private function overlapRatio(PrisonerCase $a, PrisonerCase $b): float
    {
        $range = function (PrisonerCase $c): ?array {
            $start = $c->incarceration_date ?: $c->arrest_date;
            if (! $start) {
                return null;
            }
            $end = $c->release_date ?: $start;

            return [Carbon::parse($start), Carbon::parse($end)];
        };

        $ra = $range($a);
        $rb = $range($b);
        if (! $ra || ! $rb) {
            return 0.0;
        }

        $start = $ra[0]->max($rb[0]);
        $end = $ra[1]->min($rb[1]);
        if ($start->gt($end)) {
            return 0.0;
        }

        $shared = $start->diffInDays($end) + 1;
        $shortest = min($ra[0]->diffInDays($ra[1]) + 1, $rb[0]->diffInDays($rb[1]) + 1);

        return $shortest > 0 ? $shared / $shortest : 0.0;
    }
}
