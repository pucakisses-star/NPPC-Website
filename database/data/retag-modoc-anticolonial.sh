#!/usr/bin/env bash
#
# Taxonomy change (ideologies), per request:
#   - Remove the "Anti-colonial" ideology from EVERY record that has it.
#   - Remove the "Modoc sovereignty" ideology from every record that has it.
#   - On the records that carried "Modoc sovereignty" (the six Modoc War
#     defendants), add "Native American Activism" in place of the two removed
#     tags. Records that only had "Anti-colonial" (e.g. the Puerto Rican
#     independence activists) simply lose that tag and get nothing added.
#
# Comparison is case-insensitive and whitespace-trimmed; all other ideologies
# on each record are kept, and no records are deleted. Idempotent. Run from the
# repo root:
#   bash database/data/retag-modoc-anticolonial.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$remove = ["anti-colonial", "modoc sovereignty"];
$naa = "Native American Activism";
$changedAll = 0; $addedNaa = 0;

\App\Models\Prisoner::withoutGlobalScopes()
    ->select("id", "ideologies")
    ->chunk(500, function ($chunk) use (&$changedAll, &$addedNaa, $remove, $naa) {
        foreach ($chunk as $p) {
            $ide = (array) $p->ideologies;
            $hadModoc = false;
            foreach ($ide as $v) {
                if (strtolower(trim((string) $v)) === "modoc sovereignty") { $hadModoc = true; break; }
            }
            $kept = array_values(array_filter($ide, function ($v) use ($remove) {
                return ! in_array(strtolower(trim((string) $v)), $remove, true);
            }));
            if ($hadModoc) {
                $has = false;
                foreach ($kept as $v) { if (strtolower(trim((string) $v)) === strtolower($naa)) { $has = true; break; } }
                if (! $has) { $kept[] = $naa; $addedNaa++; }
            }
            if ($kept !== $ide) {
                $p->ideologies = $kept ?: null;
                $p->save();
                $changedAll++;
            }
        }
    });

echo "Updated {$changedAll} record(s); added \"{$naa}\" to {$addedNaa} Modoc record(s).\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Modoc/Anti-colonial ideologies retagged."
