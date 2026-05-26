<?php

namespace App\Console\Commands;

use App\Models\Author;
use Illuminate\Console\Command;

/**
 * Sets researched biographical "about" text on Author rows that
 * appear in the Featured Authors block but currently have empty
 * bios.
 *
 * Idempotent — re-runs just re-write the about column.
 */
final class SetAuthorBios extends Command {
    protected $signature = 'archive:set-author-bios';
    protected $description = 'Add bios to Author rows (Hannah Kass, Ward Churchill, etc.)';

    /** name => about */
    private array $bios = [
        'Hannah Kass' => 'Geographer and abolitionist organizer focused on Black ecologies, environmental justice, and the prison-industrial complex. Earned her PhD in Geography at the University of Wisconsin–Madison; her work traces how movements for land sovereignty and prison abolition intersect — most recently through the Stop Cop City fight in the Weelaunee Forest. Her writing has appeared in Antipode, the Boston Review, Truthout, and Pinko, among others.',

        'Ward Churchill' => 'American author and activist; longtime professor of ethnic studies at the University of Colorado Boulder (1990–2007). Co-founder of the Colorado chapter of the American Indian Movement and a leading scholar of Native American history, federal Indian policy, and the FBI\'s COINTELPRO campaigns. Co-author with Jim Vander Wall of "Agents of Repression" (1988) and "The COINTELPRO Papers" (1990) — foundational primary-source compilations on the federal war against the Black Panther Party and the American Indian Movement. Dismissed from CU Boulder in 2007 in a politically-driven termination following his post-9/11 essay "Some People Push Back."',
    ];

    public function handle(): int {
        $updated = 0; $missing = [];
        foreach ($this->bios as $name => $about) {
            $author = Author::where('name', $name)->first();
            if (! $author) {
                $missing[] = $name;
                continue;
            }
            $author->about = $about;
            $author->save();
            $this->info('Updated: '.$name);
            $updated++;
        }

        $this->info("Done — updated {$updated} author(s).");
        foreach ($missing as $name) {
            $this->warn('NOT FOUND in authors table: '.$name);
        }
        return self::SUCCESS;
    }
}
