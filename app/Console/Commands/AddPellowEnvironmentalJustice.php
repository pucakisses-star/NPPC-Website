<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers David N. Pellow's 2018 article "Political Prisoners and
 * Environmental Justice" (Capitalism Nature Socialism 29:4, 1-20)
 * as an ArchiveRecord under the Scholarship collection.
 *
 * Idempotent — matches by file path.
 */
final class AddPellowEnvironmentalJustice extends Command {
    protected $signature = 'archive:add-pellow-environmental-justice';
    protected $description = 'Add David Pellow 2018 article "Political Prisoners and Environmental Justice"';

    public function handle(): int {
        $file = '/pdfs/scholarship/pellow-political-prisoners-environmental-justice-2018.pdf';

        $payload = [
            'title' => 'Political Prisoners and Environmental Justice',
            'description' => 'David N. Pellow\'s 2018 article in Capitalism Nature Socialism arguing for a "prisoner-led environmental justice movement." Pellow traces how revolutionary movements of the 20th and 21st centuries — Black Power and Black Liberation, Indigenous and American Indian, Puerto Rican independence, White/European-American anti-imperialist, and radical ecology movements — each carried an environmental-justice orientation through their fights against state power, racism, colonialism, militarism, and the imposition of ecological harm on vulnerable communities. Just as Dan Berger has argued that prisons and jails were important sites of political formation for the civil-rights movement, Pellow argues that spaces of incarceration serve a similar function for the environmental justice movement. Foundational scholarship linking the U.S. political-prisoner archive to environmental justice studies.',
            'authors' => 'David N. Pellow',
            'year' => 2018,
            'file' => $file,
            'record_type' => 'document',
            'source_format' => 'article',
            'collection' => 'Scholarship',
            'publisher' => 'Capitalism Nature Socialism (Taylor & Francis / Routledge)',
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::query()->where('file', $file)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('Updated existing record.');
        } else {
            ArchiveRecord::create($payload);
            $this->info('Created record.');
        }

        return self::SUCCESS;
    }
}
