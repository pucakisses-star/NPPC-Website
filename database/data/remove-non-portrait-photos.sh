#!/usr/bin/env bash
#
# Photo audit: remove images that are not photographs of people, and
# clear photo references pointing at files that no longer exist.
#
# HOW THE AUDIT WAS DONE. All 1,882 prisoner photographs were downloaded
# from the live site and put through OpenCV face detection (frontal,
# alt2 and profile Haar cascades). 1,609 returned a face and were left
# alone. The remaining 273 were examined by eye on contact sheets, then
# every flagged image was re-examined at full size before being listed
# here. Face detection was used ONLY to decide what a human needed to
# look at, never to decide what to delete.
#
# ---------------------------------------------------------------------
# 1. NINETEEN IMAGES THAT ARE NOT OF PEOPLE
#
#   h-c-duke                a green Victorian house
#   william-p-elmer         a house with a Dent County Museum sign
#   rev-walter-a-werth      a modern civic building
#   t-a-montgomery          an empty arcade or portico
#   max-gorman              a cable-stayed bridge with a tram on it
#   montez-terriel-lee-jr   the rubble of a burned-out building
#   earlja-dudley           a burning vehicle in the street
#   h-c-spence             a warship at speed
#   george-f-voetter        a framed painting of a snowy landscape
#   philip-wigle            a painting of mounted troops in a landscape
#   sababu-na-uhuru         the cover of the novel "Stoner"
#   sean-dunn               the seal of the U.S. District Court for the
#                           District of Columbia
#   thomas-r-sullivan       a Medal of Honor
#   shadrach-minkins        an 1849 newspaper advertisement for a slave
#                           auction naming him -- a document about him,
#                           not a picture of him
#   andres-figueroa-cordero a white cross emblem on black
#   luke-day                a plain black rectangle, no image at all
#   henry-drinker           a generic silhouette placeholder
#   john-pemberton          the same silhouette placeholder
#   samuel-pleasants        the same silhouette placeholder
#
# None of these files exists anywhere in the repository -- they were
# uploaded straight to the server -- so clearing the record and deleting
# the file is the whole job and nothing will put them back.
#
# ---------------------------------------------------------------------
# 2. THIRTY-TWO PHOTO REFERENCES POINTING AT NOTHING
#
# These records carry a photo path whose file 404s: the profile shows a
# broken image instead of the honest "No image available". Among them
# are Eugene Debs, Carl Braden, Rafael Cancel Miranda, Abraham Bolden
# and six of the Scottsboro defendants (Andy Wright, Charlie Weems,
# Eugene Williams, Olen Montgomery, Ozie Powell, Roy Wright and Willie
# Roberson). Their paths are cleared; no file is deleted because there
# is no file.
#
# WILLIAM TANNER IS DELIBERATELY EXCLUDED from that list even though his
# photo also 404s: his image DOES exist in the repository, at
# database/data/photos/zimmer/william-tanner.jpg, and running
# prisoners:add-zimmer-deportees --apply will put it back. Clearing him
# would throw away a photo that is one command from being restored.
#
# ---------------------------------------------------------------------
# WHAT IS DELIBERATELY KEPT. Seven images are posters, flyers, murals or
# scenes rather than studio portraits, but each one does show its
# subject, so none is touched: haki-malik-abdullah (a painted mural of
# him), mateen-abdul-shaheed (a FREE MATEEN poster built round his
# photograph), mohaman-koti (a Jericho Movement poster round his
# photograph), jesse-cannon (a solidarity-dinner flyer round his
# photograph), william-worthy (a lecture handbill with his photograph),
# rosaura-revueltas (the Salt of the Earth poster, the film she starred
# in and was deported during), and julio-cesar-sosa-celis (riot police
# firing tear gas -- people, though not him). If any of those should go
# as well, they are one line each.
#
# Idempotent: a second run finds nothing to do. Run from the repo root:
#   bash database/data/remove-non-portrait-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\Storage;

$notPeople = [
    "andres-figueroa-cordero", "earlja-dudley", "george-f-voetter", "h-c-duke", "h-c-spence",
    "henry-drinker", "john-pemberton", "luke-day", "max-gorman", "montez-terriel-lee-jr",
    "philip-wigle", "rev-walter-a-werth", "sababu-na-uhuru", "samuel-pleasants", "sean-dunn",
    "shadrach-minkins", "t-a-montgomery", "thomas-r-sullivan", "william-p-elmer",
];

$deadRefs = [
    "a-o-taichin", "abraham-bolden", "andy-wright", "benjamin-weiss", "c-e-bates",
    "carl-braden", "charles-g-schulze", "charlie-weems", "chisom-kingston", "david-elmakayes",
    "donnie-thornsbury", "eugene-debs", "eugene-williams", "george-carter", "ira-hardy",
    "james-russell-hallam", "joe-gump", "joseph-harrison", "k-y-hendricks", "linn-a-e-gale",
    "louis-mclaughlin", "louis-werner", "namir-abdul-mateen", "o-e-gordon", "olen-montgomery",
    "ozie-powell", "rafael-cancel-miranda", "robert-allen", "roy-wright", "william-harvey",
    "william-m-martin", "willie-roberson",
];

echo "-- images that are not of people --\n";
$removed = 0;
$filesDeleted = 0;
foreach ($notPeople as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) {
        echo "  NOT FOUND: {$slug}\n";
        continue;
    }
    if (! $p->photo) {
        echo "  already cleared: {$slug}\n";
        continue;
    }

    $path = $p->photo;
    $shared = Prisoner::withoutGlobalScopes()
        ->where("photo", $path)
        ->where("id", "!=", $p->id)
        ->exists();
    if (! $shared && Storage::disk("public")->exists($path)) {
        Storage::disk("public")->delete($path);
        $filesDeleted++;
    }
    $p->photo = null;
    $p->save();
    $removed++;
    echo "  removed {$slug}  (was {$path})".($shared ? "  [file kept: shared]" : "")."\n";
}

echo "\n-- photo references pointing at missing files --\n";
$cleared = 0;
foreach ($deadRefs as $slug) {
    $p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
    if (! $p) {
        echo "  NOT FOUND: {$slug}\n";
        continue;
    }
    if (! $p->photo) {
        continue;
    }
    if (Storage::disk("public")->exists($p->photo)) {
        echo "  SKIP {$slug}: the file exists after all ({$p->photo}) -- left alone\n";
        continue;
    }
    echo "  cleared {$slug}  (dead path {$p->photo})\n";
    $p->photo = null;
    $p->save();
    $cleared++;
}

echo "\n{$removed} non-portrait photo(s) removed, {$filesDeleted} file(s) deleted from storage.\n";
echo "{$cleared} dead photo reference(s) cleared.\n";
$total = Prisoner::withoutGlobalScopes()->whereNotNull("photo")->count();
echo "Records carrying a photo now: {$total}\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
