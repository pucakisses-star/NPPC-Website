<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\PodcastEpisode;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Merge confirmed duplicate prisoner records surfaced by
 * `prisoners:audit-duplicates`. Pairs were reviewed individually;
 * the William E. / William M. Martin pair is intentionally NOT
 * merged because the differing middle initials and states indicate
 * two distinct people sharing a birthdate.
 *
 * The AIM Pine Ridge co-defendant pairs (Robert "Bob" Robideau and
 * Darrelle "Dino" Butler) were added after confirming each is one
 * person split across a formal-name and a nickname record; the
 * canonical keeps the fuller legal-name slug (matching the existing
 * anna-mae-pictou-aquash choice). For those three AIM pairs the
 * duplicate's cases are redundant, less-complete copies of the same
 * 1975-76 RESMURS acquittal already on the canonical, so they are
 * dropped rather than reassigned (see $dropDupCasesFor).
 *
 * Pass --only=slug1,slug2 to restrict a run to specific canonicals.
 *
 * For each group, the canonical slug is kept and the duplicates
 * are folded in:
 *
 *   - All prisoner_cases rows have their prisoner_id reassigned.
 *   - All podcast_episodes rows have their prisoner_id reassigned.
 *   - All calendar_entries rows have their prisoner_id reassigned.
 *   - Scalar fields on the canonical that are NULL/empty are
 *     populated from the duplicate.
 *   - The duplicate's aka is folded into the canonical's aka
 *     (deduped).
 *   - Array fields (ideologies, affiliation) are unioned.
 *   - The duplicate row is then deleted.
 *
 * Dry-run by default; --apply writes. Idempotent: if the duplicate
 * has already been merged the group is skipped silently.
 */
final class MergeDuplicatePrisoners extends Command
{
    protected $signature = 'prisoners:merge-duplicates {--apply : Actually perform the merges} {--only= : Comma-separated canonical slugs to restrict the run to}';

    protected $description = 'Merge confirmed duplicate prisoner records into a single canonical slug.';

    /**
     * Merge groups: [canonical_slug, [duplicate_slug, ...]]
     * Canonical chosen as the better-known / more-canonical URL.
     */
    private array $groups = [
        // Joseph Waller is Omali Yeshitela's birth name — found during the
        // July 2026 photo audit. The Waller record's only "case" is OCR bleed
        // from the WWI register import (a conscientious-objector case that
        // cannot be his; he was born in 1941), so it is dropped, not moved.
        ['omali-yeshitela',              ['joseph-waller']],
        // Found via the duplicate-photo audit (identical image bytes on two
        // records): spelling-variant and name-variant duplicates.
        ['george-andreytchine',          ['george-andreychine']],
        ['martin-luther-king-jr',        ['martin-luther-king']],
        ['joan-little',                  ['joanne-little']],
        // Found via the support-website audit: name-variant duplicates of the
        // same person (H. Rap Brown / Imam Jamil Al-Amin; Xinachtli; Oso
        // Blanco). Canonicals are the records with the photo and richer data.
        ['jamil-abdullah-al-amin',       ['imam-jamil-al-amin', 'h-rap-brown']],
        ['alvaro-hernandez',             ['alvaro-luna-hernandez']],
        ['byron-chubbuck',               ['oso-blanco-byron-shane-chubbuck']],
        // Prairieland defendant duplicated under her birth name; canonical is
        // the name she goes by (photo and sentencing details backfill in).
        ['meagan-morris',                ['bradford-morris']],
        // Found in the 1950s-era photo deep dive: 1954 Capitol-attack
        // Nationalists duplicated under name variants.
        ['lolita-lebron',                ['lolita-lebron-2']],
        ['irvin-flores-rodriguez',       ['irvin-flores', 'irving-flores']],
        ['eugene-debs',                  ['eugene-victor-debs']],
        ['william-dudley-haywood',       ['bill-haywood', 'william-d-big-bill-haywood', 'w-d-haywood']],
        ['ricardo-flores-magon',         ['ricardo-flores-magon-2']],
        ['thomas-mooney',                ['tom-mooney']],
        ['jacob-stachel',                ['jack-stachel']],
        ['benjamin-j-davis-jr',          ['benjamin-j-davis']],
        ['henry-winston',                ['henry-m-winston']],
        ['filiberto-ojeda-rios',         ['filiberto-ojeda-rios-2']],
        ['sundiata-acoli',               ['clark-squire']],
        ['basheer-hameed',               ['bashir-hameed']],
        ['jim-forest',                   ['james-forest']],
        ['oscar-lopez-rivera',           ['oscar-lopez-rivera-2']],
        ['jamil-abdullah-al-amin',       ['jamil-abdullah-al-amin-2']],
        ['bill-ayers',                   ['william-charles-ayers']],
        ['william-taylor-harris',        ['bill-harris']],
        ['anna-mae-pictou-aquash',       ['anna-mae-aquash']],
        ['robert-robideau',              ['bob-robideau']],
        ['darrelle-dean-butler',         ['dino-butler']],
        ['thomas-william-manning',       ['tom-manning']],
        ['dylcia-pagan',                 ['dylcia-pagan-2']],
        ['mark-rudd',                    ['mark-william-rudd']],
        ['elmer-geronimo-pratt',         ['geronimo-pratt']],
        ['jaan-laaman',                  ['jaan-karl-laaman']],
        ['sekou-kambui',                 ['william-j-turk']],
        ['abdul-majid',                  ['anthony-laborde']],
        ['judith-clark',                 ['judith-a-clark']],
        ['joseph-patrick-doherty',       ['joe-doherty', 'joseph-doherty']],
        ['gerardo-hernandez-nordelo',    ['gerardo-hernandez']],
        ['fernando-gonzalez-llort',      ['fernando-gonzalez']],
        ['christina-reid',               ['christina-l-reid']],
        ['douglas-l-wright',             ['douglas-wright']],
        // Prairieland defendants entered twice — once under their legal name,
        // once under the name they go by. Canonical keeps the legal-name slug
        // (which holds the sentenced case); the duplicate's stub case is dropped.
        ['cameron-arnold',               ['autumn-hill']],
        ['daniel-sanchez-estrada',       ['daniel-rolando-sanchez-estrada']],
        // Duplicates surfaced by the confirmed-identity photo-research pass —
        // each is one person entered twice (a legal/alternate name and the
        // name they go by). The canonical keeps the photographed record; the
        // duplicate's name is folded into aka and, where it holds a redundant
        // copy of the same arrest, its case is dropped (see $dropDupCasesFor).
        ['daniel-alan-baker',            ['dan-baker']],
        ['cara-mitrano',                 ['cara-tobe']],
        ['celeste-legere',               ['celeste-friend']],
        ['anthony-smith',                ['anthony-david-ale-smith']],
        ['branden-wolfe',                ['branden-michael-wolfe']],
        ['carlos-matchett',              ['carlos-a-matchett']],
        ['charles-pittman',              ['charles-anthony-pittman']],
        ['christopher-rojas',            ['christopher-isidro-rojas']],
        ['cyan-bass',                    ['cyan-waters-bass']],
        ['dakotah-horton',               ['dakotah-ray-horton']],
        // Name-variant duplicates surfaced by the historical (IWW / early-labor
        // / Socialist-era) photo-research pass. Canonical keeps the photographed
        // record; the variant name folds into aka.
        ['burt-lorton',                  ['bert-lorton']],
        ['marie-equi',                   ['dr-marie-d-equi']],
        ['louis-parenti',                ['luigi-parenti']],
        ['victor-berger',                ['victor-l-berger']],
        ['annie-arniel',                 ['annie-melvin-arniel']],
        ['ben-salmon',                   ['benjamin-j-salmon']],
        ['hulet-m-wells',                ['hiulet-m-wells']],
        ['j-h-beyer',                    ['j-h-byers']],
        ['james-franklin-melton',        ['jas-franklin-melton']],
        ['otto-janson',                  ['otto-jansen']],
        ['william-ehrhard',              ['william-ehrhardt']],
        // Auto-detected duplicates from the full-database audit (strict same-person:
        // matching surname + compatible given name, no conflicting middle names;
        // common surnames require a confirming birth/death date). 171 groups.
        ['ahmad-rahman', ['ahmad-abdur-rahman']],
        ['albert-lannon', ['albert-f-lannon']],
        ['albert-lima', ['albert-jason-lima']],
        ['alexander-lanier', ['alexander-s-lanier']],
        ['aline-espinosa-villegas', ['aline-a-espinosa-villegas']],
        ['anthony-russo', ['anthony-joseph-russo-jr']],
        ['arthur-harvey', ['arthur-s-harvey']],
        ['avelino-gonzalez-claudio', ['avelino-gonzalez-claudio-2']],
        ['ben-chavis', ['benjamin-chavis', 'benjamin-franklin-chavis-jr']],
        ['ben-joldersma', ['benjamin-joldersma']],
        ['benjamin-j-davis-jr', ['benjamin-jefferson-davis-jr']],
        ['benjamin-sasway', ['benjamin-h-sasway']],
        ['bill-bichsel', ['william-bichsel']],
        ['bill-dunne', ['william-f-dunne']],
        ['bill-heikkila', ['william-heikkila']],
        ['bill-streit', ['bill-frankel-streit', 'william-frankel-streit']],
        ['brent-betterly', ['brent-vincent-betterly']],
        ['brian-church', ['brian-jacob-church']],
        ['carl-a-schmidt', ['carl-a-jack-schmidt']],
        ['carl-marzani', ['carl-aldo-marzani']],
        ['carol-manning', ['carol-saucier-manning']],
        ['charles-africa', ['charles-sims-africa']],
        ['charles-e-mcpherson', ['charles-earl-mcpherson']],
        ['charles-greenlee', ['charles-lee-greenlee-sr']],
        ['christopher-mcintosh', ['christopher-w-mcintosh']],
        ['christopher-monfort', ['christopher-john-monfort']],
        ['clarence-g-maurer', ['clarence-george-maurer']],
        ['cleveland-sellers', ['cleveland-sellers-jr']],
        ['daniel-teuscher', ['daniel-b-teuscher']],
        ['darlene-nicgorski', ['darlene-ann-nicgorski']],
        ['david-agranoff', ['david-p-agranoff']],
        ['david-corcoran', ['david-corcoran-or-corcoren']],
        ['david-mckay', ['david-guy-mckay']],
        ['debbie-africa', ['debbie-sims-africa']],
        ['delbert-africa', ['delbert-orr-africa']],
        ['don-benedict', ['donald-benedict']],
        ['dorothy-healey', ['dorothy-ray-healey']],
        ['earlja-dudley', ['earlja-j-dudley']],
        ['ed-mead', ['edward-allen-mead']],
        ['eddie-goodman-africa', ['edward-eddie-goodman-africa', 'edward-goodman-africa']],
        ['edward-schinzing', ['edward-thomas-schinzing']],
        ['edward-waltner', ['edward-j-waltner']],
        ['elizabeth-duke', ['elizabeth-ann-duke']],
        ['ellen-reiche', ['ellen-brennan-reiche']],
        ['ezra-e-barnhart', ['ezra-earl-barnhart']],
        ['federico-cintron-fiallo', ['federico-cintron-fiallo-2']],
        ['fornandous-henderson', ['fornandous-cortez-henderson']],
        ['forrest-hostetler', ['forrest-e-hostetler']],
        ['frank-burke', ['frank-j-burke']],
        ['fred-burton', ['frederick-burton']],
        ['fred-fine', ['fred-m-fine']],
        ['fred-h-robison', ['frederick-h-robison']],
        ['freddie-pitts', ['freddie-lee-pitts']],
        ['gabriella-oropesa', ['gabriella-victoria-oropesa']],
        ['garrett-ziegler', ['garrett-patrick-ziegler']],
        ['gavaughn-streeter-hillerich', ['gavaughn-gaquez-streeter-hillerich']],
        ['george-ostensen', ['george-michael-ostensen']],
        ['george-pesce', ['george-b-pesce']],
        ['george-ryan', ['george-w-ryan']],
        ['gil-green', ['gilbert-green']],
        ['grace-carlson', ['grace-holmes-carlson']],
        ['greg-boertje-obed', ['gregory-boertje-obed']],
        ['harry-winner', ['harry-e-winner']],
        ['heather-doyle', ['heather-glasgow-doyle']],
        ['helen-bryan', ['helen-r-bryan']],
        ['heriberto-marin', ['heriberto-alfonso-marin']],
        ['herman-suhr', ['herman-d-suhr']],
        ['howard-hairston', ['howard-lee-hairston']],
        ['hugo-pinell', ['hugo-yogi-pinell']],
        ['isaiah-willoughby', ['isaiah-thomas-willoughby']],
        ['jackson-patton', ['jackson-stuart-tamowski-patton']],
        ['jacob-coxey', ['jacob-s-coxey']],
        ['jacob-gaines', ['jacob-michael-gaines']],
        ['jacob-h-barkman', ['jacob-hart-barkman']],
        ['jacob-kenison', ['jacob-lymon-kenison']],
        ['james-h-thorpe-jr', ['james-henry-thorpe-jr']],
        ['james-mckoy', ['james-bun-mckoy']],
        ['jared-chase', ['jared-jay-chase']],
        ['jerritt-pace', ['jerritt-jeremy-pace']],
        ['jesse-cover', ['jesse-j-cover']],
        ['jesse-e-myers', ['jesse-ellis-myers']],
        ['jesse-smallwood', ['jesse-james-smallwood']],
        ['jim-barr', ['james-barr']],
        ['jim-forest', ['james-e-forest']],
        ['joan-bell', ['joan-andrews-bell']],
        ['joe-carroll', ['joseph-carroll']],
        ['joe-clohessy', ['joseph-clohessy']],
        ['joe-gilbert', ['joseph-gilbert']],
        ['joe-stern', ['joseph-stern']],
        ['joe-vargo', ['joseph-vargo']],
        ['john-fife', ['john-m-fife']],
        ['john-grady', ['john-peter-grady']],
        ['john-mazurek', ['jack-mazurek']],
        ['john-williamson', ['jack-williamson', 'john-b-williamson']],
        ['johnny-spain', ['johnny-larry-spain']],
        ['jose-angel-felan', ['jose-felan-2']],
        ['jose-r-rivera-santana', ['jose-tato-rivera-santana']],
        ['joseph-hofer', ['joseph-j-hofer']],
        ['joseph-remiro', ['joseph-michael-remiro']],
        ['joseph-waller', ['joseph-b-waller']],
        ['juan-segarra-palmer', ['juan-segarra-palmer-2']],
        ['judith-beaumont', ['judith-ann-beaumont']],
        ['ken-rippetoe', ['kenneth-rippetoe']],
        ['kenyatta-huggins', ['kenyatta-sheire-huggins']],
        ['lawrence-williamson', ['lawrence-hezekiah-williamson']],
        ['leslie-bacon', ['leslie-ann-bacon']],
        ['lindley-macomber', ['lindley-m-macomber']],
        ['loretta-stack', ['loretta-starvus-stack']],
        ['luz-maria-berrios', ['luz-berrios-berrios']],
        ['manna-c-woodworth', ['manna-clay-woodworth']],
        ['marcus-garvey', ['marcus-mosiah-garvey-jr']],
        ['marjorie-melville', ['marjorie-bradford-melville']],
        ['marshall-conway', ['marshall-eddie-conway']],
        ['martin-quigley', ['martin-p-quigley']],
        ['martino-andrews', ['martino-jamel-andrews']],
        ['mary-anne-grady-flores', ['mary-anne-grady-flores-2']],
        ['matthew-depalma', ['matthew-bradley-depalma']],
        ['maurice-hess', ['maurice-a-hess', 'maurice-abram-hess']],
        ['menno-richer', ['menno-s-richer']],
        ['michael-doyle', ['michael-j-doyle']],
        ['michael-tabor', ['michael-cetawayo-tabor', 'michael-cetewayo-tabor']],
        ['michael-walli', ['michael-r-walli']],
        ['mike-africa-sr', ['michael-davis-africa-sr']],
        ['mike-sturdevant', ['michael-sturdevant']],
        ['mohaman-koti', ['mohaman-geuka-koti']],
        ['neil-mclaughlin', ['neil-r-mclaughlin']],
        ['nicholas-scaglione', ['nicholas-l-scaglione']],
        ['noah-leatherman', ['noah-h-leatherman']],
        ['norberto-gonzalez-claudio', ['norberto-gonzalez-claudio-2']],
        ['omer-neuenschwander', ['omer-c-neuenschwander']],
        ['orlando-gonzalez-claudio', ['orlando-gonzalez-claudio-2']],
        ['oscar-wheeler', ['oscar-o-wheeler']],
        ['otto-wangerin', ['otto-h-wangerin']],
        ['paul-bowen', ['paul-l-bowen']],
        ['philip-connelly', ['philip-m-connelly']],
        ['philip-grosser', ['philip-b-grosser']],
        ['ralph-chaplin', ['ralph-hosea-chaplin']],
        ['richard-hunsinger', ['richard-tyler-hunsinger']],
        ['richard-lake', ['richard-mafundi-lake']],
        ['rita-silk-nauni', ['rita-silk-nauni-2']],
        ['romaine-fitzgerald', ['romaine-chip-fitzgerald']],
        ['ruchell-magee', ['ruchell-cinque-magee']],
        ['russell-little', ['russell-jack-little']],
        ['sammie-ingram', ['sammie-lee-ingram']],
        ['samuel-frey', ['samuel-elliott-frey']],
        ['scott-demuth', ['scott-ryan-demuth']],
        ['scott-warren', ['scott-kenji-warren']],
        ['shamar-betts', ['shamar-n-betts']],
        ['sherman-labovitz', ['sherman-marion-labovitz']],
        ['teddy-heath', ['teddy-jah-heath']],
        ['therese-coupez', ['therese-ann-coupez']],
        ['thomas-manning', ['thomas-william-manning']],
        ['tom-hastings', ['tom-h-hastings', 'tom-howard-hastings']],
        ['tom-lewis', ['thomas-lewis']],
        ['tom-mahedy', ['thomas-mahedy']],
        ['tom-melville', ['thomas-melville']],
        ['tom-tracy', ['thomas-h-tracy']],
        ['tony-minerich', ['anthony-minerich']],
        ['trev-poulson', ['trev-j-poulson']],
        ['tyre-means', ['tyre-wayne-means']],
        ['veronza-bowers', ['veronza-bowers-jr']],
        ['walter-irvin', ['walter-lee-irvin']],
        ['walter-w-oliver', ['walter-winfred-oliver']],
        ['warren-billings', ['warren-k-billings']],
        ['wendy-yoshimura', ['wendy-masako-yoshimura']],
        ['william-coffin', ['william-sloane-coffin']],
        ['william-l-patterson', ['william-lorenzo-patterson']],
        ['william-pennock', ['william-j-pennock']],
        ['william-rodgers', ['william-c-rodgers']],
        ['william-weinstone', ['william-w-weinstone']],
        ['zachary-karas', ['zachary-alexander-karas']],
        // Centralia (1919) defendants entered twice: once under the names the
        // campaign and the trial record used (canonical) and once under full
        // legal names by a bulk import. Same men — "Bert" Bland is James
        // Bertie Bland; "O.C." Bland is Oliver Charles Bland (his brother).
        ['bert-bland', ['james-bertie-bland']],
        ['oc-bland', ['oliver-charles-bland']],
        // Same man twice: the Maryland-D.C. Communist Party chairman jailed
        // in the 1952 Baltimore Smith Act case. Canonical keeps the name he
        // went by; the full-name record's description folds in.
        ['phil-frankfeld', ['philip-frankfeld']],
        // The 1969 hijacker-anarchist entered twice: the full record (photo,
        // case) and a Black Panther roster stub with no case. Same man.
        ['lorenzo-komboa-ervin', ['lorenzo-komboa-ervin-2']],
        // The Holy Land Foundation chairman entered under both transliterations
        // of his given name; same man, same 2008 conviction.
        ['mohammad-el-mezain', ['mohamed-el-mezain']],
        // The Florida 4 Jane's Revenge defendant who took her case to trial,
        // entered under both one-l and two-l spellings. Court records use
        // "Gabriella".
        ['gabriella-oropesa', ['gabriela-oropesa']],
        // The Little Rock 2020 arson defendant entered twice — once under her
        // "Ángel" goes-by name; the canonical carries both cases and the full
        // alias set, and the dup's richer biography folds in.
        ['aline-espinosa-villegas', ['angel-espinosa-villegas']],
        // The Pittsburgh May 30, 2020 defendant with his hyphenated surname in
        // both orders. DOJ filings use Augustyniak-Duncan; the dup's state-case
        // row reassigns to the canonical, which had none.
        ['andrew-augustyniak-duncan', ['andrew-duncan-augustyniak']],
        // The Minneapolis Third Precinct arson defendant entered under both
        // "De-Andre" and "DeAndre" spellings. The hyphenated canonical carries
        // the birthdate; the dup's sole federal-arson case reassigns to it,
        // since the canonical had none.
        ['davon-de-andre-turner', ['davon-deandre-turner']],
        // Geronimo Pratt entered twice; the canonical carries the birthdate,
        // photo and all three case rows, the dup (from a Black Panther
        // newspaper listing) has none.
        ['elmer-geronimo-pratt', ['elmer-pratt']],
        // Patricia Gros (Levasseur), United Freedom Front — same woman, same
        // harboring conviction, entered under both name forms. The canonical
        // carries the birthdate and the fuller case row (judge, dates).
        ['patricia-gros-levasseur', ['pat-gros']],
        // Both records say "born Cynthia Boston in New Rochelle in 1948" —
        // the RNA Minister of Information jailed for Brink's grand-jury
        // contempt. The canonical carries the birthdate, photo and aka.
        ['iya-fulani-sunni-ali', ['fulani-sunni-ali']],
        // The MOU labor leader arrested in 1975 (a Black Panther newspaper
        // stub with no case) is the same Federico Cintrón Fiallo as the
        // 1983-84 criminal-contempt grand-jury resister: same name, same
        // Puerto Rican independence/labor left.
        ['federico-cintron-fiallo', ['federico-cintron-fiallo-3']],
        // The 1974 Soledad self-defense defendant entered twice from Black
        // Panther sourcing, with and without the accent; the canonical
        // carries the case row, the dup is a caseless stub.
        ['inez-garcia', ['inez-garcia-2']],
        // The Charlotte Three poet entered under both name forms; the
        // canonical carries the case row, the dup (caseless) contributes
        // his birthdate via the fill-if-empty backfill.
        ['thomas-james-reddy', ['tj-reddy']],
        // The only white and only female Wilmington Ten defendant, entered
        // under both surname forms; the canonical carries the full case row,
        // the caseless dup contributes her photo.
        ['anne-sheppard-turner', ['anne-sheppard-shepard']],
        // The Atmore-Holman Brothers leader entered three times from Black
        // Panther sourcing; the canonical carries the photo and the full
        // case row, the two caseless stubs' descriptions fold in.
        ['johnny-imani-harris', ['johnnie-harris', 'johnny-imani-harris-2']],
        // Spelling-variant pair; the canonical carries the case row and the
        // photo, the dup is a caseless stub whose description folds in.
        ['malik-fard-muhammad', ['malik-fard-muhammed']],
        // The 1947 cuspidor death-sentence cause celebre entered again as a
        // caseless 1970s stub from Black Panther mining ("Bob Wells", Charles
        // Garry's client, paroled from Vacaville July 1, 1974 after 47 years).
        ['wesley-robert-wells', ['robert-wesley-wells']],
        // Second full-database duplicate audit (exact-name pairs that are the
        // same person, plus movement-name pairs where a record's own AKA names
        // the other record). Jr./Sr. father-son pairs, same-name mass-trial
        // co-defendants, and the William E./M. Martin shared-birthdate pair
        // were reviewed and excluded as genuinely distinct people. Canonical
        // keeps the fuller/photographed record; the alternate name folds into
        // aka. Where both records carry a redundant copy of the same single
        // event, the dup's case is dropped (see $dropDupCasesFor).
        ['alan-berkman',                 ['dr-alan-berkman']],
        ['andres-figueroa-cordero',      ['andres-figueroa-cordero-2']],
        ['rev-carl-kabat',               ['carl-kabat']],
        ['curtis-jones-jr',              ['curtis-jones']],
        ['george-merritt-jr',            ['george-merritt']],
        ['henry-howe',                   ['henry-howe-jr']],
        ['larry-cloud-morgan',           ['larry-cloud-morgan-2']],
        ['larry-morlan',                 ['father-larry-morlan']],
        ['rev-paul-kabat',               ['paul-kabat']],
        ['dr-rafil-dhafir',              ['rafil-dhafir']],
        ['ricardo-chavez-ortiz',         ['ricardo-chavez-ortiz-2']],
        ['william-houston',              ['william-houston-jr']],
        ['william-wright-jr',            ['william-wright']],
        ['david-sohappy',                ['david-sohappy-sr']],
        ['philip-raymond',               ['phil-raymond']],
        ['maddy-pfeiffer',               ['matthew-pfeiffer']],
        ['jeffrey-luers',                ['jeff-free-luers']],
        ['luis-medina',                  ['ramon-labanino-salazar']],
        ['peg-millett',                  ['margaret-millett']],
        ['ida-luz-rodriguez',            ['lucy-rodriguez']],
        ['tim-quinn',                    ['timothy-quinn']],
        ['vernon-joseph-rossman',        ['vern-rossman']],
        ['art-laffin',                   ['arthur-j-laffin']],
        ['cathy-wilkerson',              ['cathlyn-wilkerson']],
        ['abdullah-malik-kabah',         ['jeff-fort']],
        ['haki-malik-abdullah',          ['michael-green']],
        ['sababu-na-uhuru',              ['william-stoner']],
        ['robert-hugh-wilson',           ['standing-deer']],
        ['richard-marshall',             ['dick-marshall']],
        ['eric-thompson',                ['jomo-joka-omowale']],
        ['david-rice',                   ['mondo-we-langa']],
        ['james-earl-grant',             ['jim-grant']],
        ['oscar-johnson',                ['gamba-mani']],
        ['william-phillips-africa',      ['phil-africa']],
        ['larry-jackson',                ['karim-njabafudi']],
        ['hanif-shabazz-bey',            ['beaumont-gereau']],
        ['abdul-azeez',                  ['warren-ballentine']],
        ['shango-bahati-kakawana',       ['bernard-stroble']],
        ['ahmed-evans',                  ['fred-ahmed-evans']],
        ['philip-wigle',                 ['philip-vigol']],
        ['adolfo-matos-antogiorgi',      ['adolfo-matos']],
        ['jerome-zawada',                ['jerry-zawada']],
        // The Oregon 2020-uprising prisoner "Comrade Candle" entered twice: her
        // legal / Oregon DOC name (DeFerrari, which carries the sentenced case)
        // and the activist name her defense campaign uses ("Free Sofia Johnson").
        // Same person; canonical keeps the legal-name slug and folds "Sofia
        // Johnson" into aka.
        ['sofia-deferrari',              ['sofia-johnson']],
    ];

    /**
     * Canonicals whose duplicate records carry only redundant, less-complete
     * copies of a case already held (in fuller form) by the canonical. For
     * these the duplicate's cases are deleted rather than reassigned, so the
     * merged record does not end up with two near-identical rows for the same
     * event. Verified individually against production for each listed pair.
     */
    private array $dropDupCasesFor = [
        // Waller's sole case is a WWI conscientious-objector row that bled in
        // from the register OCR — impossible for a man born in 1941. Drop it.
        'omali-yeshitela',
        // The Andreychine spelling-variant's case is a redundant copy of the
        // same 1918 Chicago IWW mass-trial conviction already on the canonical.
        'george-andreytchine',
        // The canonical already carries both Al-Amin cases (1971 NY robbery and
        // 2000 Fulton County); the two variants each hold a redundant copy.
        'jamil-abdullah-al-amin',
        // Both Oso Blanco records describe the same 1998-99 bank-robbery spree.
        'byron-chubbuck',
        // The 1954 Capitol-attack duplicates each carry a redundant copy of
        // the same case already on the canonical.
        'lolita-lebron',
        'irvin-flores-rodriguez',
        'anna-mae-pictou-aquash',
        'robert-robideau',
        'darrelle-dean-butler',
        // Prairieland: the duplicate's case is a less-complete stub of the
        // sentenced case already held by the canonical, so drop it.
        'cameron-arnold',
        'daniel-sanchez-estrada',
        // Photo-research duplicates whose dup carries a redundant copy of the
        // same arrest already held by the canonical — drop the dup's case
        // rather than leave the merged record with two near-identical rows.
        'daniel-alan-baker',
        'cara-mitrano',
        'celeste-legere',
        'christopher-rojas',
        // Historical name-variant duplicates: the variant's case (where any) is
        // a redundant copy of the same Espionage-Act / IWW conviction already on
        // the canonical, so drop it instead of duplicating the row.
        'william-dudley-haywood',
        'burt-lorton',
        'marie-equi',
        'louis-parenti',
        'victor-berger',
        'annie-arniel',
        'ben-salmon',
        'hulet-m-wells',
        'j-h-beyer',
        'james-franklin-melton',
        'otto-janson',
        'william-ehrhard',
        // The Centralia full-name duplicates each carry a redundant copy of
        // the same 1920 Montesano second-degree-murder conviction already on
        // the canonical, so drop them.
        'bert-bland',
        'oc-bland',
        // Both Frankfeld records carry the same 1952 Baltimore Smith Act
        // conviction; the duplicate's copy is dropped.
        'phil-frankfeld',
        // Both el-Mezain records carry the same HLF conviction.
        'mohammad-el-mezain',
        // Both Oropesa records carry the same federal FACE Act prosecution.
        'gabriella-oropesa',
        // The Ángel dup's single case is a redundant copy of the Little Rock
        // arson already on the canonical (which holds two case rows).
        'aline-espinosa-villegas',
        // The pat-gros dup's case is a less-complete copy of the harboring
        // conviction already on the canonical (which carries judge and dates).
        'patricia-gros-levasseur',
        // Both Sunni-Ali records carry the same Brink's grand-jury contempt
        // jailing (released October 1983); the dup's copy is dropped.
        'iya-fulani-sunni-ali',
        // Second full-database audit: canonicals whose dup carries a redundant
        // copy of the same single event already on the canonical (Plowshares
        // silo actions, FALN seditious conspiracy, Fountain Valley, Attica,
        // Plainfield, the MOVE Ramp killing, the Whiskey Rebellion treason
        // count, etc.). robert-hugh-wilson, larry-jackson and abdul-azeez are
        // intentionally NOT listed — their dups hold a distinct or fuller case
        // that should be reassigned, not dropped.
        'rev-carl-kabat',
        'george-merritt-jr',
        'henry-howe',
        'larry-cloud-morgan',
        'larry-morlan',
        'rev-paul-kabat',
        'dr-rafil-dhafir',
        'david-sohappy',
        'philip-raymond',
        'maddy-pfeiffer',
        'jeffrey-luers',
        'peg-millett',
        'ida-luz-rodriguez',
        'tim-quinn',
        'vernon-joseph-rossman',
        'art-laffin',
        'cathy-wilkerson',
        'abdullah-malik-kabah',
        'richard-marshall',
        'eric-thompson',
        'david-rice',
        'william-phillips-africa',
        'hanif-shabazz-bey',
        'philip-wigle',
        'adolfo-matos-antogiorgi',
    ];

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $only = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('only')))));
        $merged = 0;
        $skipped = 0;

        foreach ($this->groups as [$canonicalSlug, $dupSlugs]) {
            if ($only && ! in_array($canonicalSlug, $only, true)) {
                continue;
            }

            $canonical = Prisoner::where('slug', $canonicalSlug)->first();
            if (! $canonical) {
                $this->warn("MISS canonical /prisoner/{$canonicalSlug} — skipping group.");
                $skipped++;

                continue;
            }

            foreach ($dupSlugs as $dupSlug) {
                $dup = Prisoner::where('slug', $dupSlug)->first();
                if (! $dup) {
                    $this->line("  -- already merged or missing: /prisoner/{$dupSlug}");

                    continue;
                }
                if ($dup->id === $canonical->id) {
                    continue;
                }

                $caseCount = PrisonerCase::where('prisoner_id', $dup->id)->count();
                $podcastCount = PodcastEpisode::where('prisoner_id', $dup->id)->count();
                $calendarCount = CalendarEntry::where('prisoner_id', $dup->id)->count();

                $this->info("MERGE  /prisoner/{$dupSlug}  →  /prisoner/{$canonicalSlug}");
                $this->line("   cases={$caseCount}  podcasts={$podcastCount}  calendar={$calendarCount}");

                if (! $apply) {
                    continue;
                }

                DB::transaction(function () use ($canonical, $dup, $canonicalSlug) {
                    if (in_array($canonicalSlug, $this->dropDupCasesFor, true)
                        && PrisonerCase::where('prisoner_id', $canonical->id)->exists()) {
                        // Canonical already holds the authoritative, more complete
                        // case(s); the duplicate's are redundant copies — drop them.
                        PrisonerCase::where('prisoner_id', $dup->id)->delete();
                    } else {
                        PrisonerCase::where('prisoner_id', $dup->id)->update(['prisoner_id' => $canonical->id]);
                    }
                    PodcastEpisode::where('prisoner_id', $dup->id)->update(['prisoner_id' => $canonical->id]);
                    CalendarEntry::where('prisoner_id', $dup->id)->update(['prisoner_id' => $canonical->id]);

                    // Backfill scalar fields on canonical from dup where canonical is empty.
                    // NOTE: 'description' is handled separately below — the two bios
                    // are concatenated rather than one being dropped, so no text is
                    // ever lost when both records carry a description.
                    $scalarFields = [
                        'photo', 'state', 'address', 'lat', 'lng',
                        'first_name', 'middle_name', 'last_name', 'race', 'gender',
                        'birthdate', 'death_date', 'era', 'website', 'twitter',
                        'facebook', 'instagram', 'inmate_number',
                    ];
                    $dirty = false;

                    // Preserve BOTH descriptions: keep the canonical's, then the
                    // duplicate's, separated by a blank line. Skip if the dup's text
                    // is empty or already contained in the canonical's (idempotent).
                    $canonDesc = trim((string) $canonical->description);
                    $dupDesc = trim((string) $dup->description);
                    if ($dupDesc !== '' && mb_stripos($canonDesc, $dupDesc) === false) {
                        $canonical->description = $canonDesc === ''
                            ? $dupDesc
                            : $canonDesc."\n\n".$dupDesc;
                        $dirty = true;
                    }
                    foreach ($scalarFields as $f) {
                        $cv = $canonical->{$f};
                        $dv = $dup->{$f};
                        if (($cv === null || $cv === '') && $dv !== null && $dv !== '') {
                            $canonical->{$f} = $dv;
                            // Partial dates carry a per-field precision; copy it
                            // across too so a backfilled birthdate/death_date
                            // still renders (the API only shows day-precision).
                            if (in_array($f, ['birthdate', 'death_date'], true)) {
                                $dupPrecision = $dup->date_precision[$f] ?? null;
                                if ($dupPrecision !== null) {
                                    $canonical->date_precision = array_merge(
                                        $canonical->date_precision ?? [],
                                        [$f => $dupPrecision],
                                    );
                                }
                            }
                            $dirty = true;
                        }
                    }

                    // Merge aka (string, slash-separated).
                    $akaParts = collect(preg_split('/\s*[\/;]\s*/', (string) $canonical->aka))
                        ->merge(preg_split('/\s*[\/;]\s*/', (string) $dup->aka))
                        ->merge([$dup->name])
                        ->map(fn ($s) => trim((string) $s))
                        ->filter()
                        ->filter(fn ($s) => mb_strtolower($s) !== mb_strtolower($canonical->name))
                        ->unique(fn ($s) => mb_strtolower($s))
                        ->values()
                        ->all();
                    $newAka = implode(' / ', $akaParts);
                    if ($newAka !== (string) $canonical->aka) {
                        $canonical->aka = $newAka === '' ? null : $newAka;
                        $dirty = true;
                    }

                    // Merge array fields (ideologies, affiliation).
                    foreach (['ideologies', 'affiliation'] as $f) {
                        $merged = collect((array) $canonical->{$f})
                            ->merge((array) $dup->{$f})
                            ->filter()
                            ->unique()
                            ->values()
                            ->all();
                        if ($merged !== (array) $canonical->{$f}) {
                            $canonical->{$f} = $merged;
                            $dirty = true;
                        }
                    }

                    // OR boolean status flags so the canonical reflects
                    // any "active" signal that lived only on the dup.
                    foreach (['in_custody', 'released', 'in_exile', 'currently_in_exile', 'awaiting_trial'] as $f) {
                        if (! $canonical->{$f} && $dup->{$f}) {
                            $canonical->{$f} = true;
                            $dirty = true;
                        }
                    }

                    if ($dirty) {
                        $canonical->save();
                    }

                    $dup->delete();
                });

                $merged++;
            }
        }

        $this->line('');
        if ($apply) {
            $this->info("Done. Merged {$merged} duplicate(s); skipped {$skipped} group(s).");
        } else {
            $this->info("Plan: {$merged} merge(s); {$skipped} group(s) skipped (missing canonical).");
            $this->info('(dry-run; re-run with --apply to write)');
        }

        return self::SUCCESS;
    }
}
