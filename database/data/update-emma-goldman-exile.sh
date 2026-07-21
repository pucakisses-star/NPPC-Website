#!/usr/bin/env bash
#
# Add Emma Goldman's exile period. After her Espionage Act imprisonment she
# was deported from the United States on 21 December 1919 aboard the USAT
# Buford (the "Soviet Ark") and lived the rest of her life in exile — barred
# from permanent return — until her death on 14 May 1940. Records the exile
# on her existing case.
#   in exile since: 1919-12-21
#   end of exile:   1940-05-14 (her death)
#   ~7,450 days
#
# Idempotent: only sets the exile fields while empty.
#   bash database/data/update-emma-goldman-exile.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "emma-goldman")->first();
if (! $p) { echo "emma-goldman not found\n"; return; }
$c = $p->cases()->first();
if (! $c) { echo "no case\n"; return; }

if (empty($c->in_exile_since))   { $c->in_exile_since = "1919-12-21"; echo "SET in_exile_since 1919-12-21\n"; }
if (empty($c->end_of_exile))     { $c->end_of_exile = "1940-05-14"; echo "SET end_of_exile 1940-05-14\n"; }
if (empty($c->in_exile_for_days)) { $c->in_exile_for_days = 7450; echo "SET in_exile_for_days 7450\n"; }
if ($c->isDirty()) { $c->save(); }

if (empty($p->in_exile)) { $p->in_exile = true; $p->save(); echo "SET in_exile flag\n"; }

echo "Done updating emma-goldman.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Emma Goldman exile period added."
