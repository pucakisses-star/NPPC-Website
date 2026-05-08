<?php

declare(strict_types=1);

/**
 * Audit block 1: removes rows 1-88 of the audit spreadsheet.
 * Subset of the 175-name removal list, approved by user as a first block.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$names = [
    'Ali Rehman',
    'Ahmadullah Sais Niazi',
    'Robert Hardy',
    'Sgt. Frank "Greg" Ford',
    'Ray Boudreaux',
    'Richard Brown',
    'Richard O\'Neal',
    'Thomas Adams',
    'Benjamin Moye',
    'Anne Symens-Bucher',
    'Bill Wylie-Kellermann',
    'Jack Cohen-Joppa',
    'Jim Wallis',
    'Carolyn Rodriguez',
    'Omali Yeshitela',
    'Brianna LaTrell Gibson',
    'Cole Asher Taylor-Martin',
    'Richard Alexander Sisney',
    'Ryan Harris',
    'Sarah Francis Newman',
    'Sydney Leanne Clifford',
    'Shavone Torres',
    'Tyler N. McFarland',
    'Tracye Redd',
    'Tamura Russell Seiji',
    'Zeph Fishlyn',
    'Heather Glasgow Doyle',
    'Christian Deshawn Bufford',
    'Chelcee Price',
    'Dakota Paige Schee',
    'Heidi Nybroten',
    'Julie Ann McElvain',
    'Jonathan Butler',
    'Jayden Chayanne Allen',
    'Kim Irene',
    'Michael Anton Herbert',
    'Piper Werle',
    'Mariah De Los Santos',
    'Jay O\'Hara',
    'Emily Nesbitt Johnston',
    'Annette Marie Klapstein',
    'Benjamin Gary Joldersma',
    'Steven Robert Liptay',
    'Jamie L. Boulter',
    'Holden Dometrius',
    'Anne Rolfes',
    'Kate McIntosh',
    'Nathan Pope',
    'Maryam Khajavi',
    'Adriana Stumpo',
    'Gina Eleyna Wertz',
    'Hugh F. Farrell',
    'Ernest McQueen',
    'Brandon Aaron Miller-Castillo',
    'Elmina Aghayeva',
    'Jordan Magde Ferrand-Sapsis',
    'Randall Duncan Stezer',
    'Wyatt Sherman Allgeier',
    'Cailin Elizabeth Major',
    'Nicholas Ryan Entwistle',
    'Daniel Kruk',
    'Monica Bicking',
    'Luce Guillen-Givins',
    'Eryn Trimmer',
    'Gloria Merriweather',
    'Davis Alan Beeman',
    'Maysoun Batley',
    'Ruya Hayezen',
    'Leo A. Randle',
    'Trevor H. Carter',
    'James Jones',
    'Asad Ahmed Siddiqui',
    'Michael David Mueller',
    'Oliver Kozler',
    'Avi Benjamin Tachna-Fram',
    'Henry David Mackeen-Shapiro',
    'Rhiannon Willow',
    'Samantha Rose Lewis',
    'Haley Rainwater',
    'Cortez Aaron Rice',
    'Sayed A. Quraishi',
    'Hicham Talal',
    'Emily Keppler',
    'Madeline Rose Fening',
    'Khaled Meshaal',
    'Ali Baraka',
    'Edmee Chavannes',
    'Csaba John Csukas',
];

$prisoners = Prisoner::whereIn('name', $names)->get();
$nP = 0; $nC = 0;
foreach ($prisoners as $p) {
    $caseCount = $p->cases()->count();
    $p->cases()->delete();
    $p->delete();
    $nP++;
    $nC += $caseCount;
    echo "  Removed: {$p->name} (id={$p->id}, cases={$caseCount})
";
}
echo "
Done. Prisoners deleted: {$nP}, cases deleted: {$nC}
";
