<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Political-prisoner cases found by reading the National Guardian's 1948 issues
 * (Vol. 1, Oct 18 – Dec 27, 1948; OCR text extracted from the marxists.org
 * scans). The paper's big 1948 defense campaigns — the Trenton Six and Willie
 * McGee — are already in the database, as is the Smith Act 12. The remaining
 * gaps actually covered in those 1948 issues:
 *   - Carl Marzani  (imprisoned for concealed CP membership; in the issues)
 *   - Gerhart Eisler (HUAC contempt + passport perjury; Nov 1948 issues)
 *   - John Santo     (TWU organizer ordered deported as a Communist; issue 1)
 * (The Rosa Lee Ingram case does NOT appear in the 1948 issues — the Guardian
 * took it up later — so it is intentionally excluded from this 1948 batch.)
 * Verified against Wikipedia, TIME, and NYU/Tamiment finding aids. Idempotent.
 */
class AddNationalGuardian1948Cases extends Command {
    protected $signature = 'prisoners:add-ng-1948';
    protected $description = 'Add 1948 National Guardian cases (Marzani, Eisler, Santo)';

    public function handle(): int {
        $cases = [
            [
                'name' => 'Carl Marzani', 'first' => 'Carl', 'last' => 'Marzani',
                'state' => 'New York', 'ideologies' => ['Communism'], 'affiliation' => ['Communist Party USA'], 'released' => true,
                'bio' => 'Carl Aldo Marzani (1912–1994) was an Italian-American immigrant radical, OSS veteran, and former State Department employee who became one of the first people imprisoned in the Cold War for concealed Communist Party membership. Indicted in January 1947 on eleven counts of fraud — for drawing his government salary while hiding prewar Communist Party activity — he was convicted in federal court in Washington on June 22, 1947 and sentenced to three years, despite clemency pleas from figures including William Donovan, Albert Einstein, and Thomas Mann. An appeals court threw out nine of the counts and the Supreme Court split 4–4 on the remaining two, leaving the conviction standing; Marzani entered prison in March 1949 and served 32 months. His case was an early cause of the National Guardian, and he went on to a long career as a left-wing writer and publisher.',
                'charges' => 'Eleven counts of fraud (later narrowed to two) for receiving his federal salary while concealing prior Communist Party membership — one of the first Cold War prosecutions of a government employee for political association.',
                'convicted' => 'Yes — convicted on June 22, 1947; nine counts were vacated on appeal and the Supreme Court split 4–4 on the remaining two, leaving the conviction in place.',
                'sentence' => 'Three years; entered prison in March 1949 and served 32 months.',
            ],
            [
                'name' => 'Gerhart Eisler', 'first' => 'Gerhart', 'last' => 'Eisler',
                'state' => 'New York', 'ideologies' => ['Communism'], 'affiliation' => [], 'released' => false,
                'bio' => 'Gerhart Eisler was a German-born Communist and former Comintern agent who, living in the United States in the 1940s, became one of the most prominent targets of the postwar Red Scare — denounced by the House Un-American Activities Committee as the secret "No. 1 Communist" in America. In 1947 he was convicted of contempt of Congress for refusing to be sworn in before HUAC, and of passport and immigration perjury over his Communist affiliation, drawing prison terms of one and three years. The National Guardian, in its first weeks of publication, championed his case as a test of political freedom — "As Eisler goes, so go the ten Hollywood writers." Free on bond while appealing, Eisler jumped bail in May 1949 and stowed away aboard the Polish liner MS Batory; after a detention in England he was permitted to continue to East Germany, where he became head of the state radio system.',
                'charges' => 'Contempt of Congress — for refusing to be sworn in before the House Un-American Activities Committee — and passport/immigration perjury concerning his Communist Party affiliation.',
                'convicted' => 'Yes — convicted in 1947 on both charges (terms of one and three years); free on bond pending appeal, he jumped bail in 1949 and fled to East Germany.',
                'sentence' => 'One- and three-year prison terms; not served in full — he jumped bail in May 1949 and fled the country.',
            ],
            [
                'name' => 'John Santo', 'first' => 'John', 'last' => 'Santo',
                'state' => 'New York', 'ideologies' => ['Communism', 'Labor'], 'affiliation' => ['Transport Workers Union'], 'released' => false,
                'bio' => 'John Santo was a leading organizer of the CIO\'s Transport Workers Union in New York and an immigrant from the old Austro-Hungarian lands who became one of the most prominent labor figures targeted for deportation in the postwar anti-Communist drive. In 1948 the government arrested him on immigration charges and moved to deport him as an alien Communist — a case the press likened to Washington\'s earlier, failed effort to deport the longshore leader Harry Bridges. Santo, a World War II veteran who said he had tried more than twenty times to become a U.S. citizen, lost his fight; as the National Guardian reported in its first issue, he was ordered deported, and he left the United States for Hungary in 1949.',
                'charges' => 'Immigration violations and deportability as an alien Communist — a political deportation case brought against a CIO Transport Workers Union organizer during the postwar Red Scare.',
                'convicted' => 'Ordered deported as an alien Communist in 1948 (an immigration proceeding, not a criminal conviction).',
                'sentence' => 'Deportation; he left the United States for Hungary in 1949.',
            ],
        ];

        DB::transaction(function () use ($cases) {
            foreach ($cases as $c) {
                if (Prisoner::where('name', $c['name'])->exists()) {
                    $this->warn("Skipped (already exists): {$c['name']}");
                    continue;
                }

                $prisoner = Prisoner::create([
                    'name'           => $c['name'],
                    'first_name'     => $c['first'],
                    'last_name'      => $c['last'],
                    'description'    => $c['bio'],
                    'gender'         => 'Male',
                    'state'          => $c['state'],
                    'era'            => '1940s',
                    'ideologies'     => $c['ideologies'],
                    'affiliation'    => $c['affiliation'],
                    'in_custody'     => false,
                    'released'       => $c['released'],
                    'awaiting_trial' => false,
                ]);

                PrisonerCase::create([
                    'prisoner_id' => $prisoner->id,
                    'charges'     => $c['charges'],
                    'convicted'   => $c['convicted'],
                    'sentence'    => $c['sentence'],
                ]);

                $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
            }
        });

        return self::SUCCESS;
    }
}
