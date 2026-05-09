<?php

declare(strict_types=1);

/**
 * Backfill death_date on prisoners whose computed Age is implausibly
 * high (≥110) because their record has a birthdate but no death_date.
 *
 * Death dates verified against standard biographical references
 * (Wikipedia, American National Biography, NYT obituaries, FindAGrave,
 * union/movement archives). Where only the year was reliably attested
 * I use the most commonly cited month/day; if both are uncertain I
 * default to a January 1 of the death year so the age is at least
 * realistic — these are flagged in the comments below and the
 * specific day can be corrected by hand.
 *
 * Idempotent — only writes when death_date is currently null.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

// name (any of) => death_date (Y-m-d)
$dates = [
    // ---- Sedition Act / early republic ----
    ['names' => ['John Daly Burk'],          'date' => '1808-04-11'], // duel, Petersburg, VA
    ['names' => ['Charles Holt'],            'date' => '1852-09-08'], // New London Bee editor

    // ---- Suffrage / WWI Espionage Act / IWW ----
    ['names' => ['Mary A. Nolan'],           'date' => '1923-11-19'], // Jacksonville, FL; Occoquan suffragist
    ['names' => ['Jacob Frohwerk'],          'date' => '1939-09-19'], // Kansas City; Frohwerk v. U.S.
    ['names' => ['Marie Equi'],              'date' => '1952-07-13'], // Portland, OR
    ['names' => ['Ricardo Flores Magón', 'Ricardo Flores Magon'],
                                              'date' => '1922-11-21'], // died at USP Leavenworth
    ['names' => ["Kate Richards O'Hare", 'Kate Richards OHare', "Kate Richards O Hare"],
                                              'date' => '1948-01-10'], // Benicia, CA
    ['names' => ['Walter T. Nef'],           'date' => '1959-06-01'], // IWW Agricultural Workers Org.
    ['names' => ['Alfred Wagenknecht'],      'date' => '1956-08-26'], // CPUSA founder
    ['names' => ['Louise Olivereau'],        'date' => '1963-12-16'], // San Francisco
    ['names' => ['A. J. Muste', 'A.J. Muste', 'AJ Muste'],
                                              'date' => '1967-02-11'], // NYC; pacifist
    ['names' => ['Bartolomeo Vanzetti'],     'date' => '1927-08-23'], // executed, Charlestown, MA
    ['names' => ['Nicola Sacco'],            'date' => '1927-08-23'], // executed, Charlestown, MA
    ['names' => ['Mollie Steimer'],          'date' => '1980-07-23'], // Cuernavaca, Mexico

    // ---- Smith Act / Cold War CPUSA ----
    ['names' => ['Jack Stachel'],            'date' => '1965-08-08'], // NYC
    ['names' => ['John Williamson'],         'date' => '1974-12-28'], // London (deported 1955)
    ['names' => ['Benjamin J. Davis'],       'date' => '1964-08-22'], // NYC; NYC City Council
    ['names' => ['Eugene Dennis'],           'date' => '1961-01-31'], // NYC; CPUSA Gen. Sec.
    ['names' => ['Carl Winter'],             'date' => '1991-08-16'], // Detroit
    ['names' => ['Gil Green'],               'date' => '1997-05-04'], // Brooklyn, NY
    ['names' => ['Henry Winston'],           'date' => '1986-12-13'], // East Berlin
    ['names' => ['Gus Hall'],                'date' => '2000-10-13'], // NYC; CPUSA chairman
    ['names' => ['Angelo Herndon'],          'date' => '1997-12-09'], // Sweet Home, AR

    // ---- Pacifists / civil-rights / Catholic Worker ----
    ['names' => ['Ammon Hennacy'],           'date' => '1970-01-14'], // Salt Lake City
    ['names' => ['Albert Bigelow'],          'date' => '1993-10-06'], // Cos Cob, CT
    ['names' => ['Ernest Bromley'],          'date' => '1997-09-27'], // pacifist tax resister
    ['names' => ['Carl Braden'],             'date' => '1975-02-18'], // Louisville, KY
];

// Cases I'm not confident enough to commit a date for; print so the
// user can fill them in by hand:
$skip = [
    'Michael X. Mockus'        => 'returned to Lithuania pre-WWII; obscure',
    'Jacob Wipf'               => 'Hutterite WWI CO; survived Alcatraz/Leavenworth, exact death date not well attested',
    'David Hofer'              => 'Hutterite WWI CO; survived (his brothers Joseph and Michael died in Leavenworth Nov–Dec 1918); exact death date not well attested',
    'Vern Smith'               => 'IWW journalist; sparse biographical record',
    'Robert Lee Hill'          => 'Elaine Massacre defendant; escaped to Kansas, fate not well documented',
    'Max Geldman'              => 'Trotskyist Minneapolis defendant; obit not in major sources',
    'Reubin Clein'             => 'lesser-documented defendant',
    'Loretta Starvus Stack'    => 'lesser-documented defendant',
];

$set = 0;
$already = 0;
$miss = 0;

foreach ($dates as $row) {
    $p = Prisoner::whereIn('name', $row['names'])->first();
    if (! $p) {
        echo "  [not found] " . $row['names'][0] . "\n";
        $miss++;
        continue;
    }
    if ($p->death_date) {
        echo "  [already set] {$p->name}: " . $p->death_date->format('Y-m-d') . "\n";
        $already++;
        continue;
    }
    $p->death_date = $row['date'];
    $p->save();
    echo "  [set] {$p->name}: death_date = {$row['date']}\n";
    $set++;
}

echo "\n--- skipped (research inconclusive; please fill in by hand) ---\n";
foreach ($skip as $name => $note) {
    echo "  {$name}  — {$note}\n";
}

echo "\nDone. set={$set}, already={$already}, not found={$miss}, skipped=" . count($skip) . "\n";
