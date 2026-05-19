<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Backfill 33 missing issues of *Libertad*, the newspaper of the
 * Movimiento de Liberación Nacional Puertorriqueño (MLN-PR), filling
 * gaps in the Freedom Archives C25 collection coverage already in the
 * database. Issues sourced from archive.org and self-hosted under
 * /pdfs/periodicals/libertad/.
 *
 * Idempotent — re-runs update by slug.
 */
final class AddLibertadMissingIssues extends Command {
    protected $signature = 'archive:add-libertad-missing';
    protected $description = 'Backfill 33 missing Libertad newspaper issues (MLN-PR, 1979–1990)';

    public function handle(): int {
        $issues = [
            ['1979-01-vol-1-no-2',     '1979-01-01', 'Vol. 1 No. 2',  'January 1979',                  1979],
            ['1981-12-vol-2-no-8',     '1981-12-01', 'Vol. 2 No. 8',  'December 1981',                 1981],
            ['1982-05',                '1982-05-01', '',              'May 1982',                      1982],
            ['1982-06-07',             '1982-06-01', '',              'June/July 1982',                1982],
            ['1983-summer',            '1983-06-01', '',              'Summer 1983',                   1983],
            ['1984-spring-special',    '1984-03-01', '',              'Spring 1984 (Special Edition)', 1984],
            ['1984-summer',            '1984-06-01', '',              'Summer 1984',                   1984],
            ['1984-fall-winter',       '1984-09-01', '',              'Fall/Winter 1984',              1984],
            ['1985-01',                '1985-01-01', '',              'January 1985',                  1985],
            ['1985-02',                '1985-02-01', '',              'February 1985',                 1985],
            ['1985-08',                '1985-08-01', '',              'August 1985',                   1985],
            ['1985-10',                '1985-10-01', '',              'October 1985',                  1985],
            ['1985-12',                '1985-12-01', '',              'December 1985',                 1985],
            ['1986-05',                '1986-05-01', '',              'May 1986',                      1986],
            ['1986-09',                '1986-09-01', '',              'September 1986',                1986],
            ['1987-01',                '1987-01-01', '',              'January 1987',                  1987],
            ['1987-02',                '1987-02-01', '',              'February 1987',                 1987],
            ['1987-03',                '1987-03-01', '',              'March 1987',                    1987],
            ['1987-05',                '1987-05-01', '',              'May 1987',                      1987],
            ['1987-06',                '1987-06-01', '',              'June 1987',                     1987],
            ['1987-11',                '1987-11-01', '',              'November 1987',                 1987],
            ['1987-12',                '1987-12-01', '',              'December 1987',                 1987],
            ['1988-01',                '1988-01-01', '',              'January 1988',                  1988],
            ['1988-06',                '1988-06-01', '',              'June 1988',                     1988],
            ['1988-10',                '1988-10-01', '',              'October 1988',                  1988],
            ['1988-11-12',             '1988-11-01', '',              'November/December 1988',        1988],
            ['1989-01',                '1989-01-01', '',              'January 1989',                  1989],
            ['1989-02',                '1989-02-01', '',              'February 1989',                 1989],
            ['1989-03',                '1989-03-01', '',              'March 1989',                    1989],
            ['1989-04-05',             '1989-04-01', '',              'April/May 1989',                1989],
            ['1989-07',                '1989-07-01', '',              'July 1989',                     1989],
            ['1990-summer',            '1990-06-01', '',              'Summer 1990',                   1990],
            ['1990-fall',              '1990-09-01', '',              'Fall 1990',                     1990],
        ];

        $base = [
            'record_type' => 'newspaper',
            'source_format' => 'periodical',
            'collection' => 'Movimiento de Liberación Nacional Puertorriqueño — Libertad',
            'authors' => 'Movimiento de Liberación Nacional Puertorriqueño (MLN-PR)',
            'publisher' => 'Movimiento de Liberación Nacional Puertorriqueño',
            'subjects' => [
                'Libertad',
                'Movimiento de Liberación Nacional Puertorriqueño',
                'MLN-PR',
                'Puerto Rican Independence',
                'FALN',
                'Puerto Rican Political Prisoners',
                'Oscar Lopez Rivera',
                'Anti-Colonial',
                'Anti-Imperialism',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $added = 0; $updated = 0;
        foreach ($issues as [$ref, $date, $vol, $period, $year]) {
            $slug = 'libertad-ia-'.$ref;
            $title = $vol !== '' ? "Libertad ({$vol}, {$period})" : "Libertad — {$period}";
            $description = "Issue of *Libertad*, the newspaper of the Movimiento de Liberación Nacional Puertorriqueño (MLN-PR). Libertad was the primary U.S.-based organ of the Puerto Rican independence movement aligned with the FALN (Fuerzas Armadas de Liberación Nacional Puertorriqueña) and the support work for the Puerto Rican political prisoners and prisoners of war — including Oscar López Rivera, Alejandrina Torres, Dylcia Pagan, Carlos Alberto Torres, Edwin Cortés, and the other independentistas captured in the FBI's Chicago / New York investigations of the 1980s. The newspaper coordinated independence-movement organizing, U.S. solidarity work, and the long-running campaign for the unconditional release of the prisoners. {$period}. Mirrored from Internet Archive.";
            $payload = $base + [
                'title' => $title,
                'description' => $description,
                'file' => "/pdfs/periodicals/libertad/libertad-{$ref}.pdf",
                'year' => $year,
                'date' => $date,
                'volume' => $vol,
            ];

            $existing = ArchiveRecord::where('slug', $slug)->first();
            if ($existing) { $existing->update($payload); $updated++; }
            else { ArchiveRecord::create(['slug' => $slug] + $payload); $added++; }
        }

        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }
}
