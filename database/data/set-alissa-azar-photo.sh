#!/usr/bin/env bash
#
# Alissa Azar -- replace her portrait.
#
# Her record already carried a photograph, so this is a REPLACEMENT and
# not a gap-fill. The standing prisoners:attach-nonfree-photos command
# deliberately never overwrites an existing image, which is why this
# needs its own script.
#
# WHAT IS BEING REPLACED MATTERS. The photograph on the record was her
# Clackamas County Sheriff booking photo, watermarked in the top corner
# with "@MrAndyNgo" -- the social-media handle of the journalist whose
# reporting was bound up with her prosecution, and who was her adversary
# in the courtroom this record documents. An antifascist activist
# illustrated by her mugshot as circulated by her opponent is not a
# neutral portrait.
#
# THE REPLACEMENT is a self-portrait published on her own site, cropped
# from 1277x1600 to head and shoulders at 422x700 (the source file is a
# June 6, 2024 WhatsApp image; the crop keeps the antifascist flag
# graffiti behind her, which is part of the picture she chose). It is
# copyrighted and is credited in
# database/data/photos/CREDITS-nonfree.md alongside the other non-free
# portraits.
#
# Idempotent -- re-running copies the same file over the same path. Run
# from the repo root:
#   bash database/data/set-alissa-azar-photo.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->where("slug", "alissa-azar")->first();
if (! $p) {
    echo "NOT FOUND: alissa-azar\n";
    exit(1);
}

$src = database_path("data/photos/nonfree/alissa-azar.jpg");
if (! is_file($src)) {
    echo "PHOTO FILE MISSING: {$src}\n";
    exit(1);
}

$was = $p->photo ?: "(none)";
File::ensureDirectoryExists(storage_path("app/public/prisoners"));
$dest = "prisoners/alissa-azar.jpg";
File::copy($src, storage_path("app/public/".$dest), true);
touch(storage_path("app/public/".$dest));
$p->photo = $dest;
$p->save();

echo "Alissa Azar  [{$p->slug}]\n";
echo "  was:  {$was}\n";
echo "  now:  {$p->photo}\n";
echo "  bytes on disk: ".filesize(storage_path("app/public/".$dest))."\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
