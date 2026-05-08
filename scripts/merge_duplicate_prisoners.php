<?php

declare(strict_types=1);

/**
 * Merge ~80 duplicate prisoner records identified by the audit.
 *
 * For each (survivor, [dup1, dup2, ...]) group:
 *   - Move all cases from each dup -> survivor
 *   - Concatenate description with a space if both differ
 *   - Union AKA, ideologies, affiliation
 *   - Backfill any field on survivor that is null/empty from dup
 *     (photo, birthdate, death_date, state, race, gender, etc.)
 *   - "True wins" for boolean flags (in_custody, released, etc.)
 *   - Delete the duplicate prisoner row
 *
 * Idempotent: if a dup is already gone, it's silently skipped.
 *
 * Per user direction:
 *   - Daniel Hale's correct DOB is 1987-12-05 (survivor "Daniel Hale")
 *   - Other AMBIGUOUS pairs (Carol Manning, José Riveras, Richard
 *     Williams) deliberately NOT merged.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;
use App\Models\PrisonerCase;

/**
 * Merge a single group: survivor name + list of duplicate names.
 */
function mergeGroup(string $survivorName, array $dupNames): void {
    $survivor = Prisoner::where('name', $survivorName)->first();
    if (! $survivor) {
        echo "  SKIP (survivor not found): {$survivorName}\n";
        return;
    }

    foreach ($dupNames as $dupName) {
        $dup = Prisoner::where('name', $dupName)->first();
        if (! $dup) {
            echo "  - skip: dup '{$dupName}' not found (already merged?)\n";
            continue;
        }
        if ($dup->id === $survivor->id) {
            continue;
        }

        // 1. Move cases
        $caseCount = $dup->cases()->count();
        $dup->cases()->update(['prisoner_id' => $survivor->id]);

        // 2. Concatenate description
        $sd = trim((string) $survivor->description);
        $dd = trim((string) $dup->description);
        if ($dd !== '' && $dd !== $sd) {
            if ($sd === '' || stripos($sd, $dd) !== false) {
                $survivor->description = $dd !== '' && $sd === '' ? $dd : $sd;
            } elseif (stripos($dd, $sd) !== false) {
                $survivor->description = $dd;
            } else {
                $survivor->description = $sd . ' ' . $dd;
            }
        }

        // 3. Union AKA strings (separator: "; ")
        $sAka = trim((string) $survivor->aka);
        $dAka = trim((string) $dup->aka);
        $akaParts = [];
        foreach ([$sAka, $dAka] as $a) {
            foreach (preg_split('/\s*;\s*/', $a) as $piece) {
                $piece = trim($piece);
                if ($piece !== '') $akaParts[$piece] = true;
            }
        }
        // Also include the duplicate's name itself as an AKA so we keep that historical lookup
        $dupNameTrim = trim($dup->name);
        if ($dupNameTrim !== '' && $dupNameTrim !== trim($survivor->name)) {
            $akaParts[$dupNameTrim] = true;
        }
        if (! empty($akaParts)) {
            $survivor->aka = implode('; ', array_keys($akaParts));
        }

        // 4. Union ideologies (array)
        $survivor->ideologies = arrayUnion($survivor->ideologies, $dup->ideologies);

        // 5. Union affiliation (array)
        $survivor->affiliation = arrayUnion($survivor->affiliation, $dup->affiliation);

        // 6. Backfill scalar fields
        foreach ([
            'first_name', 'middle_name', 'last_name',
            'gender', 'race', 'state', 'birthdate', 'death_date',
            'photo', 'address', 'latitude', 'longitude',
            'inmate_number', 'website', 'twitter', 'facebook',
            'era',
        ] as $field) {
            if (empty($survivor->{$field}) && ! empty($dup->{$field})) {
                $survivor->{$field} = $dup->{$field};
            }
        }

        // 7. "True wins" for booleans
        foreach (['in_custody', 'released', 'in_exile', 'currently_in_exile', 'awaiting_trial'] as $field) {
            if ($dup->{$field}) {
                $survivor->{$field} = true;
            }
        }

        // 8. Delete the duplicate
        $dup->delete();
        echo "  Merged dup '{$dupName}' -> '{$survivorName}' (cases moved: {$caseCount})\n";
    }

    $survivor->save();
}

function arrayUnion($a, $b): ?array {
    $a = is_array($a) ? $a : [];
    $b = is_array($b) ? $b : [];
    $merged = array_values(array_unique(array_merge($a, $b), SORT_STRING));
    return $merged ?: null;
}

$groups = [
    // ---- Same DOB confirmed ----
    ['Alice Stokes Paul', ['Alice Paul']],
    ['Alvah Cecil Bessie', ['Alvah Bessie']],
    ['Bernardine Rae Dohrn', ['Bernardine Dohrn']],
    ['Charles James Liteky', ['Charles Liteky']],
    ['David Mack Eberhardt', ['David Eberhardt']],
    ['Emily Montague Harris', ['Emily Harris']],
    ['Eric Patrick Brandt', ['Eric Brandt']],
    ['Ethel Greenglass Rosenberg', ['Ethel Rosenberg']],
    ['Eugene Victor Debs', ['Eugene V. Debs', 'Eugene Debs']],
    ['Imari Abubakari Obadele', ['Imari Obadele']],
    ['Junius Irving Scales', ['Junius Scales']],
    ['Margaret Higgins Sanger', ['Margaret Sanger']],
    ['Marilyn Jean Buck', ['Marilyn Buck']],
    ['Oscar López Rivera', ['Oscar Lopez Rivera']],
    ['Pauline Forstall Adams', ['Pauline Adams']],
    ['Robert George Thompson', ['Robert G. Thompson,']],
    ['Roger Nash Baldwin', ['Roger Baldwin']],
    ['Susan Lisa Rosenberg', ['Susan Rosenberg']],
    ['Thomas Joseph Mooney', ['Thomas Mooney']],
    ['Victor Luitpold Berger', ['Victor L. Berger']],
    ['Warren Knox Billings', ['Warren Billings']],
    ['Avelino González Claudio', ['Avelino Gonzalez Claudio']],
    ['Norberto González Claudio', ['Norberto Gonzalez Claudio']],
    ['Jorge Enrique Rodríguez Mendieta', ['Jorge Enrique Rodriguez Mendieta']],

    // ---- Strong contextual match (no DOB conflict) ----
    ['Abdelhaleem Hasan Abdelraziq Ashqar', ['Abdelhaleem Ashqar']],
    ['Abdulrahman Odeh', ['Abdulrahman Odeh Odeh']],
    ['Amber Marie Smith-Stewart', ['Amber Smith-Stewart']],
    ['Anthony David Ale Smith', ['Anthony Smith']],
    ['Barbara Curzi-Laaman', ['Barbara Curzi Laaman']],
    ['Brent Vincent Betterly', ['Brent Betterly']],
    ['Brian "Jacob" Church', ['Brian Church']],
    ['Bridget Irene Shergalis', ['Bridget Shergalis']],
    ['Caleb Hunter Freestone', ['Caleb Freestone']],
    ['Calla Mairead Walsh', ['Calla Walsh']],
    ['Casey Robert Goonan', ['Casey Goonan']],
    ['Charles Sims Africa', ['Charles Simms Africa']], // typo
    ['Chase A. Iron Eyes', ['Chase Iron Eyes']],
    ['Clare Therese Grady', ['Clare Grady']],
    ['Daniel Alan Baker', ['Daniel Baker']],
    // Daniel Hale: per user, 1987-12-05 is correct. The "Daniel Hale" record has that DOB; merge "Daniel E. Hale" (1987-01-01) into it.
    ['Daniel Hale', ['Daniel E. Hale']],
    ['Daniel Gerard McGowan', ['Daniel McGowan']],
    ['David Guy McKay', ['David McKay']],
    ['Debbie Sims Africa', ['Debbie Simms Africa']], // "Sims" is correct
    ['Dhoruba Bin Wahad', ['Dhoruba bin Wahad']],
    ['Donald Jose-David Zepeda', ['Donald Jose David Zepeda']],
    ['Douglas L. Wright', ['Douglas Wright']],
    ['Dylcia Noemí Pagán', ['Dylcia Pagán']],
    ['Edward "Eddie" Goodman Africa', ['Edward Goodman Africa']],
    ['Eric King', ['Eric G. King']],
    ['Hridindu Sankar Roychowdhury', ['Hridindu Roychowdhury']],
    ['Jaan Karl Laaman', ['Jaan Laaman']],
    ['James "Bun" McKoy', ['James McKoy']],
    ['James "Angry Bird" White', ['James White', 'James A. White']],
    ['Jared "Jay" Chase', ['Jared Chase']],
    ['John Robert Mazurek', ['John Mazurek']],
    ['John William Sherman', ['John Sherman']],
    ['Joseph Mahmoud Dibee', ['Joseph Dibee']],
    ['Juan Enrique Segarra-Palmer', ['Juan Enrique Segarra Palmer', 'Juan Segarra Palmer']],
    ['Karl H. Meyer', ['Karl Meyer']],
    ['Larry Cloud-Morgan', ['Larry Cloud Morgan']],
    ['Linda Sue Evans', ['Linda Evans']],
    ['Marjorie Bradford Melville', ['Marjorie Melville']],
    ['Mark Peter Colville', ['Mark Colville']],
    ['Merle Austin Africa', ['Merle Africa']],
    ['Michael "Little Feather" Giron', ['Michael Giron', 'Michael A. Giron']],
    ['Michael "Rattler" Markus', ['Michael Markus']],
    ['Mufid Abdulqader', ['Mufid Abdel Abdulqader']],
    ['Nancy Jane Epling', ['Nancy Epling']],
    ['Nathan Fraser Block', ['Nathan Block']],
    ['Nicolas Ricardo Tapasco Romero', ['Nicolas Tapasco Romero']],
    ["Patrick O'Neill", ['Patrick M. O Neill', 'Patrick O Neill']],
    ['Sami Amin Al-Arian', ['Sami Al-Arian']],
    ['Sherman Martin Austin', ['Sherman Austin']],
    ['Sophie Marika Ross', ['Sophie Ross']],
    ['Timothy Allen Blunk', ['Timothy Blunk']],
    ['William C. Rodgers', ['William Rodgers']],
    ['Claudia Vera Jones', ['Claudia Jones']],
    ['James William Kilgore', ['James Kilgore']],
    ['William Joe Wright Jr.', ['William Wright, Jr.']],
];

echo "Merging " . count($groups) . " duplicate groups...\n\n";
foreach ($groups as [$survivor, $dups]) {
    mergeGroup($survivor, $dups);
}

echo "\nDone.\n";
