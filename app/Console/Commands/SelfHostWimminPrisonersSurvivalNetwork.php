<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Self-host the Wimmin Prisoners Survival Network No. 2 (Winter
 * 1988/89) PDF. The record was originally registered by
 * AddPfocAdjacentArchive with the file field pointing at the
 * Internet Archive item page; this command swaps it for the
 * self-hosted PDF at /pdfs/periodicals/wimmin-prisoners-survival-network/.
 *
 * Idempotent — also works as a backfill if the record is missing.
 */
final class SelfHostWimminPrisonersSurvivalNetwork extends Command {
    protected $signature = 'archive:selfhost-wpsn';
    protected $description = 'Self-host the Wimmin Prisoners Survival Network No. 2 PDF';

    public function handle(): int {
        $slug = 'wimmin-prisoners-survival-network-no-2-winter-1988';
        $payload = [
            'title' => 'Wimmin Prisoners Survival Network — No. 2, Winter 1988/89',
            'description' => "Second issue of the Wimmin Prisoners Survival Network newsletter (Winter 1988/89). One of the most consistent late-1980s vehicles for women political-prisoner and prisoner-of-war support work in the United States, in close political alignment with the PFOC prisoner-support tradition and the campaign against the Lexington High Security Unit (the federal control unit used to isolate Susan Rosenberg, Alejandrina Torres, and Silvia Baraldini). The newsletter coordinated correspondence, defense fundraising, court-watching, and political education across the women-prisoners-of-war network of the period.",
            'record_type' => 'newsletter',
            'source_format' => 'periodical',
            'file' => '/pdfs/periodicals/wimmin-prisoners-survival-network/wpsn-no-2-winter-1988-89.pdf',
            'collection' => 'Wimmin Prisoners Survival Network',
            'authors' => 'Wimmin Prisoners Survival Network',
            'publisher' => 'Wimmin Prisoners Survival Network',
            'year' => 1988,
            'date' => '1988-12-01',
            'subjects' => [
                'Women Political Prisoners',
                'Lexington HSU',
                'Lexington High Security Unit',
                'Susan Rosenberg',
                'Alejandrina Torres',
                'Silvia Baraldini',
                'Prisoner Support',
                'Feminist Prisoner Solidarity',
                'Wimmin Prisoners Survival Network',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated (self-hosted): Wimmin Prisoners Survival Network No. 2.');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added (self-hosted): Wimmin Prisoners Survival Network No. 2.');
        }

        return self::SUCCESS;
    }
}
