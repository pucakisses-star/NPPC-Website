<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Adds the 1986-87 "There Are Women Political Prisoners in U.S." flyer
 * to the archive — single-page organizing piece demanding the closure of
 * the Lexington High Security Unit (HSU) that held Alejandrina Torres,
 * Susan Rosenberg, and Silvia Baraldini in basement isolation. All three
 * prisoners are already in NPPC, so this command adds the document only.
 */
final class AddLexingtonHsuFlyer extends Command {
    protected $signature = 'archive:add-lexington-hsu-flyer';
    protected $description = 'Add the "There Are Women Political Prisoners in U.S." Lexington HSU flyer to the archive';

    public function handle(): int {
        $slug = 'there-are-women-political-prisoners-lexington-hsu';
        $record = [
            'title' => 'There Are Women Political Prisoners in U.S. — Shut Down the Lexington Women\'s Control Unit',
            'description' => 'Single-page organizing flyer issued in 1986-87 demanding the closure of the Lexington High Security Unit (HSU), a basement control unit at the Federal Correctional Institution in Lexington, Kentucky opened in October 1986 specifically to hold women political prisoners under conditions of sensory deprivation. The flyer profiles the three women then incarcerated in the unit: Alejandrina Torres (FALN, 35 years for seditious conspiracy), Susan Rosenberg (anti-imperialist, 58 years for weapons/explosives/false ID), and Silvia Baraldini (Italian anti-imperialist, 43 years RICO conspiracy from the 1981 Brink\'s investigation). The flyer documents the cost of the unit ($735,000), the U.S. importation of psychological-torture techniques from West Germany and Northern Ireland, and provides organizing instructions — letters to the Warden and Federal Bureau of Prisons Director Norman Carlson. The successful campaign to close the unit ran from 1986 to its 1988 shutdown.',
            'record_type' => 'document',
            'source_format' => 'flyer',
            'file' => '/pdfs/flyers/there-are-women-political-prisoners-lexington-hsu.pdf',
            'collection' => 'Political Prisoner Defense Materials',
            'authors' => 'Committee to End the Marion Lockdown / Lexington HSU support coalition',
            'year' => 1986,
            'date' => '1986-12-01',
            'subjects' => ['Political Prisoners', 'Women Political Prisoners', 'Control Unit Prisons', 'Lexington High Security Unit', 'Sensory Deprivation'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($record);
            $this->info('RECORD updated: Lexington HSU flyer.');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $record);
            $this->info('RECORD added: Lexington HSU flyer.');
        }

        $this->info('Note: prisoners named in the flyer (Torres, Rosenberg, Baraldini) are already in NPPC.');

        return self::SUCCESS;
    }
}
