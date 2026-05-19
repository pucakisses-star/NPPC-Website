<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers Sundiata Acoli's original 1992 essay "A Brief History of
 * the New Afrikan Prison Struggle" as an ArchiveRecord. This is the
 * original text — distinct from "An Updated History of the New
 * Afrikan Prison Struggle" (the later expanded version, already in
 * the archive as new-afrikan-prison-struggle-acoli).
 *
 * PDF self-hosted at /pdfs/books/brief-history-new-afrikan-prison-struggle-acoli-1992.pdf
 * (sourced from the Internet Archive abrief-history-of-the-new-afrikan-prison-struggle item).
 */
final class AddBriefHistoryNewAfrikanArchive extends Command {
    protected $signature = 'archive:add-brief-history-new-afrikan';
    protected $description = 'Register Sundiata Acoli\'s 1992 essay A Brief History of the New Afrikan Prison Struggle';

    public function handle(): int {
        $slug = 'brief-history-new-afrikan-prison-struggle-acoli-1992';
        $payload = [
            'title' => 'A Brief History of the New Afrikan Prison Struggle',
            'description' => "Sundiata Acoli's original 1992 essay tracing the political history of Black political prisoners in U.S. state and federal custody from the late 1960s through the early 1990s. Written from inside, the text articulates the New Afrikan political identity — the post-1968 Republic of New Afrika tradition of Black national consciousness, anti-imperialist alignment, and prison-organizing practice — and narrates the 1960s–70s prison rebellions (Attica, San Quentin, Folsom), the BLA-era prisoner cohort, the Black liberation prosecutions of the 1970s and 1980s, the founding and persecution of the RNA, and the long campaign for recognition of New Afrikans as a colonized people. The foundational text of the New Afrikan Prison Movement and a core reference document for movement prisoner-support work; later expanded by Acoli as \"An Updated History of the New Afrikan Prison Struggle\" (also in this archive).",
            'record_type' => 'document',
            'source_format' => 'essay',
            'file' => '/pdfs/books/brief-history-new-afrikan-prison-struggle-acoli-1992.pdf',
            'collection' => 'Movement Reference',
            'authors' => 'Sundiata Acoli',
            'publisher' => 'Spear & Shield Publications',
            'year' => 1992,
            'date' => '1992-01-01',
            'subjects' => [
                'Sundiata Acoli',
                'New Afrikan Prison Movement',
                'Republic of New Afrika',
                'RNA',
                'New Afrikan',
                'Black Liberation',
                'Black Liberation Army',
                'BLA',
                'Attica',
                'Prison Rebellions',
                'Political Prisoners',
                'Anti-Imperialism',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: A Brief History of the New Afrikan Prison Struggle (Acoli, 1992).');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: A Brief History of the New Afrikan Prison Struggle (Acoli, 1992).');
        }

        return self::SUCCESS;
    }
}
