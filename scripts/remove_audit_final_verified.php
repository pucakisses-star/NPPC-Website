<?php

declare(strict_types=1);

/**
 * Audit final — 68 prisoners verified by 5-agent web research as having
 * NO meaningful pretrial detention (no overnight booking, no exile,
 * no court-martial, no immigration custody beyond hours).
 *
 * Out of 175 originally flagged, the agents confirmed 68 safe-to-delete,
 * 69 had pretrial detention (kept), 39 unclear (kept).
 *
 * IMPORTANT: This script SUPERSEDES all earlier audit-pass scripts.
 * Do not run remove_audit_pass1, pass2, pass3, or block1 — only this one.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$names = [
    // Charges dropped / never arrested
    'Ali Rehman',
    'Robert Hardy',
    'Sgt. Frank "Greg" Ford', 'Sgt. Frank Ford', 'Frank Ford',
    'Jay O\'Hara', 'Jay O Hara',
    'Maysoun Batley',
    'Ruya Hayezen',
    'Khaled Meshaal',
    'Ali Baraka',
    'Terry Kuelper',

    // Booked-and-released same day, no overnight
    'Anne Rolfes',
    'Kate McIntosh',
    'Elmina Aghayeva',
    'Marcus Goodman Raskin',
    'James Jones',

    // UMich Gaza encampment (charged by summons, never custody)
    'Asad Ahmed Siddiqui',
    'Michael David Mueller',
    'Oliver Kozler',
    'Avi Benjamin Tachna-Fram',
    'Henry David Mackeen-Shapiro',
    'Rhiannon Willow',
    'Samantha Rose Lewis',

    // FSAM 1985 South African Embassy (released within hours)
    'John James Conyers Jr.',
    'Joseph Echols Lowery',
    'Charles Arthur Hayes',
    'Ronald Vernie Dellums',
    'George William Crockett Jr.',
    'Parren James Mitchell',
    'Donald LeRoy Edwards',
    'Lowell Palmer Weicker Jr.',
    'Coleman Alexander Young',
    'Marian Wright Edelman',
    'Hilda Howland Mason',
    'Yolanda Denise King',
    'Coretta Scott King',
    'Bernice Albertine King',
    'Martin Luther King III',
    'Rosa Louise Parks',
    'Stevland Hardaway Morris',
    'Harry Belafonte',
    'Arthur Robert Ashe Jr.',
    'Amy Lynn Carter',
    'Jesse Louis Jackson Sr.',
    'Richard Claxton Gregory',
    'Gloria Marie Steinem',
    'Larry Holmes',
    'Anthony Leonard Randall',

    // Nevada Test Site cite-and-release
    'Martin Sheen',
    'Carl Edward Sagan',
    'Ann Druyan',
    'Kris Kristofferson',
    'Thomas John Gumbleton',
    'Charles Albert Buswell',
    'Rosemary Lynch',

    // Hospitalized / never arrested
    'S. Brian Willson',

    // Anti-apartheid 1985-86 sit-ins (cite-and-release)
    'Pedro Antonio Noguera',
    'Nancy Skinner',
    'William Nessen',
    'Matthew J. Countryman',
    'Michael Morand',
    'Matthew Lyons',

    // Sanctuary Movement (arraigned + released pending trial)
    'Anthony Clark',
    'Ramón Dagoberto Quiñones',
    'Philip M. Willis-Conger',
    'Margaret Jean Hutchison',
    'Wendy LeWin',

    // Other
    'Samuel Holden Lovejoy',
    'W. E. B. Du Bois', 'William Edward Burghardt Du Bois',
    'Howard Zinn', 'Howard Zinn (Camden 28 expert witness)',
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
