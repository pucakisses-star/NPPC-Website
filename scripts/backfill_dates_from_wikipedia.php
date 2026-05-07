<?php

declare(strict_types=1);

/**
 * Search Wikipedia + Wikidata for every prisoner missing a birthdate or
 * death_date and fill in matches that pass a context-keyword check.
 *
 * Usage:
 *   php scripts/backfill_dates_from_wikipedia.php                  # dry-run
 *   php scripts/backfill_dates_from_wikipedia.php --apply          # write
 *   php scripts/backfill_dates_from_wikipedia.php --limit=50       # cap
 *   php scripts/backfill_dates_from_wikipedia.php --name="Maduro"  # filter
 *   php scripts/backfill_dates_from_wikipedia.php --min-kw=2       # stricter
 *
 * For each prisoner with a NULL birthdate or NULL death_date the script:
 *   1. Searches en.wikipedia.org for the name.
 *   2. Walks the top hits, fetches Wikidata via pageprops, requires
 *      P31 = Q5 (instance-of human).
 *   3. Reads P569 (date of birth) and P570 (date of death).
 *   4. Pulls the article's intro extract and checks how many context
 *      terms (state, era, ideology, affiliation, words like "convicted")
 *      appear. Articles with zero hits are reported but never auto-applied.
 *   5. Applies DOB/DOD only when the field is currently NULL — never
 *      overwrites existing values.
 *
 * Conservative by default. Always review the dry-run output before --apply.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use Illuminate\Support\Facades\Http;

$opts = getopt('', ['apply', 'limit::', 'name::', 'min-kw::']);
$apply = isset($opts['apply']);
$limit = isset($opts['limit']) ? (int) $opts['limit'] : 0;
$nameFilter = $opts['name'] ?? null;
$minKw = isset($opts['min-kw']) ? (int) $opts['min-kw'] : 1;

$ua = 'NPPC-Website-DateBackfill/1.0 (https://nppcoalition.org; admin@nppcoalition.org)';

$query = Prisoner::query()
    ->where(function ($q) {
        $q->whereNull('birthdate')->orWhereNull('death_date');
    });
if ($nameFilter) {
    $query->where('name', 'like', '%' . $nameFilter . '%');
}
$query->orderBy('name');
if ($limit > 0) {
    $query->limit($limit);
}

$prisoners = $query->get(['id', 'name', 'first_name', 'last_name', 'birthdate', 'death_date', 'state', 'era', 'ideologies', 'affiliation']);

echo 'Mode: ' . ($apply ? 'APPLY (writing to DB)' : 'DRY-RUN (no DB writes)') . "\n";
echo 'Records to check: ' . count($prisoners) . "\n";
echo 'min-kw: ' . $minKw . "\n\n";

$updated = 0;
$lowConf = 0;
$noMatch = 0;
$noDates = 0;

function cleanDate(?string $t): ?string
{
    if (! $t) {
        return null;
    }
    if (preg_match('/^\+?(\-?\d{1,4})-(\d{2})-(\d{2})/', $t, $m)) {
        $year = ltrim($m[1], '+');
        if ($m[2] === '00' || $m[3] === '00') {
            return null;
        }
        return sprintf('%04d-%s-%s', (int) $year, $m[2], $m[3]);
    }
    return null;
}

function searchWikipedia(string $name, string $ua): array
{
    $r = Http::withHeaders(['User-Agent' => $ua])
        ->timeout(20)
        ->get('https://en.wikipedia.org/w/api.php', [
            'action'   => 'query',
            'list'     => 'search',
            'srsearch' => $name,
            'srlimit'  => 5,
            'format'   => 'json',
        ]);
    if (! $r->ok()) {
        return [];
    }
    return $r->json('query.search', []) ?? [];
}

function fetchPageProps(string $title, string $ua): ?array
{
    $r = Http::withHeaders(['User-Agent' => $ua])
        ->timeout(20)
        ->get('https://en.wikipedia.org/w/api.php', [
            'action'      => 'query',
            'prop'        => 'pageprops|extracts',
            'titles'      => $title,
            'exintro'     => 1,
            'explaintext' => 1,
            'redirects'   => 1,
            'format'      => 'json',
        ]);
    if (! $r->ok()) {
        return null;
    }
    $pages = $r->json('query.pages', []) ?? [];
    foreach ($pages as $pg) {
        return $pg;
    }
    return null;
}

function fetchWikidata(string $qid, string $ua): array
{
    $r = Http::withHeaders(['User-Agent' => $ua])
        ->timeout(20)
        ->get('https://www.wikidata.org/w/api.php', [
            'action' => 'wbgetentities',
            'ids'    => $qid,
            'props'  => 'claims',
            'format' => 'json',
        ]);
    if (! $r->ok()) {
        return [];
    }
    return $r->json("entities.{$qid}.claims", []) ?? [];
}

foreach ($prisoners as $p) {
    $needDob = empty($p->birthdate);
    $needDod = empty($p->death_date);
    if (! $needDob && ! $needDod) {
        continue;
    }

    $searches = array_unique(array_filter([
        $p->name,
        ($p->first_name && $p->last_name) ? "{$p->first_name} {$p->last_name}" : null,
    ]));

    $best = null;
    foreach ($searches as $term) {
        $hits = searchWikipedia($term, $ua);
        usleep(150_000);
        foreach ($hits as $hit) {
            $title = $hit['title'] ?? null;
            if (! $title) {
                continue;
            }
            $pg = fetchPageProps($title, $ua);
            usleep(150_000);
            if (! $pg) {
                continue;
            }
            $qid = $pg['pageprops']['wikibase_item'] ?? null;
            $extract = $pg['extract'] ?? '';
            if (! $qid) {
                continue;
            }
            $claims = fetchWikidata($qid, $ua);
            usleep(150_000);

            $isHuman = false;
            foreach ($claims['P31'] ?? [] as $c) {
                if (($c['mainsnak']['datavalue']['value']['id'] ?? '') === 'Q5') {
                    $isHuman = true;
                    break;
                }
            }
            if (! $isHuman) {
                continue;
            }

            $dob = cleanDate($claims['P569'][0]['mainsnak']['datavalue']['value']['time'] ?? null);
            $dod = cleanDate($claims['P570'][0]['mainsnak']['datavalue']['value']['time'] ?? null);

            $contextTerms = array_filter(array_merge(
                [$p->state, $p->era],
                $p->ideologies ?? [],
                $p->affiliation ?? [],
                ['imprisoned','convicted','sentenced','arrested','defendant','prison','indicted','espionage','sedition','terrorism','activist','militant','dissident','revolutionary']
            ));
            $kwHits = 0;
            $extractLower = mb_strtolower($extract);
            foreach ($contextTerms as $term) {
                $term = trim((string) $term);
                if ($term === '') {
                    continue;
                }
                if (mb_stripos($extractLower, mb_strtolower($term)) !== false) {
                    $kwHits++;
                }
            }

            $candidate = [
                'title'   => $title,
                'qid'     => $qid,
                'dob'     => $dob,
                'dod'     => $dod,
                'kwHits'  => $kwHits,
                'extract' => mb_substr($extract, 0, 160),
            ];
            if ($best === null || $candidate['kwHits'] > $best['kwHits']) {
                $best = $candidate;
            }
            if ($best['kwHits'] >= $minKw) {
                break 2;
            }
        }
    }

    if (! $best) {
        echo "  -- NO MATCH   {$p->name}\n";
        $noMatch++;
        continue;
    }

    $changes = [];
    if ($needDob && $best['dob']) {
        $changes['birthdate'] = $best['dob'];
    }
    if ($needDod && $best['dod']) {
        $changes['death_date'] = $best['dod'];
    }

    if (empty($changes)) {
        echo "  -- NO DATES  {$p->name}  ({$best['title']}, kw={$best['kwHits']})\n";
        $noDates++;
        continue;
    }

    if ($best['kwHits'] < $minKw) {
        echo "  ?? LOWCONF   {$p->name}  ->  {$best['title']} (kw={$best['kwHits']}) "
            . json_encode($changes) . "\n     extract: {$best['extract']}\n";
        $lowConf++;
        continue;
    }

    echo "  ++ UPDATE    {$p->name}  ->  {$best['title']} (kw={$best['kwHits']}) "
        . json_encode($changes) . "\n";
    if ($apply) {
        foreach ($changes as $k => $v) {
            $p->{$k} = $v;
        }
        $p->save();
        $updated++;
    }
}

echo "\nSummary:\n";
echo "  Updated:     {$updated}" . ($apply ? '' : ' (dry-run; not written)') . "\n";
echo "  Low conf:    {$lowConf}\n";
echo "  No dates:    {$noDates}\n";
echo "  No match:    {$noMatch}\n";
