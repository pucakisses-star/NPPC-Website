<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills Leavenworth inmate numbers for the WWI-era IWW / Espionage Act
 * prisoners whose bios mention Leavenworth, matched against the National
 * Archives (Kansas City) U.S. Penitentiary Leavenworth inmate-case-file index
 * at archives.gov. Only EXACT full-name matches are included here (surname +
 * given name uniquely matched a single index entry); ambiguous, heuristic, and
 * mismatched candidates were excluded. Each number is set only when the record
 * has no inmate_number yet — existing values are never overwritten. Idempotent.
 */
final class SetLeavenworthInmateNumbers extends Command
{
    protected $signature = 'prisoners:set-leavenworth-inmate-numbers';

    protected $description = 'Backfill Leavenworth inmate numbers (exact NARA index matches) for prisoners whose bios mention Leavenworth';

    public function handle(): int
    {
        // slug => Leavenworth inmate number (NARA Kansas City index)
        $numbers = [
            'a-m-blumberg' => '14813',
            'abraham-l-sugarman' => '13847',
            'albert-b-prashner' => '13138',
            'albert-barr' => '14807',
            'albert-eberlie' => '12031',
            'alexander-cournos' => '13123',
            'anthony-j-stopa' => '13201',
            'archie-sinclair' => '13141',
            'arthur-boose' => '13150',
            'billie-rogers' => '13687',
            'burt-lorton' => '13132',
            'caesar-tabib' => '13582',
            'carl-ahlteen' => '13100',
            'carl-schnell' => '14825',
            'charles-ashleigh' => '13115',
            'charles-h-mackinnon' => '13165',
            'charles-l-lambert' => '13107',
            'charles-rothfiser' => '13112',
            'christian-seeger' => '13373',
            'christian-yearous' => '13080',
            'curt-von-einen' => '13048',
            'dan-buckley' => '13119',
            'dave-ingar' => '13160',
            'david-t-blodgett' => '12328',
            'don-sheridan' => '13140',
            'e-j-huber' => '14817',
            'earl-browder' => '14314',
            'edgar-hoover' => '13834',
            'edward-hamilton' => '13128',
            'edward-quigley' => '13578',
            'elmer-anderson' => '13561',
            'enrique-flores-magon' => '12839',
            'enrique-flores-magon-2' => '12839',
            'ernest-henning' => '14823',
            'floyd-ramp' => '12702',
            'francis-x-schilling' => '13366',
            'frank-moran' => '13597',
            'frank-reilly' => '13598',
            'frank-westerlund' => '13178',
            'fred-grau' => '14822',
            'fred-jaakkola' => '13129',
            'frederick-esmond' => '13569',
            'g-a-fanning' => '13689',
            'g-j-bourg' => '13118',
            'g-w-bouldin' => '15402',
            'george-f-voetter' => '13584',
            'george-oconnell' => '13576',
            'george-wenger' => '14826',
            'godfrey-ebel' => '13567',
            'grover-h-perry' => '13137',
            'h-c-spence' => '12027',
            'h-h-munson' => '12026',
            'harry-a-latour' => '13596',
            'harry-brewer' => '13562',
            'harry-drew' => '14814',
            'harry-lloyd' => '13164',
            'harry-mccarl' => '14818',
            'henry-hammer' => '13572',
            'herbert-mahler' => '13166',
            'herbert-stredwick' => '13587',
            'ira-hardy' => '12032',
            'j-gresbach' => '14815',
            'j-h-majors' => '12033',
            'j-m-danley' => '12941',
            'j-r-sparkman' => '12005',
            'j-t-cumbie' => '13340',
            'jack-law' => '13131',
            'jacob-tori' => '13583',
            'james-elliott' => '13152',
            'james-larson' => '13074',
            'james-quinlan' => '13579',
            'james-rowan' => '13113',
            'james-slovik' => '13142',
            'joe-graber' => '13156',
            'john-avila' => '13148',
            'john-baldazza' => '13116',
            'john-grave' => '13570',
            'john-pancner' => '13136',
            'john-potthast' => '13577',
            'john-robbins' => '12903',
            'john-shirey' => '14229',
            'john-wallberg' => '14821',
            'joseph-a-oates' => '13172',
            'joseph-basor' => '13054',
            'joseph-harper' => '13595',
            'leroy-pinkett' => '12272',
            'leo-laukki' => '13108',
            'librado-rivera' => '15416',
            'louis-parenti' => '13174',
            'manuel-rey' => '13111',
            'michael-sapper' => '14806',
            'morris-hecht' => '14810',
            'morris-levine' => '13162',
            'myron-sprague' => '13581',
            'o-e-gordon' => '14805',
            'p-j-higgins' => '14816',
            'paul-maihak' => '14824',
            'pete-mcevoy' => '13170',
            'peter-green' => '13127',
            'phil-mclaughlin' => '13575',
            'phineas-eastman' => '14803',
            'pierce-c-wetter' => '13179',
            'pietro-nigra' => '13704',
            'ragner-johannsen' => '13130',
            'ralph-chaplin' => '13104',
            'ralph-hosea-chaplin' => '13104',
            'ricardo-flores-magon' => '14596',
            'richard-brazier' => '13103',
            'robert-connellan' => '13653',
            'robert-harden' => '12496',
            'robert-poe' => '14820',
            'roy-crane' => '12028',
            'roy-tyler' => '12276',
            's-b-hicok' => '14811',
            'sam-jacobs' => '12735',
            'sam-scarlett' => '13114',
            'stanley-j-clark' => '13121',
            'ted-fraser' => '13155',
            'tobe-simons' => '14230',
            'v-v-ohare' => '13173',
            'victor-privat' => '12320',
            'vincent-santilli' => '13580',
            'vincente-a-azuara' => '13102',
            'vladimir-lossieff' => '13109',
            'w-l-bennefield' => '12025',
            'w-p-mclester' => '12942',
            'walter-heynacher' => '14840',
            'walter-m-reeder' => '13341',
            'walter-phillips' => '14228',
            'walter-t-nef' => '13110',
            'william-gessert' => '12419',
            'william-tanner' => '13177',
            'william-weyh' => '13180',
            'z-l-risley' => '14766',
        ];

        $set = 0;
        $skipped = 0;
        $missing = 0;
        foreach ($numbers as $slug => $number) {
            $p = Prisoner::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $p) {
                $missing++;
                $this->line("not found: {$slug}");

                continue;
            }
            if (! empty($p->inmate_number)) {
                $skipped++;

                continue;
            }
            $p->inmate_number = $number;
            $p->save();
            $set++;
        }

        $this->info("Done. Set {$set}, skipped {$skipped} (already had a number), not found {$missing}.");

        return self::SUCCESS;
    }
}
