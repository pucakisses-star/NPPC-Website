#!/usr/bin/env bash
#
# David Elmakayes -- set birthdate and remove the photo.
#
# The record carried a stored age of 30 with no birthdate behind it, so
# the age was a free-floating number that would never advance. Setting
# the birthdate to December 15, 1995 gives the age something to derive
# from; the model recomputes it on save, and it lands on 30 again, this
# time correctly.
#
# The photo is removed at the request of the site owner: the record is
# cleared and the file deleted from public storage, but only when no
# other record points at the same file.
#
# Idempotent -- a second run finds the birthdate already set and the
# photo already gone. Run from the repo root:
#   bash database/data/fix-david-elmakayes.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Storage;

$p = Prisoner::withoutGlobalScopes()->where("slug", "david-elmakayes")->first();
if (! $p) {
    echo "NOT FOUND: david-elmakayes\n";
    exit(1);
}

$p->setPartialDate("birthdate", 1995, 12, 15);

$old = $p->photo;
if ($old) {
    $shared = Prisoner::withoutGlobalScopes()
        ->where("photo", $old)
        ->where("id", "!=", $p->id)
        ->exists();
    if (! $shared && Storage::disk("public")->exists($old)) {
        Storage::disk("public")->delete($old);
        echo "  deleted photo file: {$old}\n";
    } elseif ($shared) {
        echo "  photo file kept on disk: {$old} is also used by another record\n";
    } else {
        echo "  photo file was already absent from disk: {$old}\n";
    }
    $p->photo = null;
} else {
    echo "  no photo on the record (already removed)\n";
}

$p->save();

$p->refresh();
echo "\nDavid Elmakayes  [{$p->slug}]\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")."   (expect Dec 15, 1995)\n";
echo "  age  ".($p->age ?? "-")."   (expect 30)\n";
echo "  photo ".($p->photo ?: "(none)")."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
