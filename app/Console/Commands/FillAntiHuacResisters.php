<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Fills three anti-HUAC civil-liberties resisters who already existed as empty
 * stubs — all jailed for contempt of Congress for refusing, on First Amendment
 * grounds, to answer the House Un-American Activities Committee:
 *   - Carl Braden — Louisville civil-rights journalist; jailed 1961 (Braden v.
 *     United States, decided with Wilkinson v. United States, 5–4).
 *   - Frank Wilkinson — founder of the campaign to abolish HUAC; jailed with
 *     Braden in 1961 (Wilkinson v. United States, 365 U.S. 399).
 *   - Chandler Davis — University of Michigan mathematician; refused to answer
 *     HUAC on First Amendment grounds in 1954 and served six months at Danbury.
 *
 * Create-or-update by slug; rebuilds each single case. Idempotent.
 */
class FillAntiHuacResisters extends Command
{
    protected $signature = 'prisoners:fill-anti-huac-resisters';

    protected $description = 'Fill Carl Braden, Frank Wilkinson, and Chandler Davis (anti-HUAC contempt-of-Congress resisters)';

    public function handle(): int
    {
        $danbury = Institution::firstOrCreate(
            ['name' => 'Federal Correctional Institution, Danbury'],
            ['city' => 'Danbury', 'state' => 'Connecticut']
        );

        $people = [
            [
                'slug' => 'carl-braden', 'name' => 'Carl Braden',
                'first' => 'Carl', 'last' => 'Braden',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Kentucky', 'era' => '1960s',
                'birth' => [1914, 5, 3], 'death' => [1975, 2, 18],
                'ideologies' => ['Civil rights', 'Anti-racism', 'Free speech'],
                'affiliation' => ['Southern Conference Educational Fund'],
                'bio' => 'Carl Braden (1914–1975) was a Louisville, Kentucky journalist and civil-rights organizer with the Southern Conference Educational Fund. In 1954 he and his wife Anne Braden bought a house in an all-white Louisville suburb and transferred it to a Black family, Andrew and Charlotte Wade; after the house was bombed, it was Braden — not the bombers — who was prosecuted, convicted of sedition, and briefly imprisoned before the conviction was voided. In 1958 he was subpoenaed by the House Un-American Activities Committee in Atlanta and refused, on First Amendment grounds, to answer questions about his associations. Convicted of contempt of Congress, he appealed; the Supreme Court affirmed the conviction 5–4 in Braden v. United States (decided together with Wilkinson v. United States) in 1961, and he served time in federal prison.',
                'charges' => 'Contempt of Congress — for refusing, on First Amendment grounds, to answer questions before the House Un-American Activities Committee (Atlanta, 1958) about his civil-rights associations.',
                'convicted' => 'Yes — conviction affirmed 5–4 by the Supreme Court in Braden v. United States (1961), decided with Wilkinson v. United States.',
                'sentence' => 'Sentenced to twelve months; served about nine months in federal prison (1961–1962).',
                'incarceration' => [1961],
                'institution_id' => null,
            ],
            [
                'slug' => 'frank-wilkinson', 'name' => 'Frank Wilkinson',
                'first' => 'Frank', 'last' => 'Wilkinson',
                'gender' => 'Male', 'race' => 'White', 'state' => 'California', 'era' => '1960s',
                'birth' => [1914, 8, 16], 'death' => [2006, 1, 2],
                'ideologies' => ['Free speech', 'Civil liberties'],
                'affiliation' => ['National Committee to Abolish the House Un-American Activities Committee'],
                'bio' => 'Frank Wilkinson (1914–2006) was a Los Angeles public-housing official turned civil-liberties organizer who devoted his life to abolishing the House Un-American Activities Committee, founding the National Committee to Abolish HUAC (later the National Committee Against Repressive Legislation). Subpoenaed by HUAC in Atlanta in 1958, he refused to answer its questions, invoking the First Amendment. Convicted of contempt of Congress, he lost his appeal when the Supreme Court affirmed 5–4 in Wilkinson v. United States (365 U.S. 399, 1961) — decided alongside Carl Braden\'s case — and served nine months in federal prison. The FBI kept a file on him that grew to some 132,000 pages.',
                'charges' => 'Contempt of Congress — for refusing, on First Amendment grounds, to answer the House Un-American Activities Committee (Atlanta, 1958) about his organizing to abolish the committee.',
                'convicted' => 'Yes — conviction affirmed 5–4 by the Supreme Court in Wilkinson v. United States, 365 U.S. 399 (1961).',
                'sentence' => 'Sentenced to twelve months; served about nine months in federal prison (1961–1962).',
                'incarceration' => [1961],
                'institution_id' => null,
            ],
            [
                'slug' => 'chandler-davis', 'name' => 'Chandler Davis',
                'first' => 'Chandler', 'last' => 'Davis',
                'gender' => 'Male', 'race' => 'White', 'state' => 'Michigan', 'era' => '1950s',
                'birth' => [1926, 8, 12], 'death' => [2022, 9, 24],
                'ideologies' => ['Free speech', 'Communism', 'Civil liberties'],
                'affiliation' => [],
                'bio' => 'Horace Chandler Davis (August 12, 1926 – September 24, 2022) was an American-Canadian mathematician, science-fiction writer, and civil-liberties figure. Subpoenaed in 1953 and called before the House Un-American Activities Committee at its 1954 hearings in Lansing, Michigan, he — alongside University of Michigan colleagues Clement Markert and Mark Nickerson — refused to cooperate; but where the others invoked the Fifth Amendment, Davis uniquely pled the First Amendment, seeking to establish that HUAC had no right to question anyone about political belief or association. The university suspended and then fired him. Convicted of contempt of Congress, he pressed the constitutional challenge on appeal until the Supreme Court declined to hear his case in 1959, and in 1960 he served a six-month sentence in the federal prison at Danbury, Connecticut — where he kept doing mathematics, publishing a paper that carried the acknowledgment "Research supported in part by the Federal Prison System." Blacklisted from American universities, he emigrated to Canada in 1962 and spent the rest of his career at the University of Toronto, becoming a distinguished operator theorist (the Davis–Kahan theorem bears his name), co-editor-in-chief of The Mathematical Intelligencer, and a published science-fiction author whose stories appeared in Astounding Science Fiction.',
                'charges' => 'Contempt of Congress — for refusing, on First Amendment grounds, to answer the House Un-American Activities Committee (1954 Lansing, Michigan hearings) about his political beliefs and associations.',
                'convicted' => 'Yes — convicted of contempt of Congress; he pressed a First Amendment challenge on appeal until the Supreme Court declined to hear his case in 1959.',
                'sentence' => 'A six-month sentence. He surrendered to a U.S. marshal in Grand Rapids, Michigan and began serving on February 2, 1960, and was released about six months later, in the summer of 1960.',
                'incarceration' => [1960, 2, 2],
                'release' => [1960, 8],
                'institution_id' => $danbury->id,
                'photo' => 'chandler-davis.jpg',   // free (GFDL 1.2), from data/photos/
            ],
        ];

        DB::transaction(function () use ($people) {
            foreach ($people as $p) {
                $prisoner = Prisoner::withUnderReview()->where('slug', $p['slug'])->first()
                    ?? new Prisoner(['name' => $p['name']]);

                $prisoner->fill([
                    'name' => $p['name'],
                    'first_name' => $p['first'],
                    'last_name' => $p['last'],
                    'gender' => $p['gender'],
                    'race' => $p['race'],
                    'state' => $p['state'],
                    'era' => $p['era'],
                    'ideologies' => $p['ideologies'],
                    'affiliation' => $p['affiliation'],
                    'description' => $p['bio'],
                    'in_custody' => false,
                    'released' => true,
                    'in_exile' => false,
                    'awaiting_trial' => false,
                ]);
                $prisoner->setPartialDate('birthdate', ...$p['birth']);
                $prisoner->setPartialDate('death_date', ...$p['death']);
                $prisoner->save();

                $prisoner->cases()->delete();
                $case = new PrisonerCase(['prisoner_id' => $prisoner->id]);
                $case->fill([
                    'prisoner_id' => $prisoner->id,
                    'institution_id' => $p['institution_id'],
                    'charges' => $p['charges'],
                    'convicted' => $p['convicted'],
                    'sentence' => $p['sentence'],
                ]);
                $case->setPartialDate('incarceration_date', ...$p['incarceration']);
                if (! empty($p['release'])) {
                    $case->setPartialDate('release_date', ...$p['release']);
                }
                $case->save();

                // Attach a bundled portrait (from data/photos/) if unset.
                if (! empty($p['photo'])) {
                    $src = database_path('data/photos/'.$p['photo']);
                    if (is_file($src) && empty($prisoner->photo)) {
                        Storage::disk('public')->makeDirectory('prisoners');
                        Storage::disk('public')->put('prisoners/'.$p['photo'], file_get_contents($src));
                        $prisoner->photo = 'prisoners/'.$p['photo'];
                        $prisoner->save();
                    }
                }

                $this->info('Filled: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
            }

            // Remove the duplicate "H. Chandler Davis" stub, merged into
            // the canonical chandler-davis record above.
            $dup = Prisoner::withUnderReview()->where('slug', 'h-chandler-davis')->first();
            if ($dup) {
                $dup->cases()->delete();
                $dup->delete();
                $this->info('Deleted duplicate stub "H. Chandler Davis".');
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }
}
