<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers "Agents of Repression: The FBI's Secret Wars Against the
 * Black Panther Party and the American Indian Movement" by Ward
 * Churchill and Jim Vander Wall (South End Press, 1988) as an
 * ArchiveRecord. The PDF lives at
 *   public/pdfs/books/agents-of-repression-churchill-vander-wall-1988.pdf
 * and is sourced from a libgen scan mirrored on the Internet Archive.
 */
final class AddAgentsOfRepressionArchive extends Command {
    protected $signature = 'archive:add-agents-of-repression';
    protected $description = 'Register Agents of Repression (Churchill & Vander Wall, 1988) PDF as an ArchiveRecord';

    public function handle(): int {
        $slug = 'agents-of-repression-churchill-vander-wall-1988';
        $payload = [
            'title' => "Agents of Repression: The FBI's Secret Wars Against the Black Panther Party and the American Indian Movement",
            'description' => "Foundational 1988 movement history by Ward Churchill and Jim Vander Wall (South End Press). Documents the FBI's COINTELPRO and post-COINTELPRO operations against the Black Panther Party and the American Indian Movement, drawing on Bureau records released under FOIA, court records, and movement testimony. Part I covers the FBI as political police, the COINTELPRO era, and operations against the BPP (including the Chicago Police / FBI raid that killed Fred Hampton and Mark Clark). Part II provides background on the Pine Ridge Lakota struggle. Part III documents the FBI's 1972–76 war on AIM and Pine Ridge — the GOON squads, the Oglala firefight, the prosecution and frame-up of Leonard Peltier, the assassinations of Anna Mae Pictou Aquash and other AIM members, informer operations, and the perjury sustaining the federal cases. Part IV (\"We Will Remember\") closes with movement memory and the political-prisoner roster left behind. The single most cited primary-source compilation on COINTELPRO and the federal war against the BPP and AIM, and a core reference for the entire post-1970s U.S. political-prisoner support tradition.",
            'record_type' => 'book',
            'source_format' => 'monograph',
            'file' => '/pdfs/books/agents-of-repression-churchill-vander-wall-1988.pdf',
            'collection' => 'Movement Reference',
            'authors' => 'Ward Churchill; Jim Vander Wall',
            'publisher' => 'South End Press',
            'year' => 1988,
            'date' => '1988-01-01',
            'subjects' => [
                'COINTELPRO',
                'FBI',
                'Black Panther Party',
                'American Indian Movement',
                'AIM',
                'Leonard Peltier',
                'Fred Hampton',
                'Anna Mae Pictou Aquash',
                'Pine Ridge',
                'Oglala',
                'Political Prisoners',
                'State Repression',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: Agents of Repression (Churchill & Vander Wall, 1988).');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: Agents of Repression (Churchill & Vander Wall, 1988).');
        }

        return self::SUCCESS;
    }
}
