<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;

/**
 * Rank records by how incomplete they are and write a research work-list.
 *
 * The point is to produce something a researcher can act on, so the output
 * carries EVERY field the record holds rather than a summary. If a field is
 * absent from the report it is absent from the database, which is the whole
 * signal.
 *
 * WHY THE GAPS ARE WEIGHTED RATHER THAN COUNTED. A flat count is dominated by
 * the gaps almost every record shares -- 88% have no death date, 85% no
 * birthdate, 78% no photograph -- so the "worst" records come out as whoever
 * happens to lack a portrait. The weights below favour what this database
 * exists to record: that somebody was imprisoned, when, on what charge, and
 * under what sentence. A missing photograph scores 1; a missing case record
 * scores 8.
 */
final class ReportIncompleteRecords extends Command
{
    protected $signature = 'prisoners:report-incomplete
        {--limit=500 : How many records to report}
        {--md= : Write a Markdown work-list to this path}
        {--json= : Write the same data as JSON to this path}
        {--min-score=0 : Only include records scoring at least this}';

    protected $description = 'Rank the least-complete prisoner records and write a research work-list';

    /** @var array<string, int> gap label => weight */
    private const WEIGHTS = [
        'NO BIOGRAPHY' => 8,
        'NO CASE RECORD AT ALL' => 8,
        'no arrest or incarceration date' => 6,
        'biography under 150 chars' => 5,
        'no charges' => 4,
        'biography under 300 chars' => 3,
        'no release date (and not in custody)' => 3,
        'no sentence text' => 3,
        'no arrest date' => 2,
        'no incarceration date' => 2,
        'no state' => 2,
        'no era' => 2,
        'no birthdate or age' => 2,
        'no death date (pre-1940 subject, presumed deceased)' => 2,
        'no institution' => 1,
        'no ideology' => 1,
        'no affiliation' => 1,
        'no gender' => 1,
        'no race' => 1,
        'no birthdate' => 1,
        'no photograph' => 1,
    ];

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $minScore = (int) $this->option('min-score');

        $scored = [];

        Prisoner::withoutGlobalScopes()
            ->with(['cases.institution'])
            ->chunk(200, function ($chunk) use (&$scored) {
                foreach ($chunk as $p) {
                    [$score, $missing] = $this->assess($p);
                    $scored[] = ['score' => $score, 'missing' => $missing, 'prisoner' => $p];
                }
            });

        $total = count($scored);

        usort($scored, fn ($a, $b) => $b['score'] <=> $a['score'] ?: strcmp((string) $a['prisoner']->name, (string) $b['prisoner']->name));

        $top = array_values(array_filter(
            array_slice($scored, 0, $limit),
            fn ($r) => $r['score'] >= $minScore,
        ));

        if (! $top) {
            $this->info('Nothing scored at or above the threshold.');

            return self::SUCCESS;
        }

        $this->line(sprintf(
            'Scanned %s records. Reporting %d, scoring %d down to %d.',
            number_format($total),
            count($top),
            $top[0]['score'],
            $top[count($top) - 1]['score'],
        ));

        foreach (array_slice($top, 0, 15) as $i => $r) {
            $this->line(sprintf(
                '  %3d. %-38s score %2d   %s',
                $i + 1,
                mb_strimwidth((string) $r['prisoner']->name, 0, 38),
                $r['score'],
                mb_strimwidth(implode('; ', $r['missing']), 0, 70, '...'),
            ));
        }

        if (count($top) > 15) {
            $this->line('  ... '.(count($top) - 15).' more in the written files');
        }

        if ($path = $this->option('md')) {
            file_put_contents($path, $this->markdown($top, $total));
            $this->info('Markdown written to '.$path.' ('.number_format(filesize($path)).' bytes)');
        }

        if ($path = $this->option('json')) {
            file_put_contents($path, json_encode($this->payload($top), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->info('JSON written to '.$path.' ('.number_format(filesize($path)).' bytes)');
        }

        if (! $this->option('md') && ! $this->option('json')) {
            $this->warn('No --md or --json path given, so only the summary above was produced.');
        }

        return self::SUCCESS;
    }

    /** @return array{0: int, 1: array<int, string>} */
    private function assess(Prisoner $p): array
    {
        $missing = [];
        $desc = trim((string) $p->description);
        $cases = $p->cases;

        if ($desc === '') {
            $missing[] = 'NO BIOGRAPHY';
        } elseif (mb_strlen($desc) < 150) {
            $missing[] = 'biography under 150 chars';
        } elseif (mb_strlen($desc) < 300) {
            $missing[] = 'biography under 300 chars';
        }

        if ($cases->isEmpty()) {
            $missing[] = 'NO CASE RECORD AT ALL';
        } else {
            $hasArrest = $cases->contains(fn ($c) => (bool) $c->arrest_date);
            $hasIncarceration = $cases->contains(fn ($c) => (bool) $c->incarceration_date);

            if (! $hasArrest && ! $hasIncarceration) {
                $missing[] = 'no arrest or incarceration date';
            } elseif (! $hasArrest) {
                $missing[] = 'no arrest date';
            } elseif (! $hasIncarceration) {
                $missing[] = 'no incarceration date';
            }

            if (! $cases->contains(fn ($c) => (bool) $c->release_date) && ! $p->in_custody) {
                $missing[] = 'no release date (and not in custody)';
            }
            if (! $cases->contains(fn ($c) => (bool) $c->charges)) {
                $missing[] = 'no charges';
            }
            if (! $cases->contains(fn ($c) => (bool) $c->sentence)) {
                $missing[] = 'no sentence text';
            }
            if (! $cases->contains(fn ($c) => (bool) $c->institution_id)) {
                $missing[] = 'no institution';
            }
        }

        if (! $p->state) {
            $missing[] = 'no state';
        }
        if (! $p->era) {
            $missing[] = 'no era';
        }
        if (! $p->ideologies) {
            $missing[] = 'no ideology';
        }
        if (! $p->affiliation) {
            $missing[] = 'no affiliation';
        }
        if (! $p->gender) {
            $missing[] = 'no gender';
        }
        if (! $p->race) {
            $missing[] = 'no race';
        }

        if (! $p->birthdate && ! $p->age) {
            $missing[] = 'no birthdate or age';
        } elseif (! $p->birthdate) {
            $missing[] = 'no birthdate';
        }

        // Only chase a death date for someone who cannot plausibly still be
        // alive, so living subjects are not penalised for lacking one.
        $year = $this->approximateYear($p);
        if (! $p->death_date && $year && $year < 1940) {
            $missing[] = 'no death date (pre-1940 subject, presumed deceased)';
        }

        if (! $p->photo) {
            $missing[] = 'no photograph';
        }

        $score = array_sum(array_map(fn ($m) => self::WEIGHTS[$m] ?? 1, $missing));

        return [$score, $missing];
    }

    /** Best guess at the year this record belongs to, from the era or any case date. */
    private function approximateYear(Prisoner $p): ?int
    {
        if ($p->era && preg_match('/(1[6-9]\d\d|20\d\d)/', $p->era, $m)) {
            return (int) $m[1];
        }

        foreach ($p->cases as $case) {
            foreach (['arrest_date', 'incarceration_date', 'release_date', 'sentenced_date'] as $field) {
                if ($case->{$field}) {
                    return (int) $case->{$field}->format('Y');
                }
            }
        }

        return null;
    }

    /** @param  array<int, array{score: int, missing: array, prisoner: Prisoner}>  $top */
    private function markdown(array $top, int $total): string
    {
        $out = [];
        $out[] = '# NPPC records needing the most work';
        $out[] = '';
        $out[] = sprintf('The %d least-complete records of %s, ranked by a weighted gap score.',
            count($top), number_format($total));
        $out[] = '';
        $out[] = 'Weighting favours what this database exists to record: a biography, a case, '
            .'custody dates, charges and a sentence. Missing photographs and death dates count '
            .'for little, because most of the corpus lacks them.';
        $out[] = '';
        $out[] = 'Every field the database holds is reproduced below. A field that is absent here '
            .'is absent from the record — nothing has been summarised away.';
        $out[] = '';
        $out[] = '---';

        foreach ($top as $i => $r) {
            /** @var Prisoner $p */
            $p = $r['prisoner'];
            $out[] = '';
            $out[] = '## '.($i + 1).'. '.($p->name ?: '(unnamed)');
            $out[] = '';
            $out[] = '- **URL:** /prisoner/'.$p->slug;
            $out[] = '- **slug:** `'.$p->slug.'`';
            $out[] = '- **gap score:** '.$r['score'];

            foreach ([
                'Era' => $p->era, 'State' => $p->state, 'Race' => $p->race,
                'Gender' => $p->gender, 'Age' => $p->age,
                'Birthdate' => $p->formatPartialDate('birthdate'),
                'Death date' => $p->formatPartialDate('death_date'),
                'AKA' => $p->aka, 'Inmate number' => $p->inmate_number,
                'Address' => $p->address, 'Website' => $p->website,
                'Ideologies' => $this->flat($p->ideologies),
                'Affiliation' => $this->flat($p->affiliation),
            ] as $label => $value) {
                if ($value !== null && $value !== '' && $value !== []) {
                    $out[] = '- **'.$label.':** '.$value;
                }
            }

            $flags = array_keys(array_filter([
                'in custody' => $p->in_custody, 'released' => $p->released,
                'in exile' => $p->currently_in_exile, 'awaiting trial' => $p->awaiting_trial,
            ]));
            $out[] = '- **Status flags:** '.($flags ? implode(', ', $flags) : 'none set');

            if ($p->cases->isEmpty()) {
                $out[] = '';
                $out[] = '**Cases:** none recorded.';
            }

            foreach ($p->cases as $ci => $case) {
                $out[] = '';
                $out[] = '**Case '.($ci + 1).'**';
                $out[] = '';
                $lines = [];
                foreach ([
                    'Arrest date' => $case->formatPartialDate('arrest_date'),
                    'Incarceration date' => $case->formatPartialDate('incarceration_date'),
                    'Release date' => $case->formatPartialDate('release_date'),
                    'Death in custody' => $case->formatPartialDate('death_in_custody_date'),
                    'Days imprisoned' => $case->imprisoned_for_days,
                    'Indicted' => $case->indicted, 'Convicted' => $case->convicted,
                    'Plead' => $case->plead,
                    'Sentenced date' => $case->formatPartialDate('sentenced_date'),
                    'Sentence' => $case->sentence, 'Charges' => $case->charges,
                    'Prosecutor' => $case->prosecutor, 'Judge' => $case->judge,
                    'Institution' => $case->institution?->name,
                    'Institution city' => $case->institution?->city,
                    'Institution state' => $case->institution?->state,
                ] as $label => $value) {
                    if ($value !== null && $value !== '') {
                        $lines[] = '  - '.$label.': '.$value;
                    }
                }
                $out[] = $lines ? implode("\n", $lines) : '  - (every field empty)';
            }

            $desc = trim((string) $p->description);
            $out[] = '';
            $out[] = '**Biography ('.mb_strlen($desc).' chars):**';
            $out[] = '';
            $out[] = $desc !== '' ? $desc : '_(none)_';
            $out[] = '';
            $out[] = '**Missing:** '.implode('; ', $r['missing']);
            $out[] = '';
            $out[] = '---';
        }

        return implode("\n", $out)."\n";
    }

    /** @param  array<int, array{score: int, missing: array, prisoner: Prisoner}>  $top */
    private function payload(array $top): array
    {
        return array_map(function ($r, $i) {
            /** @var Prisoner $p */
            $p = $r['prisoner'];

            return [
                'rank' => $i + 1,
                'gap_score' => $r['score'],
                'missing' => $r['missing'],
                'slug' => $p->slug,
                'name' => $p->name,
                'url' => '/prisoner/'.$p->slug,
                'era' => $p->era,
                'state' => $p->state,
                'race' => $p->race,
                'gender' => $p->gender,
                'age' => $p->age,
                'birthdate' => $p->partialDateIso('birthdate'),
                'death_date' => $p->partialDateIso('death_date'),
                'aka' => $p->aka,
                'inmate_number' => $p->inmate_number,
                'address' => $p->address,
                'ideologies' => $p->ideologies,
                'affiliation' => $p->affiliation,
                'in_custody' => (bool) $p->in_custody,
                'released' => (bool) $p->released,
                'awaiting_trial' => (bool) $p->awaiting_trial,
                'in_exile' => (bool) $p->currently_in_exile,
                'photo' => $p->photo,
                'description' => $p->description,
                'cases' => $p->cases->map(fn (PrisonerCase $c) => [
                    'arrest_date' => $c->partialDateIso('arrest_date'),
                    'incarceration_date' => $c->partialDateIso('incarceration_date'),
                    'release_date' => $c->partialDateIso('release_date'),
                    'death_in_custody_date' => $c->partialDateIso('death_in_custody_date'),
                    'imprisoned_for_days' => $c->imprisoned_for_days,
                    'indicted' => $c->indicted,
                    'convicted' => $c->convicted,
                    'plead' => $c->plead,
                    'sentenced_date' => $c->partialDateIso('sentenced_date'),
                    'sentence' => $c->sentence,
                    'charges' => $c->charges,
                    'prosecutor' => $c->prosecutor,
                    'judge' => $c->judge,
                    'institution' => $c->institution?->name,
                    'institution_city' => $c->institution?->city,
                    'institution_state' => $c->institution?->state,
                ])->all(),
            ];
        }, $top, array_keys($top));
    }

    private function flat(mixed $value): ?string
    {
        if (is_array($value)) {
            return $value ? implode('; ', array_filter($value)) : null;
        }

        return $value ?: null;
    }
}
