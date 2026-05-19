<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers the RCC6 defense-campaign pamphlet "Resistance Is Not A
 * Crime! Stop the Political Show Trial! Drop the Charges Now!" as an
 * ArchiveRecord. Movement publication from the defense campaign for
 * the Resistance Conspiracy Case 6 — Marilyn Buck, Susan Rosenberg,
 * Linda Evans, Tim Blunk, Laura Whitehorn, and Alan Berkman.
 *
 * PDF self-hosted at /pdfs/pamphlets/rcc6-resistance-is-not-a-crime.pdf
 * (sourced from the Internet Archive rcc-6-resistance-is-not-acrime item).
 */
final class AddRcc6ResistanceNotCrimeArchive extends Command {
    protected $signature = 'archive:add-rcc6-resistance-not-crime';
    protected $description = 'Register the RCC6 Resistance Is Not A Crime! defense-campaign pamphlet';

    public function handle(): int {
        $slug = 'rcc6-resistance-is-not-a-crime';
        $payload = [
            'title' => 'Resistance Is Not A Crime! Stop the Political Show Trial! Drop the Charges Now!',
            'description' => "Defense-campaign pamphlet for the Resistance Conspiracy Case 6 (RCC6) — the May 11, 1988 federal indictment that charged Marilyn Buck, Susan Rosenberg, Linda Evans, Timothy Blunk, Laura Whitehorn, and Alan Berkman with conspiracy in connection with the November 1983 U.S. Capitol bombing and a string of other anti-imperialist actions taken in solidarity with national liberation movements (the South Africa Defense Forces office bombing, the Israeli Aircraft Industries office bombing, attacks on military and police targets in the Washington DC area). The pamphlet sets out the political case against the prosecution as a federal effort to retry — and re-imprison — defendants already serving long terms in other proceedings, and frames the indictment as the criminalization of anti-imperialist political activity. Argues for dropping the charges and rallies support for the defendants through the defense campaign that the pamphlet helped coordinate.",
            'record_type' => 'pamphlet',
            'source_format' => 'pamphlet',
            'file' => '/pdfs/pamphlets/rcc6-resistance-is-not-a-crime.pdf',
            'collection' => 'Resistance Conspiracy Case defense materials',
            'authors' => 'Resistance Conspiracy Case Defense Committee',
            'publisher' => 'Resistance Conspiracy Case Defense Committee',
            'year' => 1988,
            'date' => '1988-01-01',
            'subjects' => [
                'Resistance Conspiracy Case',
                'RCC6',
                'Marilyn Buck',
                'Susan Rosenberg',
                'Linda Evans',
                'Tim Blunk',
                'Laura Whitehorn',
                'Alan Berkman',
                'U.S. Capitol Bombing',
                'Anti-Imperialism',
                'Political Prisoners',
                'Defense Campaign',
                'May 19th Communist Organization',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: RCC6 Resistance Is Not A Crime!');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: RCC6 Resistance Is Not A Crime!');
        }

        return self::SUCCESS;
    }
}
