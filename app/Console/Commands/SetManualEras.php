<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * For prisoners whose era can't be auto-derived from case dates,
 * this command sets a manually researched era. Run AFTER
 * prisoners:normalize-eras — only acts on prisoners whose era is
 * still empty/dash, so it won't overwrite anything that command
 * already filled.
 */
class SetManualEras extends Command
{
    protected $signature = 'prisoners:set-manual-eras {--dry-run : Show planned writes without applying}';
    protected $description = 'Set manually researched eras for prisoners with no usable case-date data.';

    /**
     * Manually researched mappings: slug → era.
     * Each is justified by a clear era cue in the prisoner\'s
     * existing description (case year, defining event, organizational
     * tenure, etc.). Only applied when the prisoner\'s current era is
     * empty/dash, so this never overwrites a correctly-set value.
     */
    private const MANUAL_ERAS = [
        // Catholic Worker / Plowshares (no case dates because cases
        // weren't fully populated in the original DB)
        'mark-colville'            => '2010s', // Kings Bay Plowshares 2018
        'patrick-oneill'           => '2010s', // Kings Bay Plowshares 2018
        'oren-miller'              => '2020s', // Sumter County perjury 2022
        'michele-naar-obed'        => '1990s', // 1995 Newport News submarine action
        'greg-boertje-obed'        => '2010s', // Y-12 Transform Now Plowshares 2012
        'ciaron-oreilly'           => '1990s', // 1991 ANZUS Ploughshares Griffiss AFB
        'gerald-jerry-ebner'       => '1980s', // Plowshares actions
        'joe-gump'                 => '1980s', // Plowshares
        'jean-gump'                => '1980s', // Plowshares
        'elizabeth-walz'           => '2000s', // Plowshares-tradition activism
        'susan-crane'              => '2000s', // Disarm Now Plowshares 2009
        'charles-liteky'           => '1980s', // SOA Watch / peace activism

        // Catonsville Nine (1968 draft action)
        'john-hogan'               => '1960s',
        'david-darst'              => '1960s',
        'marjorie-bradford-melville' => '1960s',
        'george-mische'            => '1960s',
        'tom-melville'             => '1960s',

        // Baltimore Four (1967 draft blood)
        'david-eberhardt'          => '1960s',
        'rev-james-l-mengel-iii'   => '1960s',

        // Vietnam War-era 1960s
        'george-daniels'           => '1960s', // 1967 Marines disloyal statements
        'captain-howard-levy'      => '1960s', // 1967 court martial
        'lt-henry-howe'            => '1960s', // 1965 antiwar
        'dick-gregory'             => '1960s',
        'fannie-lou-hamer'         => '1960s',
        'sal-castro'               => '1960s', // 1968 East LA walkouts
        'fred-ahmed-evans'         => '1960s', // 1968 Cleveland
        'omali-yeshitela'          => '1960s', // founding period of African People\'s Socialist Party
        'john-sinclair'            => '1960s', // 1969 marijuana sentence

        // 1960s civil rights / radical journalism
        'gloria-rodriguez'         => '1970s', // 1971 BPP newspaper feature
        'michael-tabor'            => '1970s', // Panther 21 case 1969–71
        'peter-bridge'             => '1970s', // 1972 NJ journalist case

        // Vietnam-era / 1970s
        'howard-mechanic'          => '1970s', // 1970 Kent State protest
        'norma-jean-croy'          => '1970s', // 1978–79 case
        'aida-mccray-robinson'     => '1970s', // 1971 hijacking
        'bob-avakian'              => '1970s', // RCP founding period

        // Weather Underground / May 19 / UFF (1980s)
        'kathy-boudin'             => '1980s', // 1981 Brink\'s arrest
        'judy-clark'               => '1980s', // May 19 Communist Org
        'bernardine-dohrn'         => '1980s', // surrendered 1980
        'timothy-blunk'            => '1980s', // UFF era
        'dr-alan-berkman'          => '1980s', // 1981 fugitive aid
        'susan-rosenberg'          => '1980s', // May 19 CO
        'marilyn-buck'             => '1980s', // 1988 prosecution
        'sanyika-shakur'           => '1980s', // gang-life memoir era

        // FBI Most Wanted Puerto Rican independence-era fugitives
        'donna-willmott'           => '1980s', // FBI Ten Most Wanted era
        'claude-marks'             => '1990s', // captured 1994

        // San Francisco 8 (2007 reopened 1971 investigation)
        'francisco-torres'         => '2000s',
        'hank-jones'               => '2000s',
        'ray-boudreaux'            => '2000s',
        'richard-oneal'            => '2000s',
        'harold-taylor'            => '2000s',
        'richard-brown'            => '2000s',

        // ELF / animal rights — 1990s/2000s
        'rod-coronado'             => '1990s', // 1990s ALF Operation Bite Back
        'briana-waters'            => '2000s', // 2001 UW arson
        'roy-bourgeois'            => '1990s', // SOA Watch from 1990s
        'roy-bourgeois-2'          => '1990s', // duplicate-pending-merge

        // Antiwar 1990s
        'steve-argue'              => '1990s', // late-1990s Santa Cruz
        'michael-billington'       => '1980s', // LaRouche prosecutions

        // 2020s recent
        'stephen-plato-mcrae'      => '2020s', // electrical-infrastructure attacks
        'steven-pennycooke'        => '2020s', // June 2020 Philadelphia unrest
        'shawn-collins'            => '2020s', // June 2020 Philadelphia unrest
        'lore-elisabeth-blumenthal' => '2020s', // May 2020 BLM Philadelphia
        'john-mazurek'             => '2020s', // 2024 Stop Cop City Atlanta
        'scott-scelye'             => '2020s', // recent USP Marion listing
        'david-annarelli'          => '2010s', // Floyd County deputy shooting
        'jorge-p-cornell'          => '2010s', // Greensboro racketeering era
        'elisabeth-epps'           => '2020s', // Colorado House 2023–25
        'trevor-miller'            => '2000s', // 2006 Grand River land reclamation
        'nada-nadim-prouty'        => '2000s', // 2007 CIA/FBI case
        'carolyn-rodriguez'        => '2010s', // Fort Worth copwatcher

        // Pre-9/11 cases swept up later — 1990s for the 1996 Olympic etc.
        'gabriel-meyers'           => '2000s', // post-Oscar Grant 2009 BART
    ];

    public function handle(): int
    {
        $applied = 0;
        $skippedNotFound = 0;
        $skippedAlreadySet = 0;

        foreach (self::MANUAL_ERAS as $slug => $era) {
            $p = Prisoner::where('slug', $slug)->first();
            if (! $p) {
                $this->warn("  not found: {$slug}");
                $skippedNotFound++;
                continue;
            }

            $current = trim((string) $p->era);
            if ($current !== '' && $current !== '—') {
                $this->line("  skip: {$slug} already has era '{$current}'");
                $skippedAlreadySet++;
                continue;
            }

            $this->info("  set {$slug}: era -> {$era}");

            if (! $this->option('dry-run')) {
                $p->era = $era;
                $p->saveQuietly();
            }

            $applied++;
        }

        $this->line('');
        if ($this->option('dry-run')) {
            $this->warn('Dry run — no changes written.');
        }
        $this->info("Done.");
        $this->line("  applied:                     {$applied}");
        $this->line("  skipped (already set):       {$skippedAlreadySet}");
        $this->line("  skipped (slug not found):    {$skippedNotFound}");

        return self::SUCCESS;
    }
}
