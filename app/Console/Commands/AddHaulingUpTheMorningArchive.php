<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers "Hauling Up the Morning / Izando la Mañana: Writings & Art
 * by Political Prisoners & Prisoners of War in the U.S." edited by
 * Tim Blunk and Raymond Luc Levasseur (Red Sea Press / Jacobin Books,
 * 1990) as an ArchiveRecord. PDF self-hosted at
 * /pdfs/books/hauling-up-the-morning-blunk-levasseur-1990.pdf
 * (mirrored from the Internet Archive hauling-izando item).
 */
final class AddHaulingUpTheMorningArchive extends Command {
    protected $signature = 'archive:add-hauling-up-the-morning';
    protected $description = 'Register Hauling Up the Morning / Izando la Mañana (Blunk & Levasseur, eds., 1990) as an ArchiveRecord';

    public function handle(): int {
        $slug = 'hauling-up-the-morning-blunk-levasseur-1990';
        $payload = [
            'title' => 'Hauling Up the Morning / Izando la Mañana: Writings & Art by Political Prisoners & Prisoners of War in the U.S.',
            'description' => "Bilingual (English/Spanish) anthology of writings and visual art by approximately 80 U.S.-held political prisoners and prisoners of war, edited by Tim Blunk and Raymond Luc Levasseur — themselves political prisoners at the time (the Resistance Conspiracy and Ohio 7 cases). Published in 1990 by Red Sea Press / Jacobin Books. The book opens with the declaration: \"This book is the answer to a lie. The lie is that there are no political prisoners in the United States.\" Contributors include Mumia Abu-Jamal, Assata Shakur, Leonard Peltier, Oscar López Rivera, Carlos Alberto Torres, Dylcia Pagan, Sundiata Acoli, Susan Rosenberg, Marilyn Buck, Linda Evans, Laura Whitehorn, David Gilbert, Alan Berkman, Bashir Hameed, Geronimo ji Jaga (Pratt), Ojore Lutalo, Sekou Odinga, Mutulu Shakur, Russell Maroon Shoatz, and many others — drawn from the Black Liberation Army, Puerto Rican independentista, AIM, Plowshares, Resistance Conspiracy, and Ohio 7 cases, with foreword and contextual essays by William Kunstler and others. Definitive primary-source compilation for the post-COINTELPRO U.S. political-prisoner generation.",
            'record_type' => 'book',
            'source_format' => 'monograph',
            'file' => '/pdfs/books/hauling-up-the-morning-blunk-levasseur-1990.pdf',
            'collection' => 'Movement Reference',
            'authors' => 'Tim Blunk and Raymond Luc Levasseur (editors)',
            'publisher' => 'Red Sea Press / Jacobin Books',
            'year' => 1990,
            'date' => '1990-01-01',
            'subjects' => [
                'Political Prisoners',
                'Prisoners of War',
                'Black Liberation Army',
                'Puerto Rican Independence',
                'FALN',
                'Resistance Conspiracy',
                'Ohio 7',
                'American Indian Movement',
                'AIM',
                'May 19th Communist Organization',
                'Anti-Imperialism',
                'Prison Writing',
                'Prison Art',
                'Mumia Abu-Jamal',
                'Assata Shakur',
                'Leonard Peltier',
                'Oscar Lopez Rivera',
                'Marilyn Buck',
                'Tim Blunk',
                'Raymond Luc Levasseur',
            ],
            'is_digitized' => true,
            'published' => true,
        ];

        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($payload);
            $this->info('RECORD updated: Hauling Up the Morning (Blunk & Levasseur, 1990).');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $payload);
            $this->info('RECORD added: Hauling Up the Morning (Blunk & Levasseur, 1990).');
        }

        return self::SUCCESS;
    }
}
