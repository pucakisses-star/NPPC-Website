#!/usr/bin/env bash
#
# The supplied Freedom Archives scan (DOC510 Bulldozer collection,
# "The Women of the Ohio 7") turned out to be byte-identical to the
# zine already held at /pdfs/zines/women-of-ohio-7.pdf — but the same
# document had been registered twice: once by
# archive:add-women-of-ohio-7-zine (Movement Zines) and once by the
# Collection 29 batch import under the slug
# freedom-archives-ohio-7-women-bulldozer.
#
# This script keeps the richer Movement Zines record — now updated with
# the Bulldozer provenance, publisher, and a thumbnail — and removes
# the batch-import duplicate (record and file). The duplicate's row
# has also been removed from freedom-archives-collection-29.json so
# re-running the batch import will not recreate it.
#
# Idempotent throughout.
#
# Run from the repo root:  bash database/data/dedupe-women-of-ohio-7.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan archive:add-women-of-ohio-7-zine

php artisan tinker --execute='
$dup = \App\Models\ArchiveRecord::where("slug", "freedom-archives-ohio-7-women-bulldozer")->first();
if ($dup) { $dup->delete(); echo "DELETED duplicate record\n"; } else { echo "No duplicate record (ok)\n"; }
$f = public_path("pdfs/freedom-archives/freedom-archives-ohio-7-women-bulldozer.pdf");
if (is_file($f)) { unlink($f); echo "DELETED duplicate file\n"; } else { echo "No duplicate file (ok)\n"; }
echo "Done.\n";
'

echo
echo "Done. Women of the Ohio 7 deduplicated."
