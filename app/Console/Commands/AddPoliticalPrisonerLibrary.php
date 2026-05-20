<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Registers 38 political-prisoner PDFs sourced from Freedom Archives,
 * archive.org, Jericho NY, and Kersplebedeb as ArchiveRecord rows.
 *
 * The PDFs live under public/pdfs/political-prisoner-library/ and are
 * tracked in git alongside the existing 4strugglemag, BPP newspaper,
 * and ABCF library collections.
 *
 * Idempotent — matches by file path.
 */
final class AddPoliticalPrisonerLibrary extends Command {
    protected $signature = 'archive:add-political-prisoner-library';
    protected $description = 'Register 38 political-prisoner PDFs as ArchiveRecord rows';

    public function handle(): int {
        $added = 0;
        $updated = 0;

        foreach ($this->records() as $rec) {
            $rec['file'] = '/pdfs/political-prisoner-library/'.$rec['file'];
            $rec['record_type'] = 'document';
            $rec['is_digitized'] = true;
            $rec['published'] = true;

            $existing = ArchiveRecord::query()->where('file', $rec['file'])->first();
            if ($existing) {
                $existing->update($rec);
                $updated++;
            } else {
                ArchiveRecord::create($rec);
                $added++;
            }
        }

        $this->info("Done — added {$added}, updated {$updated}.");
        return self::SUCCESS;
    }

    /** @return array<int, array<string, mixed>> */
    private function records(): array {
        return [
            ['file' => 'folsom-manifesto-1970.pdf', 'title' => 'Folsom Prisoners Manifesto of Demands', 'authors' => 'Folsom Prison inmates', 'publisher' => 'Freedom Archives', 'year' => 1970, 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Anti-repression platform issued from the 1970 Folsom Prison strike — a foundational text of 1970s prison-organizing demands.', 'subjects' => ['Prison strikes', 'Anti-repression', 'Folsom']],
            ['file' => 'faln-finding-aid.pdf', 'title' => 'FALN / Puerto Rico Materials — Finding Aid', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Collection guide to FALN and Puerto Rican POW materials in the Freedom Archives holdings.', 'subjects' => ['FALN', 'Puerto Rican independentistas', 'Finding aid']],
            ['file' => 'new-movement-solidarity-puerto-rico.pdf', 'title' => 'New Movement in Solidarity with Puerto Rican Independence', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Solidarity materials in support of Puerto Rican Prisoners of War.', 'subjects' => ['Puerto Rican independentistas', 'Solidarity']],
            ['file' => 'juan-antonio-corretjer-booklet.pdf', 'title' => 'Juan Antonio Corretjer — A Visual Finding Aid', 'publisher' => 'Freedom Archives', 'year' => 2022, 'source_format' => 'book', 'collection' => 'Freedom Archives', 'description' => 'Visual finding aid documenting the life and work of Puerto Rican poet and independentista Juan Antonio Corretjer.', 'subjects' => ['Puerto Rican independentistas', 'Juan Antonio Corretjer']],
            ['file' => 'all-out-save-mumia-1995.pdf', 'title' => 'All Out to Save Mumia Abu-Jamal!', 'authors' => 'Partisan Defense Committee', 'publisher' => 'Partisan Defense Committee', 'year' => 1995, 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Mumia Abu-Jamal death-penalty defense pamphlet published during the 1995 execution-date emergency campaign.', 'subjects' => ['Mumia Abu-Jamal', 'Death penalty', 'Anti-repression']],
            ['file' => 'mxgm-political-prisoner-handbook.pdf', 'title' => 'Political Prisoners in the U.S.: An Organizing Handbook', 'authors' => 'Malcolm X Grassroots Movement (MXGM)', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Movement organizing handbook for political-prisoner support campaigns produced by MXGM.', 'subjects' => ['Anti-repression', 'POW support', 'Organizing']],
            ['file' => 'addameer-palestine-pp-booklet-2016.pdf', 'title' => 'Palestine Political Prisoners Booklet', 'authors' => 'Addameer Prisoner Support and Human Rights Association', 'year' => 2016, 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Bilingual (Arabic/English) Palestinian political-prisoner organizing handbook produced by Addameer.', 'subjects' => ['Palestine', 'Anti-repression']],
            ['file' => 'cointelpro-educator-pamphlet.pdf', 'title' => 'COINTELPRO — Educator Pamphlet', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Long-form COINTELPRO history and methods scan distributed for educational use.', 'subjects' => ['COINTELPRO', 'FBI repression']],
            ['file' => 'free-assata-may19-flyer.pdf', 'title' => 'Free Assata Shakur', 'authors' => 'May 19th Communist Organization', 'publisher' => 'Freedom Archives', 'source_format' => 'flyer', 'collection' => 'Freedom Archives', 'description' => 'Defense flyer for Assata Shakur produced by the May 19th Communist Organization in the 1980s.', 'subjects' => ['Assata Shakur', 'Black Liberation Army']],
            ['file' => 'why-assata-must-be-supported.pdf', 'title' => 'Why Assata Shakur Must Be Supported', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'BLA bulletin laying out the political case for supporting Assata Shakur and Sundiata Acoli.', 'subjects' => ['Assata Shakur', 'Sundiata Acoli', 'Black Liberation Army']],
            ['file' => 'sncc-the-movement-1963.pdf', 'title' => 'The Movement — SNCC Selma Supplement', 'authors' => 'Student Nonviolent Coordinating Committee', 'publisher' => 'SNCC', 'year' => 1963, 'source_format' => 'periodical', 'collection' => 'Freedom Archives', 'description' => 'Civil-rights movement documents and analysis from SNCC\'s newspaper.', 'subjects' => ['SNCC', 'Civil rights']],
            ['file' => 'resistance-conspiracy-case-finding-aid.pdf', 'title' => 'Resistance Conspiracy Case — Finding Aid', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Index to the Resistance Conspiracy Case archive (Marilyn Buck, Linda Evans, Susan Rosenberg, Tim Blunk, Laura Whitehorn, Alan Berkman).', 'subjects' => ['Anti-imperialist political prisoners', 'Resistance Conspiracy Case', 'Finding aid']],
            ['file' => 'fbi-cointelpro-black-extremism-pt1.pdf', 'title' => 'FBI COINTELPRO — Black Extremism File, Part 1', 'authors' => 'Federal Bureau of Investigation', 'publisher' => 'Internet Archive', 'year' => 1971, 'source_format' => 'book', 'collection' => 'Internet Archive', 'description' => 'Original FBI file on COINTELPRO operations targeting Black nationalist organizations, 1967–1971.', 'subjects' => ['COINTELPRO', 'FBI repression', 'Primary source']],
            ['file' => 'fbi-cointelpro-new-left-pt1.pdf', 'title' => 'FBI COINTELPRO — New Left File, Part 1', 'authors' => 'Federal Bureau of Investigation', 'publisher' => 'Internet Archive', 'year' => 1971, 'source_format' => 'book', 'collection' => 'Internet Archive', 'description' => 'Original FBI file on COINTELPRO operations targeting the New Left, 1968–1971.', 'subjects' => ['COINTELPRO', 'FBI repression', 'Primary source']],
            ['file' => 'cointelpro-papers-churchill.pdf', 'title' => 'The COINTELPRO Papers', 'authors' => 'Ward Churchill, Jim Vander Wall', 'publisher' => 'South End Press', 'year' => 1990, 'source_format' => 'book', 'collection' => 'Internet Archive', 'description' => 'Definitive book reproducing FBI COINTELPRO memos and analyzing the campaign against the Black Panthers, AIM, Puerto Rican independentistas, and other movements.', 'subjects' => ['COINTELPRO', 'FBI repression']],
            ['file' => 'collected-works-bla-vol1.pdf', 'title' => 'Collected Works of the Black Liberation Army, Volume 1', 'publisher' => 'Rookery Press', 'source_format' => 'book', 'collection' => 'Internet Archive', 'description' => 'BLA primary documents, study guide, and essays from members and supporters.', 'subjects' => ['Black Liberation Army', 'Primary source']],
            ['file' => 'let-freedom-ring-meyer.pdf', 'title' => 'Let Freedom Ring: A Collection of Documents from the Movements to Free U.S. Political Prisoners', 'authors' => 'Matt Meyer (editor)', 'publisher' => 'PM Press', 'year' => 2008, 'source_format' => 'book', 'collection' => 'Internet Archive', 'description' => 'Anthology of U.S. political-prisoner movement documents, statements, and analyses.', 'subjects' => ['Anthology', 'POW support', 'Anti-repression']],
            ['file' => 'move-organization.pdf', 'title' => 'The MOVE Organization', 'publisher' => 'Internet Archive', 'source_format' => 'pamphlet', 'collection' => 'Internet Archive', 'description' => 'Pamphlet on the history of the MOVE Organization and the MOVE 9 prisoners.', 'subjects' => ['MOVE', 'MOVE 9']],
            ['file' => 'political-prisoners-prisons-black-liberation-davis-1971.pdf', 'title' => 'If They Come in the Morning: Voices of Resistance', 'authors' => 'Angela Y. Davis (editor)', 'publisher' => 'Internet Archive', 'year' => 1971, 'source_format' => 'book', 'collection' => 'Internet Archive', 'description' => 'Angela Davis\'s foundational essay collection on political prisoners, prisons, and Black liberation.', 'subjects' => ['Angela Davis', 'Black Panther Party', 'Political prisoners']],
            ['file' => 'george-jonathan-jackson.pdf', 'title' => 'George Jackson / Jonathan Jackson — Movement Documents', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Biographical and movement documents on George Jackson and his brother Jonathan Jackson.', 'subjects' => ['George Jackson', 'Soledad Brothers', 'Black Panther Party']],
            ['file' => 'seize-the-time-v1n2-1974.pdf', 'title' => 'Seize the Time, Vol. 1 No. 2 (June 1974)', 'authors' => 'Black Panther Party', 'publisher' => 'Freedom Archives', 'year' => 1974, 'source_format' => 'periodical', 'collection' => 'Freedom Archives', 'volume' => 'Vol. 1 No. 2', 'description' => 'Black Panther Party newspaper issue with George Jackson focus.', 'subjects' => ['Black Panther Party', 'George Jackson']],
            ['file' => 'san-quentin-to-attica.pdf', 'title' => 'San Quentin to Attica: The Sound Before the Fury', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Attica rebellion documents — demands, timelines, negotiations.', 'subjects' => ['Attica', 'Prison strikes', 'George Jackson']],
            ['file' => 'afrikan-nation-george-jackson-memorial.pdf', 'title' => 'Afrikan Nation: Towards a Memorial (George Jackson Lives)', 'authors' => 'Anarchist Black Cross', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'George Jackson memorial pamphlet produced by the Anarchist Black Cross.', 'subjects' => ['George Jackson', 'Anarchist Black Cross']],
            ['file' => 'leonard-peltier-freedom-calendar-1995.pdf', 'title' => '1995 Leonard Peltier Freedom Calendar', 'publisher' => 'Leonard Peltier Defense Committee', 'year' => 1995, 'source_format' => 'book', 'collection' => 'Freedom Archives', 'description' => 'Art, timeline, and American Indian Movement history compiled for the 1995 Peltier freedom campaign.', 'subjects' => ['Leonard Peltier', 'American Indian Movement']],
            ['file' => 'fbi-takes-aim-1987.pdf', 'title' => 'The FBI Takes AIM', 'publisher' => 'The Other Side', 'year' => 1987, 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Account of the FBI grand-jury attack on the American Indian Movement.', 'subjects' => ['American Indian Movement', 'COINTELPRO', 'FBI repression']],
            ['file' => 'prison-news-service-1991-08.pdf', 'title' => 'Prison News Service — July/August 1991 (Peltier hearing)', 'publisher' => 'Bulldozer / Prison News Service', 'year' => 1991, 'source_format' => 'periodical', 'collection' => 'Freedom Archives', 'description' => 'Political-prisoner newsletter covering Leonard Peltier\'s 1991 evidentiary hearing.', 'subjects' => ['Leonard Peltier', 'Newsletter']],
            ['file' => 'break-23-fall-1992.pdf', 'title' => 'Break! Issue 23 (Fall 1992)', 'publisher' => 'Freedom Archives', 'year' => 1992, 'source_format' => 'periodical', 'collection' => 'Freedom Archives', 'volume' => 'Issue 23', 'description' => 'Political-prisoner support newsletter covering the LA Rebellion aftermath and political-prisoner cases.', 'subjects' => ['Newsletter', 'LA Rebellion']],
            ['file' => 'framing-of-geronimo-pratt-1992.pdf', 'title' => 'The Framing of Geronimo Pratt', 'publisher' => 'Freedom Archives', 'year' => 1992, 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Defense pamphlet detailing the FBI/police frame-up of Black Panther Geronimo ji Jaga Pratt.', 'subjects' => ['Geronimo Pratt', 'Black Panther Party', 'COINTELPRO']],
            ['file' => 'geronimo-pratt-fact-sheet.pdf', 'title' => 'Geronimo Pratt — Case Fact Sheet', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Concise case biography of Geronimo ji Jaga Pratt.', 'subjects' => ['Geronimo Pratt', 'Black Panther Party']],
            ['file' => 'geronimo-pratt-frame-up-dellums.pdf', 'title' => 'Geronimo Pratt Frame-Up Exposed by Ron Dellums', 'authors' => 'Rep. Ronald V. Dellums', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Congressional intervention by Rep. Ron Dellums exposing the frame-up of Geronimo Pratt.', 'subjects' => ['Geronimo Pratt', 'Black Panther Party']],
            ['file' => 'new-afrikan-pps-conditions-of-confinement.pdf', 'title' => 'New Afrikan / Black Political Prisoners & Prisoners of War: Conditions of Confinement', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Report on conditions of confinement for New Afrikan / Black political prisoners and POWs.', 'subjects' => ['New Afrikan', 'POW', 'Conditions']],
            ['file' => 'black-august-resistance-ashanti-alston.pdf', 'title' => 'Black August Resistance — Ashanti Alston Letter', 'authors' => 'Ashanti Alston', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Prison letter on Black August Resistance from former Black Liberation Army member Ashanti Alston.', 'subjects' => ['Black August', 'Ashanti Alston', 'Black Liberation Army']],
            ['file' => 'doc510-prisons-finding-aid.pdf', 'title' => 'DOC 510: Prison Newspapers Finding Aid', 'publisher' => 'Freedom Archives', 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Master index to the Freedom Archives prison-newspaper collection.', 'subjects' => ['Finding aid', 'Prison press']],
            ['file' => 'arm-the-spirit-1979-08.pdf', 'title' => 'Arm the Spirit (August 1979)', 'publisher' => 'Arm the Spirit / Freedom Archives', 'year' => 1979, 'source_format' => 'periodical', 'collection' => 'Freedom Archives', 'description' => 'Revolutionary prisoners\' newspaper covering political-prisoner organizing inside U.S. prisons.', 'subjects' => ['Newsletter', 'Prison press']],
            ['file' => 'bpp-intercommunal-news-1971.pdf', 'title' => 'Black Panther Intercommunal News Service (1971)', 'authors' => 'Black Panther Party', 'publisher' => 'Freedom Archives', 'year' => 1971, 'source_format' => 'periodical', 'collection' => 'Freedom Archives', 'description' => 'Reprint of the Black Panther Party newspaper, 1971.', 'subjects' => ['Black Panther Party']],
            ['file' => 'bpp-rules-of-the-party-1970.pdf', 'title' => 'Rules of the Black Panther Party', 'authors' => 'Black Panther Party Central Committee', 'publisher' => 'Freedom Archives', 'year' => 1970, 'source_format' => 'pamphlet', 'collection' => 'Freedom Archives', 'description' => 'Central Committee rules document of the Black Panther Party.', 'subjects' => ['Black Panther Party', 'Primary source']],
            ['file' => 'jericho-movement-manual-2014.pdf', 'title' => 'Jericho Movement Manual (2014)', 'publisher' => 'Jericho Movement', 'year' => 2014, 'source_format' => 'book', 'collection' => 'Jericho Movement', 'description' => 'Movement-to-free political prisoners organizing handbook.', 'subjects' => ['Jericho', 'POW support', 'Organizing']],
            ['file' => 'kersplebedeb-catalog-2010.pdf', 'title' => 'Kersplebedeb Catalog 2010', 'publisher' => 'Kersplebedeb', 'year' => 2010, 'source_format' => 'pamphlet', 'collection' => 'Kersplebedeb', 'description' => 'Kersplebedeb publisher catalog including political-prisoner titles.', 'subjects' => ['Catalog', 'Kersplebedeb']],
        ];
    }
}
