<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

/**
 * Expands the exile category beyond Cuba to the wider diaspora of U.S.
 * political fugitives who fled prosecution abroad. The Cuba exiles are
 * already covered (Assata Shakur, Nehanda Abiodun, William Lee Brent, Robert
 * & Mabel Williams, William Morales, Victor Manuel Gerena, the RNA hijacker
 * trio); Stokely Carmichael (Guinea) and Philip Agee are also already in.
 *
 * NOTE on Libya: despite Gaddafi's funding of the Black Panther Party, no
 * prominent American actually took exile in Libya — the diaspora went to
 * Algeria, Guinea, Ghana, Tanzania, Cuba, and France. So Libya yields no add.
 *
 * The genuine gaps, by country of exile:
 *  - Pete O'Neal & Charlotte O'Neal — Tanzania (KC Black Panther chairman who
 *    jumped bail in 1970; UAACC in Arusha; never returned).
 *  - Donald Cox ("Field Marshal DC") — Algeria then France; died in exile 2011.
 *  - Melvin McNair & Jean McNair — France, via the 1972 Delta Flight 841
 *    hijacking; tried in France, never extradited.
 *  - George Wright — Guinea-Bissau then Portugal; BLA member, prison escapee,
 *    Flight 841 hijacker, fugitive 40+ years; extradition denied in 2011.
 *
 * Idempotent: prisoner:add refuses duplicate names.
 */
final class AddExilesAbroadPrisoners extends Command {
    protected $signature = 'prisoners:add-exiles-abroad';
    protected $description = 'Add U.S. political exiles who fled abroad beyond Cuba (Tanzania, France, Portugal, Algeria)';

    public function handle(): int {
        $prisoners = [
            [
                'name' => 'Pete O\'Neal',
                'first_name' => 'Pete',
                'last_name' => 'O\'Neal',
                'aka' => 'Felix Pete O\'Neal',
                'description' => 'Pete O\'Neal (born 1940) was chairman of the Kansas City chapter of the Black Panther Party, where he built free-breakfast and other community programs. Arrested in 1969 and convicted in 1970 of transporting a gun across state lines — under a statute enacted only two weeks before his arrest — he was sentenced to four years. While free on appeal he jumped bail and fled the United States, joining the Black Panther International Section in Algiers before settling permanently in Tanzania. With his wife Charlotte he founded the United African Alliance Community Center near Arusha, running education, health, and arts programs. He has lived in exile in Tanzania ever since, one of the last Panther exiles never to return.',
                'race' => 'Black',
                'gender' => 'Male',
                'state' => 'Tanzania',
                'ideologies' => ['Black liberation', 'Black Panther Party', 'Pan-Africanism'],
                'affiliation' => ['Black Panther Party'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'currently_in_exile' => true,
                'imprisoned_or_exiled' => true,
                'cases' => [[
                    'charges' => 'Transporting a firearm across state lines (1969)',
                    'convicted' => 'Convicted 1970, sentenced to four years; jumped bail while free on appeal and fled into exile',
                    'sentence' => 'Four-year sentence (fled to Algeria, then Tanzania, rather than serve it)',
                ]],
            ],
            [
                'name' => 'Charlotte O\'Neal',
                'first_name' => 'Charlotte',
                'last_name' => 'O\'Neal',
                'aka' => 'Charlotte Hill O\'Neal; Mama C',
                'description' => 'Charlotte O\'Neal, known as Mama C, is an artist, poet, and musician who went into exile alongside her husband, Black Panther leader Pete O\'Neal. After Pete fled United States prosecution in 1970, Charlotte joined him abroad, and together they settled in Tanzania and co-founded the United African Alliance Community Center near Arusha. There she has spent decades leading arts, music, and education programs and mentoring young people, becoming a prominent figure in the African American expatriate and Pan-Africanist community in East Africa. Like her husband, she has remained in exile in Tanzania.',
                'race' => 'Black',
                'gender' => 'Female',
                'state' => 'Tanzania',
                'ideologies' => ['Black liberation', 'Pan-Africanism'],
                'affiliation' => ['Black Panther Party'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'currently_in_exile' => true,
                'imprisoned_or_exiled' => true,
                'cases' => [[
                    'charges' => 'Went into exile with her husband Pete O\'Neal following his flight from U.S. prosecution',
                    'convicted' => 'Never charged; has lived in exile in Tanzania since the early 1970s',
                ]],
            ],
            [
                'name' => 'Donald Cox',
                'first_name' => 'Donald',
                'last_name' => 'Cox',
                'aka' => 'Field Marshal DC; Don Cox',
                'description' => 'Donald Cox, known in the Black Panther Party as Field Marshal DC, sat on the party central committee and was responsible for organizing armed self-defense. Facing conspiracy charges and fearing prosecution, he fled the United States in 1970. He joined the Black Panther International Section in Algiers established by Eldridge and Kathleen Cleaver, and later settled in the Languedoc region of southern France. Cox never returned to the United States, living out his life in exile and writing a memoir of his Panther years before his death in France in 2011.',
                'race' => 'Black',
                'gender' => 'Male',
                'state' => 'France',
                'ideologies' => ['Black liberation', 'Black Panther Party', 'Anti-imperialism'],
                'affiliation' => ['Black Panther Party'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'currently_in_exile' => false,
                'imprisoned_or_exiled' => true,
                'cases' => [[
                    'charges' => 'Conspiracy charges connected to Black Panther Party activity',
                    'convicted' => 'Never tried; fled the United States in 1970 and lived in exile in Algeria and then France until his death in 2011',
                ]],
            ],
            [
                'name' => 'Melvin McNair',
                'first_name' => 'Melvin',
                'last_name' => 'McNair',
                'description' => 'Melvin McNair was a young African American and Vietnam-era Army veteran who, with his wife Jean and two companions, hijacked Delta Air Lines Flight 841 out of Detroit on July 31, 1972, demanding a one-million-dollar ransom and passage to Algeria to join the Black Panther International Section. The hijackers released all 86 passengers in Miami before flying on to Algiers. Disillusioned with exile in Algeria, the McNairs moved to France, where they were arrested in 1976, tried, and given relatively light sentences; France declined to extradite them. Melvin McNair settled in France, working at an orphanage in Caen and coaching youth baseball, and has said he is at peace with the choices he made.',
                'race' => 'Black',
                'gender' => 'Male',
                'state' => 'France',
                'ideologies' => ['Black liberation', 'Anti-imperialism'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'currently_in_exile' => true,
                'imprisoned_or_exiled' => true,
                'cases' => [[
                    'charges' => 'Air piracy — hijacking of Delta Air Lines Flight 841 (July 31, 1972)',
                    'convicted' => 'Tried and convicted in France in the late 1970s; France declined to extradite him to the United States',
                    'sentence' => 'Relatively light French sentence; settled permanently in France',
                    'in_exile_since' => '1972-07-31',
                ]],
            ],
            [
                'name' => 'Jean McNair',
                'first_name' => 'Jean',
                'last_name' => 'McNair',
                'aka' => 'Jean Allen McNair',
                'description' => 'Jean McNair, with her husband Melvin, was among those who hijacked Delta Air Lines Flight 841 from Detroit on July 31, 1972, seeking ransom and passage to Algeria to join the exiled Black Panther International Section. After the passengers were released in Miami the group flew to Algiers, then moved on to France when life in Algeria proved disappointing. Arrested in France in 1976 and given a relatively light sentence, Jean McNair was not extradited and remained in France, where she and Melvin rebuilt their lives doing community and youth work in Caen.',
                'race' => 'Black',
                'gender' => 'Female',
                'state' => 'France',
                'ideologies' => ['Black liberation', 'Anti-imperialism'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'currently_in_exile' => true,
                'imprisoned_or_exiled' => true,
                'cases' => [[
                    'charges' => 'Air piracy — hijacking of Delta Air Lines Flight 841 (July 31, 1972)',
                    'convicted' => 'Tried and convicted in France in the late 1970s; not extradited to the United States',
                    'sentence' => 'Relatively light French sentence; settled permanently in France',
                    'in_exile_since' => '1972-07-31',
                ]],
            ],
            [
                'name' => 'George Wright',
                'first_name' => 'George',
                'last_name' => 'Wright',
                'aka' => 'José Luís Jorge dos Santos',
                'description' => 'George Wright was a Black Liberation Army member who became one of the longest-running fugitives in U.S. history. Imprisoned in New Jersey for a 1962 robbery-murder, he escaped from Leesburg State Prison in 1970 and joined the Black Liberation Army in Detroit. On July 31, 1972 Wright — reportedly disguised as a priest, with a pistol hidden in a hollowed-out Bible — was among the BLA members who hijacked Delta Air Lines Flight 841, releasing the passengers in Miami and flying on to Algeria. He later lived openly under his own name in Guinea-Bissau through the 1980s, then gained Portuguese citizenship and a new name, José Luís Jorge dos Santos, through marriage. Arrested in Portugal in 2011 after more than four decades on the run, he avoided extradition when Portugal refused to surrender one of its citizens, and he remained free in Portugal.',
                'race' => 'Black',
                'gender' => 'Male',
                'state' => 'Portugal',
                'ideologies' => ['Black liberation', 'Anti-imperialism'],
                'affiliation' => ['Black Liberation Army'],
                'era' => '1970s',
                'in_custody' => false,
                'released' => false,
                'in_exile' => true,
                'currently_in_exile' => true,
                'imprisoned_or_exiled' => true,
                'cases' => [[
                    'charges' => 'Escape from a New Jersey state prison (1970) while serving time for a 1962 robbery-murder; air piracy (hijacking of Delta Air Lines Flight 841, 1972)',
                    'convicted' => 'Fugitive for more than 40 years; arrested in Portugal in 2011, but extradition was denied',
                    'sentence' => 'Lived in exile in Guinea-Bissau and Portugal; never returned to U.S. custody',
                    'in_exile_since' => '1972-07-31',
                ]],
            ],
        ];

        $added = 0;
        $skipped = 0;
        foreach ($prisoners as $p) {
            $this->line("\n— {$p['name']} —");
            $code = Artisan::call('prisoner:add', ['json' => json_encode($p, JSON_UNESCAPED_UNICODE)]);
            $this->line(trim(Artisan::output()));
            if ($code === self::SUCCESS) {
                $added++;
            } else {
                $skipped++;
            }
        }

        $this->info("\nDone — added {$added}, skipped {$skipped} (already present).");

        return self::SUCCESS;
    }
}
