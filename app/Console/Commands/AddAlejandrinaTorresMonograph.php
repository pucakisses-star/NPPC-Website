<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers "Alejandrina Torres: A Profile of a Puerto Rican Prisoner
 * of War" (Liga por los Derechos y la Liberación de los Pueblos —
 * Capítulo Nacional de Puerto Rico, 1987) as an ArchiveRecord.
 *
 * 16-page monograph compiled by Dr. Luis Nieves-Falcón documenting
 * the conditions of Torres's confinement at the Lexington High
 * Security Unit and presenting an international human-rights appeal
 * under UN Resolutions 2621 (1970) and 3103 (1973).
 */
final class AddAlejandrinaTorresMonograph extends Command {
    protected $signature = 'archive:add-alejandrina-torres-monograph';
    protected $description = 'Register the Alejandrina Torres 1987 LIDLIP monograph as an ArchiveRecord';

    public function handle(): int {
        $slug = 'alejandrina-torres-puerto-rican-pow-1987';
        $payload = [
            'title' => 'Alejandrina Torres: A Profile of a Puerto Rican Prisoner of War',
            'description' => 'Sixteen-page monograph published by the Liga por los Derechos y la Liberación de los Pueblos (International League for the Rights and Liberation of Peoples), Puerto Rico Chapter, on September 1, 1987. Compiled and introduced by Dr. Luis Nieves-Falcón, the booklet profiles FALN prisoner of war Alejandrina Torres — a 48-year-old mother of five and wife of a Protestant minister — and documents her treatment at the Lexington High Security Unit (HSU), the underground sensory-deprivation chamber specially built for political prisoners. The monograph invokes UN Resolutions 2621 (1970) and 3103 (1973) to support Torres\'s claim of POW status as an imprisoned colonial freedom fighter, and was distributed as an international human-rights appeal calling for letters to BOP Director Michael Quinlan, FCI Lexington Warden Dubois, and Congressman Robert Kastenmeier. Reprinted by the Free Puerto Rico Committee (formerly New Movement), San Francisco.',
            'record_type' => 'document',
            'source_format' => 'monograph',
            'file' => '/pdfs/monographs/alejandrina-torres-prisoner-of-war-1987.pdf',
            'collection' => 'Puerto Rican POW Materials',
            'authors' => 'Luis Nieves-Falcón; Liga por los Derechos y la Liberación de los Pueblos (Puerto Rico Chapter)',
            'publisher' => 'Liga por los Derechos y la Liberación de los Pueblos — Capítulo Nacional de Puerto Rico',
            'year' => 1987,
            'date' => '1987-09-01',
            'subjects' => ['Puerto Rican Independence', 'FALN', 'Political Prisoners', 'Women Political Prisoners', 'Lexington HSU', 'Prisoners of War', 'Human Rights'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: Alejandrina Torres monograph (1987).');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: Alejandrina Torres monograph (1987).');
        }

        return self::SUCCESS;
    }
}
