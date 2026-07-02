#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/fix_demuth_add_case.sh
# Scott DeMuth already exists in the database but may be missing his case.
# This script adds the case if it does not already exist.
set +e

cat > /tmp/fix_demuth_case.php << 'PHPEOF'
<?php

$demuth = \App\Models\Prisoner::withoutGlobalScopes()
    ->where('slug', 'scott-demuth')
    ->firstOrFail();

echo 'Found prisoner: ' . $demuth->name . ' (ID: ' . $demuth->id . ')' . PHP_EOL;
echo 'Existing cases: ' . $demuth->cases()->count() . PHP_EOL;

if ($demuth->cases()->count() > 0) {
    echo 'Case already exists — no action needed.' . PHP_EOL;
    foreach ($demuth->cases as $c) {
        echo '  Institution ID: ' . $c->institution_id . PHP_EOL;
        echo '  Charges: ' . $c->charges . PHP_EOL;
        echo '  Sentence: ' . $c->sentence . PHP_EOL;
    }
} else {
    echo 'No case found — adding case.' . PHP_EOL;

    $inst = \App\Models\Institution::firstOrCreate(
        ['name' => 'Federal Correctional Institution', 'city' => 'Oxford', 'state' => 'Wisconsin']
    );

    $case = new \App\Models\PrisonerCase([
        'institution_id' => $inst->id,
        'charges' => 'Conspiracy to commit animal enterprise terrorism (Animal Enterprise Terrorism Act); 2004 ALF raid on University of Iowa animal research labs freed hundreds of animals and caused approximately $400,000 in damages',
        'arrest_date' => '2011-02-14',
        'incarceration_date' => '2011-02-14',
        'release_date' => '2011-07-29',
        'imprisoned_for_days' => 165,
        'convicted' => 'Yes — guilty plea',
        'sentence' => '6 months federal prison',
    ]);
    $demuth->cases()->save($case);
    echo 'Case added successfully.' . PHP_EOL;
}
PHPEOF

echo "Checking Scott DeMuth's case via tinker..."
php artisan tinker --execute="require '/tmp/fix_demuth_case.php';"
echo "Done."
