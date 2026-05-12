<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Adds the 1990 Malone Defense Fund flyer "Who Is Chuck Malone? (And Why
 * is He in Jail?)" as an ArchiveRecord, and adds Chuck Malone as a
 * prisoner — the San Francisco Irish Republican convicted in Montgomery,
 * Alabama in 1990 of conspiracy to ship assault rifles to the IRA.
 *
 * Companion to the Boston Irish 3 flyer (PR #288); both were part of the
 * Constitutional Defense Fund's 1990 organizing.
 */
final class AddChuckMaloneFlyer extends Command {
    protected $signature = 'archive:add-chuck-malone-flyer';
    protected $description = 'Add the 1990 Chuck Malone Defense Fund flyer + Malone prisoner record';

    public function handle(): int {
        $slug = 'who-is-chuck-malone-1990';
        $record = [
            'title' => 'Who Is Chuck Malone? (And Why is He in Jail?) — Malone Defense Fund (1990)',
            'description' => 'Single-page Malone Defense Fund flyer issued after the January 1990 arrest and three-day federal trial of San Francisco Irish Republican Chuck Malone in Montgomery, Alabama. Malone, a lifelong Clan na Gael member and former Fianna Eireann scout leader, was charged with conspiracy to ship assault rifles to the IRA. The flyer outlines his 1972 "Golden Gate Gunrunner" conviction (which produced a probation order forbidding him from associating with Irish people or drinking in Irish bars — later partly struck down on appeal financed by the NAACP), his health (Parkinson\'s, cerebral hemorrhage, high blood pressure), the appointment of an inexperienced public defender, and the prosecution\'s use of an RUC "expert on terrorism" calling the Fianna "youthful storm troopers." Companion piece to the Constitutional Defense Fund\'s Boston Irish 3 organizing the same year.',
            'record_type' => 'document',
            'source_format' => 'flyer',
            'file' => '/pdfs/flyers/who-is-chuck-malone-1990.pdf',
            'collection' => 'Irish Republican Defense Materials',
            'authors' => 'Malone Defense Fund',
            'publisher' => 'Malone Defense Fund (San Francisco)',
            'year' => 1990,
            'date' => '1990-06-01',
            'subjects' => ['Irish Republicanism', 'Political Prisoners', 'Conspiracy Law', 'San Francisco', 'Alabama'],
            'is_digitized' => true,
            'published' => true,
        ];
        $existing = ArchiveRecord::where('slug', $slug)->first();
        if ($existing) {
            $existing->update($record);
            $this->info('RECORD updated: Who Is Chuck Malone (1990).');
        } else {
            ArchiveRecord::create(['slug' => $slug] + $record);
            $this->info('RECORD added: Who Is Chuck Malone (1990).');
        }

        $payload = [
            'name' => 'Chuck Malone',
            'first_name' => 'Chuck',
            'last_name' => 'Malone',
            'description' => 'San Francisco-based lifelong Irish Republican activist. Joined Clan na Gael in the 1950s; co-founded and served as vice-president of Citizens for Irish Justice in San Francisco in the late 1960s; made his first trip to Ireland in 1970 (a year before British internment began) and returned firmly aligned with the Provisional IRA. In late 1972 a U.S. government seizure of a trunkful of rifles bound for Ireland led to his arrest as the "Golden Gate Gunrunner" (Coast Magazine); he pleaded guilty to exporting arms without a license and received two years probation and a $1,000 fine. The probation conditions — forbidding association with Irish people, drinking in Irish bars, joining an Irish Catholic sodality, or working for an Irish contractor — were challenged on appeal financed by the NAACP, and references to religious activity were struck. Through the 1970s and 80s Malone organized and led the Irish Republican Scouts (Fianna Eireann) in San Francisco. Suffering from Parkinson\'s Syndrome, the after-effects of stomach surgery, high blood pressure, and a prior cerebral hemorrhage, he was arrested again in January 1990 in Montgomery, Alabama and charged with conspiracy to ship assault rifles to the IRA. Held without bail through a three-day federal trial in which an RUC expert was permitted to brand Fianna Eireann "youthful storm troopers." Convicted and faced a five-year maximum.',
            'state' => 'California',
            'race' => 'White',
            'gender' => 'Male',
            'ideologies' => ['Irish republicanism', 'Anti-imperialism'],
            'affiliation' => ['Clan na Gael', 'Fianna Eireann', 'Citizens for Irish Justice'],
            'era' => '1990s',
            'in_custody' => false,
            'released' => true,
            'cases' => [
                [
                    'charges' => 'Exporting arms without a license (rifles shipped from San Francisco to Ireland)',
                    'arrest_date' => '1972-12-01',
                    'convicted' => 'Yes — guilty plea',
                    'sentence' => '2 years probation and $1,000 fine',
                ],
                [
                    'institution_name' => 'Federal Bureau of Prisons',
                    'institution_state' => 'Alabama',
                    'charges' => 'Conspiracy to ship assault rifles to the Provisional IRA (Montgomery, Alabama federal trial)',
                    'arrest_date' => '1990-01-01',
                    'convicted' => 'Yes — convicted 1990 (three-day trial; co-defendant granted bail, Malone denied)',
                    'sentence' => 'Faced 5-year maximum',
                ],
            ],
        ];
        $exit = $this->call('prisoner:add', ['json' => json_encode($payload)]);
        if ($exit === self::SUCCESS) {
            $this->info('ADD: Chuck Malone');
        } else {
            $this->info('SKIP: Chuck Malone (already exists or invalid).');
        }

        return self::SUCCESS;
    }
}
