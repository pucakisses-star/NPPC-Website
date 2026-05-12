<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers "Political Prisoners: Racism and the Politics of
 * Imprisonment" (National Minority Advisory Council on Criminal
 * Justice, August 1980) as an ArchiveRecord.
 *
 * 41-page report funded by the Law Enforcement Assistance
 * Administration (LEAA) and U.S. Department of Justice. Notable as a
 * federally-funded document that took the existence of political
 * prisoners in the U.S. seriously, framing minority political
 * prisoners as "victims of racist and political repression."
 */
final class AddNmacPoliticalPrisonersReport extends Command {
    protected $signature = 'archive:add-nmac-political-prisoners-report';
    protected $description = 'Register the NMAC 1980 Political Prisoners report as an ArchiveRecord';

    public function handle(): int {
        $slug = 'nmac-political-prisoners-racism-politics-imprisonment-1980';
        $payload = [
            'title' => 'Political Prisoners: Racism and the Politics of Imprisonment',
            'description' => '41-page report issued in August 1980 by the National Minority Advisory Council on Criminal Justice (NMAC). Notable as a federally-funded document — produced under contract J-LEAA-009-79 with the Law Enforcement Assistance Administration of the U.S. Department of Justice — that took the existence of political prisoners in the United States seriously. The report defines three categories: "minorities as political prisoners," "politicized prisoners," and "race and class prisoners," and argues that racism in the American criminal-legal system produces political prisoners as a structural phenomenon. Includes a selected bibliography and the NMAC membership roster. Submitted to NCJRS as document 76108.',
            'record_type' => 'report',
            'source_format' => 'government report',
            'file' => '/pdfs/reports/nmac-political-prisoners-1980.pdf',
            'collection' => 'Government Documents',
            'authors' => 'National Minority Advisory Council on Criminal Justice',
            'publisher' => 'A.L. Nellum & Associates, Inc. (for U.S. Department of Justice, LEAA)',
            'year' => 1980,
            'date' => '1980-08-01',
            'subjects' => ['Political Prisoners', 'Racism', 'Mass Incarceration', 'Criminal Justice', 'Government Reports'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: NMAC Political Prisoners report (1980).');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: NMAC Political Prisoners report (1980).');
        }

        return self::SUCCESS;
    }
}
