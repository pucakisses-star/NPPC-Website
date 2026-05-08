<?php

declare(strict_types=1);

/**
 * Audit Pass 2 removals — 137 prisoners flagged by 5-agent review of
 * 292 candidates with non-conviction case dispositions (acquitted,
 * dismissed, dropped, voluntary dismissal, etc.) where the description
 * does NOT indicate meaningful pretrial detention, immigration
 * detention, exile, or court-martial.
 *
 * Categories of removal:
 *   - Greenpeace 28 (Houston bridge rappel; charges did not proceed)
 *   - Asheville 11 (charges dismissed without leave)
 *   - RNC 8 (all charges dropped)
 *   - UMich Gaza encampment (charges dismissed)
 *   - FSAM 1985 South African embassy arrests (same-day ROR celebrities)
 *   - Anti-nuclear cite-and-release (Carl Sagan, Ann Druyan, Martin Sheen)
 *   - Diablo Canyon brief detentions (Jackson Browne, Wavy Gravy)
 *   - Rocky Flats brief detentions (Allen Ginsberg, Anne Waldman)
 *   - Anti-apartheid sit-ins (same-day release)
 *   - #ShutItDown / pipeline action defendants (cases dismissed)
 *   - AETA 4 dismissed defendants
 *   - Hamas leadership never in U.S. custody
 *   - Other one-offs
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$names = [
    // Greenpeace 28 + bridge action
    'Brianna LaTrell Gibson', 'Cole Asher Taylor-Martin', 'Richard Alexander Sisney',
    'Ryan Harris', 'Sarah Francis Newman', 'Sydney Leanne Clifford', 'Shavone Torres',
    'Tyler N. McFarland', 'Tracye Redd', 'Tamura Russell Seiji', 'Zeph Fishlyn',
    'Heather Glasgow Doyle', 'Christian Deshawn Bufford', 'Chelcee Price',
    'Dakota Paige Schee', 'Heidi Nybroten', 'Julie Ann McElvain', 'Jonathan Butler',
    'Jayden Chayanne Allen', 'Kim Irene', 'Michael Anton Herbert', 'Piper Werle',
    'Mariah De Los Santos',

    // Other small environmental cases
    'Jay O Hara', "Jay O'Hara",
    'Emily Nesbitt Johnston', 'Annette Marie Klapstein', 'Benjamin Gary Joldersma',
    'Steven Robert Liptay',
    'Jamie L. Boulter', 'Holden Dometrius',
    'Anne Rolfes', 'Kate McIntosh',

    // AETA 4 dismissed
    'Nathan Pope', 'Maryam Khajavi', 'Adriana Stumpo',

    // I-69 protest plea-probation
    'Gina Eleyna Wertz', 'Hugh F. Farrell',

    // Vietnam-era brief military / unrelated
    'Ernest McQueen',

    // NoDAPL Backwater Bridge
    'Brandon Aaron Miller-Castillo',

    // ICE 12-hour
    'Elmina Aghayeva',

    // Asheville 11
    'Jordan Magde Ferrand-Sapsis', 'Randall Duncan Stezer', 'Wyatt Sherman Allgeier',
    'Cailin Elizabeth Major', 'Nicholas Ryan Entwistle',

    // Daniel Kruk (cooperation dismissal)
    'Daniel Kruk',

    // RNC 8
    'Monica Bicking', 'Luce Guillen-Givins', 'Eryn Trimmer',

    // Misc dropped
    'Gloria Merriweather', 'Davis Alan Beeman', 'Maysoun Batley', 'Ruya Hayezen',
    'Leo A. Randle', 'Trevor H. Carter', 'James Jones',

    // UMich Gaza encampment
    'Asad Ahmed Siddiqui', 'Michael David Mueller', 'Oliver Kozler',
    'Avi Benjamin Tachna-Fram', 'Henry David Mackeen-Shapiro', 'Rhiannon Willow',
    'Samantha Rose Lewis',

    // Misc one-offs
    'Haley Rainwater', 'Cortez Aaron Rice', 'Sayed A. Quraishi', 'Hicham Talal',
    'Emily Keppler', 'Madeline Rose Fening',

    // Hamas leadership never in U.S. custody (controversial — agents put as REMOVE/AMBIGUOUS, including only those that returned REMOVE)
    'Khaled Meshaal', 'Ali Baraka',

    // Acquitted with no documented pretrial detention
    'Edmee Chavannes', 'Csaba John Csukas',
    'Ghufran Ullah', 'Izhar Muhammad', 'Mohammad Mazhar',
    'Matthew Karlovsky',
    'Malachi Joshua Marlan-Librett', 'Eyal Shalom',
    'James A. Corbett',

    // FSAM 1985 South African Embassy (same-day ROR celebrities)
    'Randall Robinson', 'Mary Frances Berry', 'Walter Edward Fauntroy',
    'John James Conyers Jr.', 'Joseph Echols Lowery', 'Charles Arthur Hayes',
    'Ronald Vernie Dellums', 'George William Crockett Jr.', 'Parren James Mitchell',
    'Donald LeRoy Edwards', 'Lowell Palmer Weicker Jr.', 'Coleman Alexander Young',
    'Marian Wright Edelman', 'Hilda Howland Mason', 'Yolanda Denise King',
    'Coretta Scott King', 'Bernice Albertine King', 'Martin Luther King III',
    'Rosa Louise Parks', 'Stevland Hardaway Morris', 'Harry Belafonte',
    'Arthur Robert Ashe Jr.', 'Amy Lynn Carter', 'Jesse Louis Jackson Sr.',
    'Richard Claxton Gregory', 'Gloria Marie Steinem', 'Larry Holmes',
    'Anthony Leonard Randall',

    // S. Brian Willson (civil case only)
    'S. Brian Willson',

    // Anti-nuclear cite-and-release celebrity arrests (Nevada Test Site etc.)
    'Martin Sheen', 'Carl Edward Sagan', 'Ann Druyan', 'Kris Kristofferson',
    'Thomas John Gumbleton', 'Charles Albert Buswell', 'Rosemary Lynch',
    'James W. Douglass', 'Shelley Douglass', 'Karol Schulkin', 'Mary Grondin',

    // Diablo Canyon brief
    'Jackson Browne', 'Hugh Nanton Romney Jr.',

    // Rocky Flats brief
    'Allen Ginsberg', 'Anne Waldman', 'Pam Solo',

    // Anti-apartheid sit-ins
    'Pedro Antonio Noguera', 'Nancy Skinner', 'William Nessen',
    'Matthew J. Countryman', 'Michael Morand', 'Matthew Lyons',

    // Sanctuary movement deferred adjudication
    'Mary Dianne Muhlenkamp',

    // Misc acquitted/dropped without pretrial
    'Samuel Holden Lovejoy', 'Terry Kuelper',
    'Marcus Goodman Raskin', 'William Z. Foster',
    'W. E. B. Du Bois', 'William Edward Burghardt Du Bois',

    // Howard Zinn (expert witness, not defendant)
    'Howard Zinn',
];

$prisoners = Prisoner::whereIn('name', $names)->get();

$nP = 0; $nC = 0;
foreach ($prisoners as $p) {
    $caseCount = $p->cases()->count();
    $p->cases()->delete();
    $p->delete();
    $nP++;
    $nC += $caseCount;
    echo "  Removed: {$p->name} (id={$p->id}, cases={$caseCount})\n";
}

echo "\nDone. Prisoners deleted: {$nP}, cases deleted: {$nC}\n";
