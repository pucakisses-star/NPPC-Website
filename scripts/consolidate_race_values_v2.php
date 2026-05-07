<?php

declare(strict_types=1);

/**
 * Run on production (104.238.162.40):
 *   cd /var/www/NPPC-Website && php scripts/consolidate_race_values_v2.php
 *
 * 1) "Yemeni-American" -> "Arab"
 * 2) "Latino"          -> "Latino/Hispanic"
 * 3) NULL race entries: best-effort guess from name where the signal is
 *    strong. Conservative — only sets a value when a surname or first
 *    name is unambiguously associated with one of: Latino/Hispanic,
 *    Asian, Arab. Skips Black/White/Native American because those are
 *    not reliably inferable from a name.
 *
 * The script prints every change it makes so you can audit and override
 * via /admin afterward.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// --- 1) literal-string remaps -----------------------------------------------

$remaps = [
    'Yemeni-American' => 'Arab',
    'Latino'          => 'Latino/Hispanic',
];

foreach ($remaps as $from => $to) {
    $count = Prisoner::where('race', $from)->count();
    if ($count === 0) {
        echo "  (none) {$from}\n";
        continue;
    }
    Prisoner::where('race', $from)->update(['race' => $to]);
    echo "  {$count} updated: {$from} -> {$to}\n";
}

// --- 2) NULL race name-based guesses ----------------------------------------

// Conservative high-signal lists. Lowercase compared.

$hispanicSurnames = [
    'rodriguez','garcia','martinez','lopez','hernandez','gonzalez','perez',
    'sanchez','ramirez','torres','flores','diaz','morales','reyes','cruz',
    'ortiz','gomez','vasquez','vazquez','castillo','jimenez','mendoza',
    'ruiz','alvarez','romero','vega','soto','salazar','acosta','aguilar',
    'espinoza','espinosa','maldonado','nunez','valdez','cabrera','delgado',
    'pacheco','estrada','padilla','carrillo','cervantes','galindo',
    'guerrero','marquez','mejia','ochoa','pena','rivas','salinas',
    'sandoval','trujillo','velasquez','velazquez','zamora','castro',
    'rivera','vargas','moreno','herrera','medina','aguirre','alvarado',
    'beltran','bermudez','calderon','camacho','cardenas','carmona',
    'castaneda','chavez','contreras','cortez','escobar','fernandez',
    'figueroa','fonseca','fuentes','galvez','hidalgo','iglesias','lara',
    'leon','lugo','marin','mata','miranda','molina','montano','montoya',
    'mora','munoz','murillo','nava','navarro','ortega','palacios',
    'paredes','patino','pereira','quintero','quintana','quezada','ramos',
    'rangel','rios','robles','rojas','rosas','salas','santiago','santos',
    'sepulveda','serrano','solano','solis','suarez','tovar','trejo',
    'valencia','velasco','vera','villa','villalobos','villanueva',
    'villegas','zavala','zapata','zelaya','zuniga','arroyo','barragan',
    'becerra','bonilla','briseno','caballero','campos','carbajal',
    'cardona','carrasco','casillas','centeno','colon','cordero','cordova',
    'cortes','cuevas','duran','enriquez','escamilla','esparza','espino',
    'estrella','farias','figueras','fragoso','franco','gallardo','gallegos',
    'garza','gil','godinez','grijalva','guevara','gutierrez','guzman',
    'hinojosa','huerta','ibarra','jasso','juarez','lemus','linares',
    'llamas','loya','luna','macias','madrigal','magana','manzano','marrero',
    'mata','melendez','mesa','meza','montanez','montes','montez','navarrete',
    'olivares','olivas','olvera','orellana','orozco','palomino','paniagua',
    'parra','pavia','pedraza','pelaez','pinedo','pinto','plascencia','polanco',
    'porras','prado','prieto','quiroz','razo','renteria','resendez','reynoso',
    'rincon','rocha','rosales','rosario','rubio','salcedo','salcido','samano',
    'samaniego','santana','sarmiento','sauceda','saucedo','saavedra','segura',
    'serna','sierra','silva','sosa','tafoya','tapia','tellez','toro','torres',
    'urias','urrutia','valadez','valdes','valdivia','valenzuela','vallejo',
    'varela','vasconcelos','venegas','viera','vigil','villarreal','yanez',
    'zaragoza','zarate','arellano','arenas','arrieta','arvizu','avila','ayala',
    'baca','barajas','barbosa','barragan','barreto','barrientos','barrios',
    'bautista','benitez','blanco','bocanegra','bravo','bueno','burgos',
    'caicedo','campa','canales','candelaria','canizales','carballo','cardoza',
    'caro','carranza','carrera','carrion','casanova','castellanos','castelan',
    'castelar','castrejon','catalan','cazares','ceja','celaya','cepeda',
    'cervantez','chacon','cifuentes','cobos','collazo','consuegra','corpus',
    'corral','correa','corona','corrales','cortinas','cuellar','cuesta',
];

$southAsianSurnames = [
    'patel','singh','sharma','gupta','reddy','naidu','kapoor','chopra',
    'iyer','iyengar','rao','bhatt','bhattacharya','mukherjee','banerjee',
    'chakraborty','chatterjee','ganguly','sengupta','bose','saha','joshi',
    'varma','verma','desai','mehta','agarwal','aggarwal','agrawal','arora',
    'malhotra','sinha','das','dutta','dey','ghosh','sen','kumar','prasad',
    'srinivasan','venkat','venkatesan','venkataraman','subramaniam',
    'subramanian','krishnamurthy','krishnan','natarajan','rajagopal',
    'ramaswamy','ramesh','suresh','dinesh','mahesh','rajan','raghavan',
    'pillai','nair','menon','panicker','kurup','warrier','namboodiri',
    'shenoy','kamath','shetty','hegde','bhat','pai','prabhu','gowda',
    'mallya','rajappa','vaidya','sanghvi','shroff','contractor','dastur',
    'gandhi','nehru','tagore','bhattarai','adhikari','poudel','thapa',
    'gurung','sherpa','tamang','rai','limbu','khatri','pandey','dixit',
    'tiwari','trivedi','dwivedi','chaturvedi','upadhyay','mishra',
    'srivastava','saxena','rastogi','bansal','goyal','jindal','mittal',
    'singhal','aggarwal','garg','khandelwal','maheshwari','poddar','kothari',
    'rathi','jain','mahajan','bajaj','khanna','sethi','anand','bhasin',
    'bhalla','batra','bedi','bhandari','chadha','chawla','dhillon','dua',
    'duggal','gill','grover','jagota','jolly','kohli','lamba','luthra',
    'malik','marwah','mehra','minhas','nanda','narang','nayyar','oberoi',
    'puri','sablok','sabharwal','sahni','sapra','sawhney','seth','sibal',
    'sodhi','soni','sood','suri','tandon','thapar','tuli','uppal','vohra',
    'walia','wadhwa','wahi','seetharaman','ramanathan','ramachandran',
    'parameswaran','sundaresan','swaminathan','vaidyanathan',
];

$eastAsianSurnames = [
    // Vietnamese
    'nguyen','tran','pham','hoang','vo','phan','bui','dang','duong','dinh',
    'doan','luong','luu','ly','mai','ngo','ninh','quach','thai','thieu',
    'tieu','tong','trinh','truong','vu','vuong','huynh',
    // Japanese
    'yamamoto','tanaka','suzuki','sato','watanabe','ito','takahashi',
    'nakamura','kobayashi','yoshida','yamada','sasaki','yamaguchi',
    'matsumoto','inoue','kimura','hayashi','shimizu','ikeda','hashimoto',
    'yamazaki','mori','abe','ogawa','ishikawa','maeda','fujita','goto',
    'okada','hasegawa','ishii','murakami','kondo','sakamoto','endo',
    'aoki','fujii','nishimura','fukuda','ota','miura','fujiwara','okamoto',
    'matsuda','nakagawa','nakajima','nakano','harada','ono','tamura',
    'takeuchi','nakata','yokoyama','arai','kaneko','takeda','noguchi',
    'matsui','kikuchi','sugiyama','imai','takagi','hirano','uchida',
    'andoh','ando','miyamoto','iwasaki','sakai','komatsu','konno',
    'shibata','akiyama','onishi','iida','furukawa','tsuchiya','nishida',
    'kuroda','minami','iwata','baba','iguchi','noda','toyama','katayama',
    // Chinese (very high-signal romanizations only)
    'zhang','wang','liu','chen','yang','huang','zhao','zhou','wu','xu',
    'sun','hu','zhu','lin','he','guo','ma','luo','liang','song','tang',
    'han','feng','deng','cao','peng','xiao','tian','ren','jiang','dong',
    'shi','xie','jin','duan','xie','xia','fan','fang','fu','gao','jia',
    'kong','lai','lei','li','meng','mou','niu','pan','qian','qiu','ren',
    'shen','shi','shu','su','tao','wei','xiang','xin','xue','yan','yao',
    'ye','yu','yuan','zeng','zheng','zhuang',
    // Korean (high-signal — though many also overlap with Chinese in pinyin)
    'kim','park','choi','jung','jeong','kang','cho','yoon','jang','lim',
    'oh','seo','hwang','ahn','yoo','yu','baek','ryu','noh','moon',
    // Filipino (Tagalog)
    'reyes','cruz','dela','santos','garcia','ramos','aquino','mendoza',
    'bautista','flores','gonzales','rivera','rosales','dimagiba','natividad',
    'pangilinan','panganiban',
];

$arabFirstNames = [
    'mohammed','muhammad','mohamed','mohammad','ahmad','ahmed','hassan',
    'hasan','hussein','hussain','omar','umar','khalid','khaled','abdullah',
    'abdulla','yousef','youssef','yusuf','yousif','fatima','fatimah',
    'aisha','aysha','ayesha','mahmoud','mahmood','said','sayed','sayyid',
    'ibrahim','karim','kareem','rashid','rasheed','rahman','aziz','tariq',
    'tareq','bilal','faisal','feisal','hisham','jamal','marwan','ramzi',
    'sami','tarek','yasser','yasir','adel','bakr','salim','saleem','salah',
    'wael','walid','waleed','rami','sherif','sharif','samir','samira',
    'nour','noura','mariam','maryam','khadija','zainab','zaynab','laila',
    'leila','huda','salma','nadia','rania','dina','reem','dalia','nora',
    'amira','samia','amal','iman','imani','farah','hala','heba','yara',
    'zahra','zara','zeinab','sara','aya','jenna','dana','mona','suha',
    'nahla','ranya','manar','siham','hadeel','randa','mai','rasha','rola',
    'lina','aliya','najwa','jihad','firas','tamer','tamim','majd','majid',
    'majeed','mansour','mansoor','nabil','nasir','nasser','nizar','osama',
    'usama','rafiq','raed','raid','rashed','riad','riyad','rifat','sabri',
    'safwat','salman','sameh','samih','sayyid','seif','sef','shadi','shadid',
    'sufyan','suleiman','sulaiman','tahir','talal','talib','wajdi','yahya',
    'yacoub','yaqub','zaid','zayd','ziad','ziyad',
];

$arabPrefixes = ['al-', 'el-', 'al ', 'el ', 'abu ', 'abu-', 'abd ', 'abd-', 'abdel-', 'abdul-', 'abdul ', 'abdal-', 'ibn '];

$arabKeywords = [
    'abdulrahman','abdurrahman','abdulrahim','abdurrahim','abdullatif',
    'abdulhamid','abdulhakim','abdulkarim','abdulmajid','abdulaziz',
    'abdelaziz','abdelfattah','abdelhakim','abdelkarim','abdelmajid',
    'abdelrahman','abdelrahim','abdelsalam','abdul','abdel',
];

$slavicSuffixes = ['ski', 'sky', 'cki', 'czyk', 'enko', 'chuk', 'shvili', 'adze'];
$greekSuffixes = ['opoulos', 'opulos', 'akis', 'idis', 'ides', 'eas'];
$italianSuffixes = ['elli', 'etti', 'ini', 'ello', 'occhi', 'occi'];

function classify(string $name, array $opts): ?string
{
    $lower = mb_strtolower(trim($name));
    $lower = str_replace(['é','á','í','ó','ú','ñ','ü','ç','ý','ä','ö','ø','å','ã','õ'],
                        ['e','a','i','o','u','n','u','c','y','a','o','o','a','a','o'], $lower);

    // Tokenise on whitespace and hyphens
    $parts = preg_split('/[\s\-,]+/', $lower) ?: [];
    $parts = array_values(array_filter($parts, fn ($p) => $p !== ''));
    if (empty($parts)) {
        return null;
    }

    $first = $parts[0];
    $last  = end($parts);

    // Arab — prefix on full name
    foreach ($opts['arabPrefixes'] as $pfx) {
        if (str_contains($lower, $pfx)) {
            return 'Arab';
        }
    }
    // Arab — first-name signal
    if (in_array($first, $opts['arabFirstNames'], true)) {
        return 'Arab';
    }
    // Arab — any token matches first-names list (e.g. middle name "Mohammed")
    foreach ($parts as $p) {
        if (in_array($p, $opts['arabFirstNames'], true)) {
            return 'Arab';
        }
        if (in_array($p, $opts['arabKeywords'], true)) {
            return 'Arab';
        }
        foreach ($opts['arabKeywords'] as $kw) {
            if (str_starts_with($p, $kw)) {
                return 'Arab';
            }
        }
    }

    // Hispanic — surname or any token in list
    if (in_array($last, $opts['hispanicSurnames'], true)) {
        return 'Latino/Hispanic';
    }
    foreach ($parts as $p) {
        if (in_array($p, $opts['hispanicSurnames'], true)) {
            return 'Latino/Hispanic';
        }
    }

    // Asian — surname match (East/South Asian, Vietnamese, Filipino, etc.)
    if (in_array($last, $opts['southAsianSurnames'], true)) {
        return 'Asian';
    }
    if (in_array($first, $opts['eastAsianSurnames'], true)) {
        // East Asian names sometimes given surname-first (Chen Wei).
        return 'Asian';
    }
    if (in_array($last, $opts['eastAsianSurnames'], true)) {
        return 'Asian';
    }

    // White — distinctive Slavic/Greek/Italian suffixes
    foreach ($opts['slavicSuffixes'] as $sfx) {
        if (str_ends_with($last, $sfx) && strlen($last) > strlen($sfx) + 2) {
            return 'White';
        }
    }
    foreach ($opts['greekSuffixes'] as $sfx) {
        if (str_ends_with($last, $sfx) && strlen($last) > strlen($sfx) + 2) {
            return 'White';
        }
    }
    foreach ($opts['italianSuffixes'] as $sfx) {
        if (str_ends_with($last, $sfx) && strlen($last) > strlen($sfx) + 2) {
            return 'White';
        }
    }

    return null;
}

$opts = compact(
    'hispanicSurnames','southAsianSurnames','eastAsianSurnames',
    'arabFirstNames','arabPrefixes','arabKeywords',
    'slavicSuffixes','greekSuffixes','italianSuffixes'
);

echo "\nNULL race entries — best-effort name classification:\n";
$nulls = Prisoner::whereNull('race')->orWhere('race', '')->get(['id','name']);
$classified = ['Arab'=>0,'Latino/Hispanic'=>0,'Asian'=>0,'White'=>0];
$skipped = 0;
foreach ($nulls as $p) {
    if (!$p->name) {
        $skipped++;
        continue;
    }
    $guess = classify($p->name, $opts);
    if ($guess === null) {
        $skipped++;
        echo "  SKIP   {$p->name}\n";
        continue;
    }
    $p->race = $guess;
    $p->save();
    $classified[$guess]++;
    echo "  " . str_pad($guess, 17) . $p->name . "\n";
}

echo "\nBy guessed race:\n";
foreach ($classified as $race => $n) {
    echo "  " . str_pad($race, 17) . $n . "\n";
}
echo "  " . str_pad('(skipped)', 17) . $skipped . "\n";

echo "\nFinal race counts:\n";
$rows = Prisoner::select('race', \DB::raw('count(*) as c'))
    ->groupBy('race')
    ->orderByDesc('c')
    ->get();
foreach ($rows as $r) {
    echo "  " . str_pad($r->race ?? '(null)', 30) . $r->c . "\n";
}

echo "\nDone.\n";
