<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Fills race = "White" for prisoners with no race set who have a European
 * surname and NO indication of another race/ethnicity.
 *
 * Surname-based race inference is unreliable (many Americans of other
 * backgrounds carry European surnames), so this is heavily guarded — it does
 * NOT set White when:
 *   - the name/description/ideologies/affiliation contain any non-white ethnic,
 *     racial, national, or movement signal (Black, Puerto Rican, Chican@,
 *     Indigenous, Asian, Arab/MENA, Latin Kings, Nation of Islam, …);
 *   - the FIRST name is strongly Hispanic, Middle-Eastern/Muslim, South/East
 *     Asian, or a name strongly associated with Black Americans;
 *   - a name token is a recognized Hispanic / Asian / Arab / African surname
 *     (or a Spanish "-ez" surname).
 * Everything caught by a guard is left unset (never guessed). Existing races
 * are never changed. Run with --dry to preview, --show-skipped to list.
 */
final class InferWhiteRace extends Command
{
    protected $signature = 'prisoners:infer-white-race {--dry : Preview without writing} {--show-skipped : List records left unset}';

    protected $description = 'Set race=White for no-race prisoners with a European surname and no non-white indication';

    /** Text signals (regex, case-insensitive) that block a White inference. */
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

    private const HISP_FN = 'jose juan jesus francisco javier alejandro alejandra julio cesar ernesto rafael roberto ricardo eduardo fernando pedro pablo hector raul ramon maricela guadalupe alfredo armando arturo enrique felipe gerardo gonzalo ignacio joaquin lorenzo mauricio rodrigo rogelio salvador santiago saul sebastian tomas ulises yolanda gabriela rosario mercedes consuelo dolores esperanza rocio marisol lupe alba adriana beatriz carmen elena cristina yesenia diego emilio esteban horacio jaime leonel miguel moises nestor octavio osvaldo rigoberto ruben teodoro wilfredo nydia mireya migdalia gamaly marlon';

    private const MENA_FN = 'mohammed muhammad mohamed ahmed ahmad ali hassan hussein hussain khalid faraz amin nassim tariq yusuf yousef youssef ibrahim ismail mustafa mahmoud karim rashid samir nabil hamid bilal hamza saeed reza abbas hadi mehdi khalil fadi walid ziad rami wael tarek osama omar mansour anwar bassam ghassan imad marwan nizar yaqub zaid zayd sami nadia yasmin layla fatima aisha zainab yagmur emine ayse mehmet ahmet kamile';

    private const SASIAN_FN = 'aditya arjun rahul amit anil sanjay rajesh vijay sunil deepak ashok ravi suresh manoj vikram krishna gopal harish naveen prakash pranav rohit sandeep aswani priya anjali kavya lakshmi';

    private const EASIAN_FN = 'wei jin ming hao yun feng lei jun bo xin yue teng kai jian ling mei hui yong hyun jae min seung jung woo sung hiro kenji takashi akira yuki haruki';

    private const BLACK_FN = 'jamal jamaal jamar jamel jaylen jaylin deshawn deshaun darnell tyrone lamar deandre tyrese rakim rakem keisha latoya latonya shanice tamika marquis tremaine jermaine yonte tyshawn daquan jaquan raekwon shaquille aaliyah imani dashawn';

    private const HISP_SN = 'garcia hernandez martinez lopez gonzalez gonzales rodriguez perez sanchez ramirez torres flores rivera gomez diaz reyes morales cruz ortiz gutierrez chavez ramos ruiz alvarez mendoza vasquez vazquez castillo jimenez moreno romero herrera medina aguilar vargas guerrero rojas munoz delgado pena rios alvarado sandoval castro ortega nunez dominguez guzman navarro figueroa mejia molina contreras salazar espinoza aguirre juarez cabrera vega leon campos vera acosta soto padilla suarez cortez cortes marquez rosales cervantes robles bautista carrillo velasquez maldonado velasco palacios trejo cardenas fuentes zamora ibarra pacheco montes andrade avila galvan alfaro serrano galindo mora valdez valdes valencia lara benitez ochoa duran zavala fonseca escobar orozco meza cisneros tapia pineda estrada quintero cordova cordero santana bonilla arias caballero santos escobedo felix gallardo gallegos guevara hidalgo huerta ledesma linares luna macias magana marin montoya olivares ordonez paz portillo prieto pulido quintana rangel renteria robledo salinas saldana salgado tellez tovar trujillo uribe valle villa villalobos villanueva villarreal zapata zaragoza zepeda carranza casillas cuellar deleon escamilla esparza esquivel franco lozano madrigal mata ocampo ponce quiroz regalado reyna rivas rocha rosas sepulveda sierra solano solis sosa sotelo toledo trevino ulloa varela ventura vigil zambrano zarate zuniga corona coronado nieto betancourt amaya arreola cazares galarza guerra nava peralta ceja tejeda velez montero calderon carrasco melo rueda bracamonte orellana oropesa osorio pizarro bustamante barragan camacho carbajal escalante gallo lugo maciel marrero najera olvera pichardo quezada rendon sarmiento urias valdivia vento zelaya anaya arana caceres cadena cepeda chaparro echeverria giron loera navas pantoja rascon rosado saldivar samayoa tercero urbina villagran';

    private const ASIAN_SN = 'nguyen tran pham huynh hoang phan vu vo dang bui do ho ngo duong ly truong dinh lam kim park choi jung kang cho yoon jang lim han oh seo shin kwon hwang ahn song bae yu chen wang zhang liu yang huang zhao wu zhou xu sun zhu hu guo lin gao luo zheng liang xie tang cao deng feng zeng peng xiao wong chan chang cheng chiang tsai chu yee shen shi dong yamamoto tanaka sato suzuki takahashi watanabe ito nakamura kobayashi kato yoshida yamada sasaki matsumoto inoue kimura hayashi shimizu yamaguchi mori abe ikeda hashimoto ishikawa okada fujiwara ogawa nakagawa saito patel singh sharma kumar gupta rao reddy nair mehta desai agarwal chowdhury das banerjee mukherjee iyer pillai chatterjee aswani lee';

    private const ARAB_SN = 'mohammed muhammad ahmed ahmad hassan hussein hussain khan rahman abdullah abdallah mahmoud mustafa ibrahim ismail yousef youssef yusuf osman farah saleh nasser haddad said saeed karim rashid tariq nour khoury sayed sultan darwish nassar mansour saab jaber salem hamdan awad odeh masri qureshi siddiqui malik akhtar aziz talab baghdadi zerriffi chaoui dincsoy hodzic aslan yilmaz demir kaya celik ozturk arslan dogan sahin cetin koc kurt aydin ozdemir hamza farhan zaidi rizvi naqvi abbasi haidar jafari mousavi hosseini karimi rahimi ansari';

    private const AFRI_SN = 'okafor okonkwo adebayo adeyemi okoro eze nwosu chukwu obi mensah asante osei owusu boateng diallo traore keita toure cisse ndiaye mbeki dlamini nkosi achebe abebe tesfaye mwangi kamau otieno afolabi balogun nwachukwu mutombo obasi olawale chukwuma ademola';

    public function handle(): int
    {
        $sig = '/('.implode('|', self::TEXT_SIGNALS).')/i';
        $hispFn = array_flip(explode(' ', self::HISP_FN));
        $menaFn = array_flip(explode(' ', self::MENA_FN));
        $sasianFn = array_flip(explode(' ', self::SASIAN_FN));
        $easianFn = array_flip(explode(' ', self::EASIAN_FN));
        $blackFn = array_flip(explode(' ', self::BLACK_FN));
        $hispSn = array_flip(explode(' ', self::HISP_SN));
        $asianSn = array_flip(explode(' ', self::ASIAN_SN));
        $arabSn = array_flip(explode(' ', self::ARAB_SN));
        $afriSn = array_flip(explode(' ', self::AFRI_SN));

        $dry = (bool) $this->option('dry');
        $set = 0;
        $skipped = [];

        Prisoner::withUnderReview()
            ->where(fn ($q) => $q->whereNull('race')->orWhere('race', ''))
            ->orderBy('name')
            ->chunkById(500, function ($rows) use (
                $sig, $hispFn, $menaFn, $sasianFn, $easianFn, $blackFn,
                $hispSn, $asianSn, $arabSn, $afriSn, $dry, &$set, &$skipped
            ) {
                foreach ($rows as $p) {
                    $reason = $this->classify(
                        $p, $sig, $hispFn, $menaFn, $sasianFn, $easianFn, $blackFn,
                        $hispSn, $asianSn, $arabSn, $afriSn
                    );

                    if ($reason !== null) {
                        $skipped[] = [$p->name, $reason];

                        continue;
                    }

                    if (! $dry) {
                        Prisoner::withUnderReview()->whereKey($p->getKey())->update(['race' => 'White']);
                    }
                    $set++;
                }
            });

        $verb = $dry ? 'Would set' : 'Set';
        $this->info("{$verb} race=White on {$set}. Left unset: ".count($skipped).'.');

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

        if (! $dry && $set > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        return self::SUCCESS;
    }

    /** Return a skip-reason string, or null if the record should be set White. */
    private function classify(
        Prisoner $p, string $sig, array $hispFn, array $menaFn, array $sasianFn,
        array $easianFn, array $blackFn, array $hispSn, array $asianSn, array $arabSn, array $afriSn
    ): ?string {
        $parts = [(string) $p->name, (string) $p->description];
        foreach (['ideologies', 'affiliation'] as $k) {
            $v = $p->{$k};
            $parts[] = is_array($v) ? implode(' ', $v) : (string) $v;
        }
        $hay = strtolower(implode(' ', $parts));

        if (preg_match($sig, $hay)) {
            return 'signal';
        }

        $tokens = preg_split('/\s+/', trim((string) $p->name)) ?: [];
        $norm = fn ($t) => preg_replace('/[^a-z]/', '', strtolower($t));

        $first = (string) $p->first_name !== '' ? $norm($p->first_name) : $norm($tokens[0] ?? '');
        if ($first !== '') {
            if (isset($hispFn[$first])) {
                return 'hispanic-firstname';
            }
            if (isset($menaFn[$first])) {
                return 'mena-firstname';
            }
            if (isset($sasianFn[$first])) {
                return 'sasian-firstname';
            }
            if (isset($easianFn[$first])) {
                return 'easian-firstname';
            }
            if (isset($blackFn[$first])) {
                return 'black-firstname';
            }
        }

        $rest = array_slice($tokens, 1);
        if (empty($rest)) {
            return 'no-surname';
        }
        foreach ($rest as $t) {
            $sn = $norm($t);
            if ($sn === '') {
                continue;
            }
            if (isset($hispSn[$sn]) || (strlen($sn) > 4 && str_ends_with($sn, 'ez'))) {
                return 'hispanic-surname';
            }
            if (isset($asianSn[$sn])) {
                return 'asian-surname';
            }
            if (isset($arabSn[$sn])) {
                return 'arab-surname';
            }
            if (isset($afriSn[$sn])) {
                return 'african-surname';
            }
        }

        return null; // → set White
    }
}
