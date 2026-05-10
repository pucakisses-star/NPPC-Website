<?php

declare(strict_types=1);

/**
 * Replacement for fix_bail_then_sentenced_cases.php that uses an
 * explicit allowlist of prisoners confirmed by historical research
 * (six parallel research agents) to have been OUT ON BAIL between
 * arrest and sentencing.
 *
 * Cecily McMillan still gets her dedicated fix (she predates the
 * allowlist work). Everyone else only gets touched if their
 * lower-cased name is in $bail.
 *
 * Defaulting UNKNOWN to HELD: when an agent couldn't confirm bail
 * positively, the case stays as-is (incarceration_date = arrest
 * date). That overcounts time only if the defendant was actually
 * out on bail; it correctly counts pretrial detention if they
 * were held without bail. Conservative.
 *
 * Idempotent. --dry-run to inspect.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$dryRun = in_array('--dry-run', $argv ?? [], true);

// ---- 1. Cecily McMillan dedicated fix (confirmed bail Mar 2012 - May 2014)
$cecily = Prisoner::whereRaw('LOWER(name) = ?', ['cecily mcmillan'])->first();
if ($cecily) {
    $case = $cecily->cases()->first();
    if ($case) {
        $case->arrest_date         = '2012-03-17';
        $case->sentenced_date      = '2014-05-19';
        $case->incarceration_date  = '2014-05-19';
        $case->release_date        = '2014-07-02';
        $case->imprisoned_for_days = 44;
        if (! $dryRun) $case->save();
        echo "[fix] Cecily McMillan -> 44 days at Rikers (May-Jul 2014)\n";
    }
}

// ---- 2. BAIL allowlist (lower-cased names) -----------------------
$bail = [
    // 2020s (per regional research)
    'jarrid bailey huber','lisa melanie karlovsky','bridget shergalis','calla walsh','sophie ross',
    'bevelyn beatty williams','alexander akridgejacobs','brian cortez lightfoot jr.','bryan rivera',
    'christian martinez','faraz martin talab','jeremy white','joseph austin gaskins','ruchelle ogden',
    'daniel hale','eric brandt','jessica reznicek','patrick o\'neill',

    // 2009-2018
    'brennon j. nastacio','dane powell','michael "rattler" markus','greg boertje-obed','stephen kim',
    'erik oseland','cesar aguirre',

    // 1990s-2000s
    'lynne stewart','mohamed yousry','sherman austin','sara jane olson','douglas joshua ellerman',
    'charles liteky','frank cordaro',

    // 1980s — Sanctuary, draft resisters, plowshares, Wilkerson, Morison
    'darlene ann nicgorski','john m. fife','maría del socorro pardo viuda de aguilar','jack elder',
    'stacey lynn merkt','benjamin h. sasway','david alan wayte','enten eller','paul jacob',
    'tom h. hastings','cathy wilkerson','samuel loring morison',

    // 1947-1979 — Sterling Hall (Fine), Milwaukee 14, Boston 5, Eberhardt, Worthy, Smith Act 11
    'david fine','alfred janicke','anthony mullaney','bob graf','don cotton','doug marvy',
    'edmund basil o\'leary','fred ojile','james forest','james harney','jerry gardner',
    'jon higgenbotham','lawrence rosebaugh','michael cullen','robert cunnane',
    'benjamin spock','michael ferber','mitchell goodman','william sloane coffin',
    'david eberhardt','william worthy',
    'maurice e. travis','hugh bryson','junius scales','william lorenzo patterson','ben gold',
    'claudia jones','dorothy ray healey','elizabeth gurley flynn','oleta o\'connor yates',
    'pettis perry','william v. schneiderman',

    // pre-1947 — Smith Act NY 11, Hollywood Ten, Marzani, Hiss, Bridges, Remington, Emspak,
    // Steve Nelson, Minneapolis Trotskyist 11, Garvey, Gitlow, Whitney, Germer, SP/Berger, IWW
    // Chicago, Schenck, Debs, O'Hare, Sanger
    'julius emspak','steve nelson','william walter remington','harry bridges','alger hiss',
    'benjamin jefferson davis jr.','carl winter','gilbert green','gus hall','henry winston',
    'irving potash','jacob stachel','john gates','john williamson','robert thompson','eugene dennis',
    'adrian scott','alvah bessie','edward dmytryk','herbert biberman','john howard lawson',
    'lester cole','ring lardner jr.','samuel ornitz','carl aldo marzani',
    'albert goldman','carl skoglund','carlos hudson','farrell dobbs','felix morrow',
    'grace holmes carlson','harry deboer','jake cooper','james p. cannon','max geldman',
    'vincent raymond dunne',
    'marcus mosiah garvey jr.','benjamin gitlow','charlotte anita whitney','adolph frank germer',
    'irwin st. john tucker','j. louis engdahl','william f. kruse','rose pastor stokes','victor berger',
    'charles ashleigh','ralph hosea chaplin','vincent saint john','walter t. nef',
    'william dudley haywood', // 1917 IWW Chicago trial (Bill Haywood is separate 1906 case — HELD)
    'charles emil ruthenberg','charles t. schenck','kate richards o\'hare','eugene debs',
    'margaret sanger',
];

// ---- 3. Apply allowlist -------------------------------------------
$candidates = PrisonerCase::query()
    ->whereNotNull('arrest_date')
    ->whereNotNull('sentenced_date')
    ->whereColumn('arrest_date', '=', 'incarceration_date')
    ->whereRaw("julianday(sentenced_date) - julianday(arrest_date) > 30")
    ->with('prisoner:id,name')
    ->get();

$swept = 0;
$skippedHeld = 0;
foreach ($candidates as $c) {
    if (! $c->prisoner) continue;
    $nameLc = mb_strtolower($c->prisoner->name);

    if (! in_array($nameLc, $bail, true)) {
        $skippedHeld++;
        continue;
    }

    $oldDays  = (int) ($c->imprisoned_for_days ?? 0);
    $newStart = Carbon::parse($c->sentenced_date);
    $newEnd   = $c->release_date ? Carbon::parse($c->release_date) : Carbon::now();
    $newDays  = max(0, (int) $newStart->diffInDays($newEnd));

    if ($oldDays === $newDays && (string) $c->incarceration_date === (string) $c->sentenced_date) {
        continue; // already in correct shape
    }

    echo sprintf("  [bail] %-40s  %s -> %s, days %d -> %d\n",
        $c->prisoner->name,
        substr((string) $c->incarceration_date, 0, 10),
        $newStart->format('Y-m-d'),
        $oldDays, $newDays
    );

    if (! $dryRun) {
        $c->incarceration_date  = $newStart->format('Y-m-d');
        $c->imprisoned_for_days = $newDays;
        $c->save();
    }
    $swept++;
}

echo "\nDone. cases_updated_to_sentenced_date={$swept}, cases_left_alone_as_held={$skippedHeld}";
echo $dryRun ? " (dry-run)\n" : "\n";
