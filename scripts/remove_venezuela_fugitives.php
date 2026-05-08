<?php

declare(strict_types=1);

/**
 * Remove four Venezuela-government Cartel de los Soles indictees who
 * were never in U.S. custody (verified by web search May 2026):
 *   - Diosdado Cabello Rondón     (still in Venezuela, central power-holder)
 *   - Vladimir Padrino López       (former Defense Minister, never extradited)
 *   - Maikel José Moreno Pérez     (former Supreme Court Chief Justice)
 *   - Tareck Zaidan El Aissami Maddah (in Venezuelan prison since Apr 2024)
 *
 * KEPT (verified actual U.S. custody at some point):
 *   - Nicolás Maduro Moros, Cilia Flores (captured Jan 3, 2026)
 *   - Cliver Alcalá Cordero (surrendered 2020, US prison)
 *   - Hugo Carvajal Barrios (extradited from Spain 2023)
 *   - Alex Saab (extradited 2021)
 *   - Efraín Campo Flores, Franqui Flores de Freitas (served 7 years)
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Prisoner;

$names = [
    'Diosdado Cabello Rondon',
    'Diosdado Cabello Rondón',
    'Diosdado Cabello',
    'Vladimir Padrino Lopez',
    'Vladimir Padrino López',
    'Vladimir Padrino',
    'Maikel Jose Moreno Perez',
    'Maikel José Moreno Pérez',
    'Maikel Moreno',
    'Tareck Zaidan El Aissami Maddah',
    'Tareck El Aissami',
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
