<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Fills the race field for prisoners with no race set, inferring from surname
 * and name context:
 *   - a recognized Hispanic surname (or Spanish "-ez")  -> Hispanic
 *   - a recognized East/South-Asian surname             -> Asian
 *   - a recognized Arab / Middle-Eastern / Turkish name -> Middle Eastern
 *   - a recognized sub-Saharan African surname          -> Black
 *   - otherwise a European surname with no non-white
 *     indication                                        -> White
 *
 * Race is assigned only from SURNAMES (distinctive) — never from first names,
 * which are too cross-ethnic in this dataset (e.g. "Roberto" is Italian as
 * often as Spanish). Records are left UNSET (never guessed) when:
 *   - the name/description/ideologies/affiliation carry a non-white ethnic,
 *     national, or movement signal (too broad to assign a specific race from);
 *   - the FIRST name is strongly Hispanic / MENA / Asian / Black-associated
 *     (ambiguous for a positive call — flagged for manual review);
 *   - the surname is ambiguous (e.g. "Lee") or there is no surname.
 * Existing races are never changed. Run with --dry / --show-skipped.
 */
final class InferRace extends Command
{
    protected $signature = 'prisoners:infer-race {--dry : Preview without writing} {--show-skipped : List records left unset}';

    protected $description = 'Infer race (White/Hispanic/Asian/Middle Eastern/Black) from surname for no-race prisoners';

    /** Nobiliary / connective particles to ignore when scanning surname tokens. */
    private const PARTICLES = ['de', 'del', 'la', 'las', 'los', 'di', 'da', 'dal', 'van', 'von', 'der', 'den', 'ter', 'le', 'du', 'dos', 'das', 'mac', 'san', 'st'];

    private const TEXT_SIGNALS = [
        'black', 'negro', 'colored', 'afro', 'african', 'new afrika', 'puerto ric', 'boricua', 'nuyorican',
        'latin[oax]', 'latin king', 'hispanic', 'chican[oa]', 'mexican', 'salvador', 'guatemal', 'hondur',
        'dominican', 'cuban', 'nicaragu', 'colombian', 'venezuel', 'ecuador', 'peruvian', 'boliv', 'chilean',
        'argentin', 'brazil', 'haitian', 'jamaican', 'caribbean', 'trinidad', '\bnetas\b', 'ms-13', 'mara salvatrucha',
        'indigenous', 'native american', 'american indian', 'first nations', 'navajo', '\bdine\b', 'lakota',
        '\bdakota\b', '\bsioux\b', 'apache', 'cherokee', 'mohawk', 'seminole', '\bhopi\b', 'ojibwe', 'anishinaabe',
        'cheyenne', 'comanche', '\bpueblo\b', '\btribe\b', 'tribal', 'reservation', 'weelaunee',
        'asian', 'chinese', 'japanese', '\bnisei\b', '\bissei\b', 'korean', 'filipin', 'vietnamese', 'cambodian',
        '\bhmong\b', 'laotian', '\bthai\b', 'taiwan', 'south asian', 'pakistan', 'bangladesh', 'sri lankan',
        '\barab', 'middle eastern', 'iranian', 'iraqi', 'palestin', 'syrian', 'egyptian', 'yemeni', '\bsaudi',
        'lebanese', '\bkurd', 'somali', 'ethiopian', 'eritrean', 'nigerian', 'ghanaian', 'sudanese', 'moroccan',
        'algerian', 'tunisian', 'turkish', 'bosnian', 'afghan',
        'black panther', 'black liberation', 'black power', 'nation of islam', 'moorish', 'black nationalis',
        '\bnaacp\b', 'brown beret', 'young lords', 'la raza', 'aztlan',
    ];

    // First names too cross-ethnic to assign a race from, but non-Anglo enough
    // that we should not assert White either — these records are left unset.
    private const FLAG_FN = 'jose juan jesus francisco javier alejandro alejandra julio cesar rafael roberto ricardo eduardo fernando pedro pablo hector raul ramon maricela guadalupe alfredo armando arturo enrique felipe gerardo gonzalo ignacio joaquin lorenzo mauricio rodrigo rogelio salvador santiago santiago sebastian tomas ulises gabriela rosario consuelo esperanza rocio marisol lupe adriana diego emilio esteban horacio jaime leonel miguel octavio osvaldo rigoberto teodoro wilfredo nydia mireya migdalia gamaly refugio ismael augusto placido librado mohammed muhammad mohamed ahmed ahmad ali hassan hussein hussain khalid faraz amin nassim tariq yusuf yousef youssef ibrahim ismail mustafa mahmoud karim rashid nabil hamid bilal hamza saeed reza abbas hadi mehdi khalil fadi walid ziad rami wael tarek osama mansour anwar bassam ghassan imad marwan nizar yaqub zaid zayd yasmin layla fatima aisha zainab yagmur emine ayse mehmet ahmet kamile aditya arjun rahul amit anil sanjay rajesh vijay sunil deepak ashok ravi suresh manoj vikram krishna gopal harish naveen prakash pranav rohit sandeep aswani priya anjali kavya lakshmi jamal jamaal jamar jamel jaylen jaylin deshawn deshaun darnell tyrone lamar deandre tyrese rakim rakem keisha latoya latonya shanice tamika marquis tremaine jermaine yonte tyshawn daquan jaquan raekwon shaquille aaliyah imani dashawn';

    private const HISP_SN = 'garcia hernandez martinez lopez gonzalez gonzales rodriguez perez sanchez ramirez torres flores rivera gomez diaz reyes morales cruz ortiz gutierrez chavez ramos ruiz alvarez mendoza vasquez vazquez castillo jimenez moreno romero herrera medina aguilar vargas guerrero rojas munoz delgado pena rios alvarado sandoval castro ortega nunez dominguez guzman navarro figueroa mejia molina contreras salazar espinoza aguirre juarez cabrera vega leon campos vera acosta soto padilla suarez cortez cortes marquez rosales cervantes robles bautista carrillo velasquez maldonado velasco palacios trejo cardenas fuentes zamora ibarra pacheco montes andrade avila galvan alfaro serrano galindo mora valdez valdes valencia lara benitez ochoa duran zavala fonseca escobar orozco meza cisneros tapia pineda estrada quintero cordova cordero santana bonilla arias caballero santos escobedo felix gallardo gallegos guevara hidalgo huerta ledesma linares luna macias magana marin montoya olivares ordonez portillo prieto pulido quintana rangel renteria robledo salinas saldana salgado tellez tovar trujillo uribe valle villa villalobos villanueva villarreal zapata zaragoza zepeda carranza casillas cuellar deleon escamilla esparza esquivel franco lozano madrigal mata ocampo ponce quiroz regalado reyna rivas rocha rosas sepulveda sierra solano solis sosa sotelo toledo trevino ulloa varela ventura vigil zambrano zarate zuniga corona coronado nieto betancourt amaya arreola cazares galarza guerra nava peralta ceja tejeda velez montero calderon carrasco melo rueda bracamonte orellana oropesa osorio pizarro bustamante barragan camacho carbajal escalante gallo lugo maciel marrero najera olvera pichardo quezada rendon sarmiento urias valdivia vento zelaya anaya arana caceres cadena cepeda chaparro echeverria giron loera navas pantoja rascon rosado saldivar samayoa tercero urbina villagran';

    private const ASIAN_SN = 'nguyen tran pham huynh hoang phan dang bui duong truong dinh lam kim park choi jung kang cho yoon jang lim han seo shin kwon hwang ahn song bae chen wang zhang liu yang huang zhao zhou sun zhu guo lin gao luo zheng liang xie tang cao deng zeng peng xiao wong chan chang cheng chiang tsai chu yee shen dong yamamoto tanaka sato suzuki takahashi watanabe ito nakamura kobayashi kato yoshida yamada sasaki matsumoto inoue kimura hayashi shimizu yamaguchi mori abe ikeda hashimoto ishikawa okada fujiwara ogawa nakagawa saito patel singh sharma kumar gupta rao reddy nair mehta desai agarwal chowdhury das banerjee mukherjee iyer pillai chatterjee aswani';

    private const ARAB_SN = 'mohammed muhammad ahmed ahmad hassan hussein hussain khan rahman abdullah abdallah mahmoud mustafa ibrahim ismail yousef youssef yusuf osman farah saleh nasser haddad said saeed karim rashid tariq nour khoury sayed sultan darwish nassar mansour saab jaber salem hamdan awad odeh masri qureshi siddiqui akhtar aziz talab baghdadi zerriffi chaoui dincsoy hodzic aslan yilmaz demir kaya celik ozturk arslan dogan sahin cetin koc kurt aydin ozdemir hamza farhan zaidi rizvi naqvi abbasi haidar jafari mousavi hosseini karimi rahimi ansari';

    private const AFRI_SN = 'okafor okonkwo adebayo adeyemi okoro eze nwosu chukwu obi mensah asante osei owusu boateng diallo traore keita toure cisse ndiaye mbeki dlamini nkosi achebe abebe tesfaye mwangi kamau otieno afolabi balogun nwachukwu mutombo obasi olawale chukwuma ademola';

    public function handle(): int
    {
        $sig = '/('.implode('|', self::TEXT_SIGNALS).')/i';
        $flagFn = array_flip(explode(' ', self::FLAG_FN));
        $hispSn = array_flip(explode(' ', self::HISP_SN));
        $asianSn = array_flip(explode(' ', self::ASIAN_SN));
        $arabSn = array_flip(explode(' ', self::ARAB_SN));
        $afriSn = array_flip(explode(' ', self::AFRI_SN));
        $particles = array_flip(self::PARTICLES);

        $dry = (bool) $this->option('dry');
        $byRace = [];
        $skipped = [];

        Prisoner::withUnderReview()
            ->where(fn ($q) => $q->whereNull('race')->orWhere('race', ''))
            ->orderBy('name')
            ->chunkById(500, function ($rows) use (
                $sig, $flagFn, $hispSn, $asianSn, $arabSn, $afriSn, $particles, $dry, &$byRace, &$skipped
            ) {
                foreach ($rows as $p) {
                    [$race, $reason] = $this->classify($p, $sig, $flagFn, $hispSn, $asianSn, $arabSn, $afriSn, $particles);

                    if ($race === null) {
                        $skipped[] = [$p->name, $reason];

                        continue;
                    }

                    if (! $dry) {
                        Prisoner::withUnderReview()->whereKey($p->getKey())->update(['race' => $race]);
                    }
                    $byRace[$race] = ($byRace[$race] ?? 0) + 1;
                }
            });

        $verb = $dry ? 'Would set' : 'Set';
        ksort($byRace);
        foreach ($byRace as $race => $n) {
            $this->info("{$verb} {$race}: {$n}");
        }
        $this->line('Left unset: '.count($skipped).'.');

        $byReason = [];
        foreach ($skipped as [$n, $r]) {
            $byReason[$r] = ($byReason[$r] ?? 0) + 1;
        }
        ksort($byReason);
        foreach ($byReason as $r => $n) {
            $this->line("  unset [{$r}]: {$n}");
        }

        if ($this->option('show-skipped')) {
            $this->line('');
            foreach ($skipped as [$n, $r]) {
                $this->line("  [{$r}] {$n}");
            }
        } else {
            $this->comment('  (run with --show-skipped to list every unset record)');
        }

        if (! $dry && ! empty($byRace)) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        return self::SUCCESS;
    }

    /** @return array{0: string|null, 1: string} [race|null, reason] */
    private function classify(
        Prisoner $p, string $sig, array $flagFn, array $hispSn, array $asianSn,
        array $arabSn, array $afriSn, array $particles
    ): array {
        $parts = [(string) $p->name, (string) $p->description];
        foreach (['ideologies', 'affiliation'] as $k) {
            $v = $p->{$k};
            $parts[] = is_array($v) ? implode(' ', $v) : (string) $v;
        }
        if (preg_match($sig, strtolower(implode(' ', $parts)))) {
            return [null, 'signal'];
        }

        $tokens = preg_split('/\s+/', trim((string) $p->name)) ?: [];
        $norm = fn ($t) => preg_replace('/[^a-z]/', '', strtolower($t));
        $rest = array_slice($tokens, 1);

        // Race is assigned from surname tokens only (distinctive).
        foreach ($rest as $t) {
            $sn = $norm($t);
            if (strlen($sn) < 3 || isset($particles[$sn])) {
                continue;
            }
            if ($sn === 'lee') {
                return [null, 'ambiguous-surname'];
            }
            if (isset($hispSn[$sn]) || (strlen($sn) > 4 && str_ends_with($sn, 'ez'))) {
                return ['Hispanic', 'hispanic-surname'];
            }
            if (isset($asianSn[$sn])) {
                return ['Asian', 'asian-surname'];
            }
            if (isset($arabSn[$sn])) {
                return ['Middle Eastern', 'arab-surname'];
            }
            if (isset($afriSn[$sn])) {
                return ['Black', 'african-surname'];
            }
        }

        // Cross-ethnic first name → leave unset for manual review.
        $first = (string) $p->first_name !== '' ? $norm($p->first_name) : $norm($tokens[0] ?? '');
        if ($first !== '' && isset($flagFn[$first])) {
            return [null, 'firstname-flag'];
        }

        if (empty($rest)) {
            return [null, 'no-surname'];
        }

        return ['White', 'european'];
    }
}
