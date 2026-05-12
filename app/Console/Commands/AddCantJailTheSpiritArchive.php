<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers "Can't Jail the Spirit: Political Prisoners in the U.S. —
 * A Collection of Biographies" (Committee to End the Marion Lockdown,
 * 1985) as an ArchiveRecord. The PDF is the source for the 35 prisoner
 * records imported via archive:add-cant-jail-the-spirit-prisoners.
 */
final class AddCantJailTheSpiritArchive extends Command {
    protected $signature = 'archive:add-cant-jail-the-spirit-archive';
    protected $description = 'Register the Can\'t Jail the Spirit (CEML 1985) PDF as an ArchiveRecord';

    public function handle(): int {
        $slug = 'cant-jail-the-spirit-ceml-1985';
        $payload = [
            'title' => "Can't Jail the Spirit: Political Prisoners in the U.S. — A Collection of Biographies",
            'description' => 'Biographical compendium of US political prisoners compiled and published by the Committee to End the Marion Lockdown (CEML) in 1985. Organized into sections on Native American, New Afrikan/Black, Puerto Rican, North American (white anti-imperialist), and Irish prisoners — including AIM defendants, Black Liberation Army and MOVE prisoners, FALN and Macheteros Puerto Rican Nationalists, the Ohio 7 / United Freedom Front, Resistance Conspiracy and George Jackson Brigade defendants, and Provisional IRA volunteers held in U.S. federal extradition custody. Includes case histories, prison addresses, and a closing list of organizations supporting political prisoners.',
            'record_type' => 'book',
            'source_format' => 'compendium',
            'file' => '/pdfs/books/cant-jail-the-spirit-1985.pdf',
            'collection' => 'Movement Reference',
            'authors' => 'Committee to End the Marion Lockdown (CEML)',
            'publisher' => 'Committee to End the Marion Lockdown',
            'year' => 1985,
            'date' => '1985-01-01',
            'subjects' => ['Political Prisoners', 'AIM', 'Black Liberation Army', 'FALN', 'Macheteros', 'United Freedom Front', 'MOVE', 'Resistance Conspiracy', 'George Jackson Brigade', 'IRA', 'Marion'],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info("RECORD updated: Can't Jail the Spirit (CEML 1985).");
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info("RECORD added: Can't Jail the Spirit (CEML 1985).");
        }

        return self::SUCCESS;
    }
}
