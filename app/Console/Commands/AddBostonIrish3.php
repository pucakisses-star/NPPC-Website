<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds the 1990 Constitutional Defense Fund flyer "Boston Trial
 * Convicts 3 Irish Activists" as an ArchiveRecord, and creates
 * prisoner records for the four defendants: Richard Johnson,
 * Christina Reid, Martin Quigley, and Gerald Hoy.
 */
final class AddBostonIrish3 extends Command {
    protected $signature = 'archive:add-boston-irish-3';
    protected $description = 'Add the Boston Irish 3 flyer + 4 prisoner records';

    public function handle(): int {
        // Archive record
        $slug = 'boston-trial-convicts-3-irish-activists-1990';
        $record = [
            'title' => 'Boston Trial Convicts 3 Irish Activists — Constitutional Defense Fund (1990)',
            'description' => 'Two-page Constitutional Defense Fund flyer issued after the August 20, 1990 conviction of Richard Johnson, Christina Reid, and Martin Quigley in U.S. District Court in Boston for conspiring to develop a missile guidance system intended for use against British helicopters in Northern Ireland. The flyer details the 7-year FBI investigation (100 agents in Boston, 30 surveilling Quigley alone for 10 weeks, 900 phone booths wiretapped, mail openings), the prosecution\'s novel use of Foreign Intelligence Surveillance Act (FISA) warrants in a criminal trial, the in-chambers censorship of defense witness Bernadette Devlin McAliskey, and Judge A. David Mazzone\'s decisions to double the sentences of Johnson and Quigley after conviction. A fourth defendant, Lehigh University computer scientist Gerald Hoy, plea-bargained.',
            'record_type' => 'document',
            'source_format' => 'flyer',
            'file' => '/pdfs/flyers/boston-trial-convicts-3-irish-activists-1990.pdf',
            'collection' => 'Irish Republican Defense Materials',
            'authors' => 'Constitutional Defense Fund',
            'publisher' => 'Constitutional Defense Fund (Boston / San Francisco)',
            'year' => 1990,
            'date' => '1990-09-01',
            'subjects' => ['Irish Republicanism', 'Political Prisoners', 'FISA', 'Boston', 'Northern Ireland'],
            'is_digitized' => true,
            'published' => true,
        ];
        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($record);
            $this->info('RECORD updated: Boston Trial Convicts 3 Irish Activists (1990).');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $record);
            $this->info('RECORD added: Boston Trial Convicts 3 Irish Activists (1990).');
        }

        // Prisoners
        $payloads = json_decode(file_get_contents(database_path('data/boston-irish-3.json')), true);
        $added = 0;
        $skipped = 0;
        foreach ($payloads as $payload) {
            $name = $payload['name'];
            $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
            if ($exit === self::SUCCESS) {
                $this->info("ADD: {$name}");
                $added++;
            } else {
                $skipped++;
            }
        }
        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
