#!/usr/bin/env bash
#
# READ-ONLY diagnostic: report what photo a prisoner record actually points at
# and whether the file on disk matches the source committed in the repo. Use
# this when a replaced photo still looks like the old one -- it distinguishes
# "the swap never happened" from "the swap happened, your browser cached it".
#
#   bash database/data/check-prisoner-photo.sh bob-lederer
#   bash database/data/check-prisoner-photo.sh terry-bisson
set -euo pipefail
cd "$(dirname "$0")/../.."

SLUG="${1:-}"
if [ -z "$SLUG" ]; then
    echo "usage: bash database/data/check-prisoner-photo.sh <slug>"
    exit 1
fi

SLUG="$SLUG" php artisan tinker --execute='
use App\Models\Prisoner;

$slug = getenv("SLUG");
$p = Prisoner::withoutGlobalScopes()->where("slug", $slug)->first();
if (! $p) { echo "NOT FOUND: {$slug}\n"; exit(1); }

echo "name:        {$p->name}\n";
echo "slug:        {$p->slug}\n";
echo "photo (db):  ".($p->photo ?: "(none)")."\n";

if (! $p->photo) { echo "\nNo photo set on the record -- the apply script has not run.\n"; exit(0); }

$live = storage_path("app/public/{$p->photo}");
echo "live file:   {$live}\n";
if (is_file($live)) {
    echo "  exists:    yes\n";
    echo "  size:      ".filesize($live)." bytes\n";
    echo "  modified:  ".date("Y-m-d H:i:s", filemtime($live))."\n";
    echo "  md5:       ".md5_file($live)."\n";
} else {
    echo "  exists:    NO -- record points at a missing file\n";
}

$repo = base_path("database/data/photos/{$slug}.jpg");
echo "repo source: {$repo}\n";
if (is_file($repo)) {
    echo "  size:      ".filesize($repo)." bytes\n";
    echo "  md5:       ".md5_file($repo)."\n";
} else {
    echo "  exists:    no source committed under that name\n";
}

if (is_file($live) && is_file($repo)) {
    echo "\n";
    if (md5_file($live) === md5_file($repo)) {
        echo "VERDICT: the new photo IS installed. If the page still shows the old\n";
        echo "one it is browser/CDN cache -- hard-refresh (Ctrl/Cmd+Shift+R).\n";
    } else {
        echo "VERDICT: the live file DIFFERS from the repo source -- the apply script\n";
        echo "has not been run (or failed). Re-run it.\n";
    }
}

echo "\npublic URL:  ".$p->photoUrl()."\n";
echo "Done.\n";
'
