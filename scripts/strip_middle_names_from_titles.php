<?php

declare(strict_types=1);

/**
 * For each merged-survivor prisoner whose `name` (display title)
 * still contains a middle name, move the middle portion into the
 * `middle_name` field and shorten the `name` to "First Last".
 *
 * Spanish-convention names with two surnames (González Claudio /
 * López Rivera) are deliberately left alone — neither part is a
 * middle name.
 *
 * Hyphenated surnames (Cloud-Morgan, Smith-Stewart, Curzi-Laaman,
 * Segarra-Palmer) treat the hyphenated form as the surname.
 *
 * Quoted nicknames ("Jacob", "Angry Bird", "Bun", etc.) stay in the
 * name field — they are aliases, not middle names.
 *
 * Also adds the original full name to `aka` so historical lookups
 * still resolve.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// [current_name, new_name, middle_name]
$mappings = [
    ['Alice Stokes Paul',                     'Alice Paul',                   'Stokes'],
    ['Alvah Cecil Bessie',                    'Alvah Bessie',                 'Cecil'],
    ['Bernardine Rae Dohrn',                  'Bernardine Dohrn',             'Rae'],
    ['Charles James Liteky',                  'Charles Liteky',               'James'],
    ['David Mack Eberhardt',                  'David Eberhardt',              'Mack'],
    ['Emily Montague Harris',                 'Emily Harris',                 'Montague'],
    ['Eric Patrick Brandt',                   'Eric Brandt',                  'Patrick'],
    ['Ethel Greenglass Rosenberg',            'Ethel Rosenberg',              'Greenglass'],
    ['Eugene Victor Debs',                    'Eugene Debs',                  'Victor'],
    ['Imari Abubakari Obadele',               'Imari Obadele',                'Abubakari'],
    ['Junius Irving Scales',                  'Junius Scales',                'Irving'],
    ['Margaret Higgins Sanger',               'Margaret Sanger',              'Higgins'],
    ['Marilyn Jean Buck',                     'Marilyn Buck',                 'Jean'],
    ['Pauline Forstall Adams',                'Pauline Adams',                'Forstall'],
    ['Robert George Thompson',                'Robert Thompson',              'George'],
    ['Roger Nash Baldwin',                    'Roger Baldwin',                'Nash'],
    ['Susan Lisa Rosenberg',                  'Susan Rosenberg',              'Lisa'],
    ['Thomas Joseph Mooney',                  'Thomas Mooney',                'Joseph'],
    ['Victor Luitpold Berger',                'Victor Berger',                'Luitpold'],
    ['Warren Knox Billings',                  'Warren Billings',              'Knox'],
    ['Jorge Enrique Rodríguez Mendieta',      'Jorge Rodríguez Mendieta',     'Enrique'],
    ['Nicolas Ricardo Tapasco Romero',        'Nicolas Tapasco Romero',       'Ricardo'],
    ['Abdelhaleem Hasan Abdelraziq Ashqar',   'Abdelhaleem Ashqar',           'Hasan Abdelraziq'],
    ['Amber Marie Smith-Stewart',             'Amber Smith-Stewart',          'Marie'],
    ['Anthony David Ale Smith',               'Anthony Smith',                'David Ale'],
    ['Brent Vincent Betterly',                'Brent Betterly',               'Vincent'],
    ['Bridget Irene Shergalis',               'Bridget Shergalis',            'Irene'],
    ['Caleb Hunter Freestone',                'Caleb Freestone',              'Hunter'],
    ['Calla Mairead Walsh',                   'Calla Walsh',                  'Mairead'],
    ['Casey Robert Goonan',                   'Casey Goonan',                 'Robert'],
    ['Charles Sims Africa',                   'Charles Africa',               'Sims'],
    ['Chase A. Iron Eyes',                    'Chase Iron Eyes',              'A.'],
    ['Clare Therese Grady',                   'Clare Grady',                  'Therese'],
    ['Daniel Alan Baker',                     'Daniel Baker',                 'Alan'],
    ['Daniel Gerard McGowan',                 'Daniel McGowan',               'Gerard'],
    ['David Guy McKay',                       'David McKay',                  'Guy'],
    ['Debbie Sims Africa',                    'Debbie Africa',                'Sims'],
    ['Donald Jose-David Zepeda',              'Donald Zepeda',                'Jose-David'],
    ['Douglas L. Wright',                     'Douglas Wright',               'L.'],
    ['Dylcia Noemí Pagán',                    'Dylcia Pagán',                 'Noemí'],
    ['Hridindu Sankar Roychowdhury',          'Hridindu Roychowdhury',        'Sankar'],
    ['Jaan Karl Laaman',                      'Jaan Laaman',                  'Karl'],
    ['John Robert Mazurek',                   'John Mazurek',                 'Robert'],
    ['John William Sherman',                  'John Sherman',                 'William'],
    ['Joseph Mahmoud Dibee',                  'Joseph Dibee',                 'Mahmoud'],
    ['Juan Enrique Segarra-Palmer',           'Juan Segarra-Palmer',          'Enrique'],
    ['Karl H. Meyer',                         'Karl Meyer',                   'H.'],
    ['Linda Sue Evans',                       'Linda Evans',                  'Sue'],
    ['Marjorie Bradford Melville',            'Marjorie Melville',            'Bradford'],
    ['Mark Peter Colville',                   'Mark Colville',                'Peter'],
    ['Merle Austin Africa',                   'Merle Africa',                 'Austin'],
    ['Nancy Jane Epling',                     'Nancy Epling',                 'Jane'],
    ['Nathan Fraser Block',                   'Nathan Block',                 'Fraser'],
    ['Sami Amin Al-Arian',                    'Sami Al-Arian',                'Amin'],
    ['Sherman Martin Austin',                 'Sherman Austin',               'Martin'],
    ['Sophie Marika Ross',                    'Sophie Ross',                  'Marika'],
    ['Timothy Allen Blunk',                   'Timothy Blunk',                'Allen'],
    ['William C. Rodgers',                    'William Rodgers',              'C.'],
    ['Claudia Vera Jones',                    'Claudia Jones',                'Vera'],
    ['James William Kilgore',                 'James Kilgore',                'William'],
    ['William Joe Wright Jr.',                'William Wright Jr.',           'Joe'],
];

$updated = 0;
$skipped = 0;

foreach ($mappings as [$oldName, $newName, $middle]) {
    $p = Prisoner::where('name', $oldName)->first();
    if (! $p) {
        echo "  SKIP (not found): {$oldName}\n";
        $skipped++;
        continue;
    }

    $changed = false;

    if ($p->name !== $newName) {
        $p->name = $newName;
        $changed = true;
    }

    if (empty($p->middle_name) && $middle !== '') {
        $p->middle_name = $middle;
        $changed = true;
    }

    // Add the original full name to AKA so historical lookups still work
    $aka = trim((string) $p->aka);
    $akaParts = [];
    foreach (preg_split('/\s*;\s*/', $aka) as $piece) {
        $piece = trim($piece);
        if ($piece !== '') $akaParts[$piece] = true;
    }
    if (! isset($akaParts[$oldName])) {
        $akaParts[$oldName] = true;
        $p->aka = implode('; ', array_keys($akaParts));
        $changed = true;
    }

    if ($changed) {
        $p->save();
        echo "  Updated: '{$oldName}' -> name='{$p->name}', middle_name='{$p->middle_name}'\n";
        $updated++;
    } else {
        echo "  No change: {$oldName}\n";
        $skipped++;
    }
}

echo "\nDone.\n";
echo sprintf("  Updated: %d\n", $updated);
echo sprintf("  Skipped: %d\n", $skipped);
