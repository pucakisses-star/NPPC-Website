#!/usr/bin/env bash
#
# Add a "Wheatland Hop Riot" sub-topic under the Industrial Workers of the World
# topic (Topics > Organizations > Industrial Workers of the World). The body is
# read from a file so its apostrophes survive intact:
#   database/data/topics/wheatland-hop-riot.body.html
#
# Idempotent: matched by slug under the IWW parent; created if absent, body
# refreshed if present. Run from the repo root:
#   bash database/data/add-wheatland-topic.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$parent = \App\Models\Topic::where("slug", "industrial-workers-of-the-world")->first();
if (! $parent) { echo "IWW parent topic not found; aborting.\n"; return; }

$body = file_get_contents(base_path("database/data/topics/wheatland-hop-riot.body.html"));

$t = \App\Models\Topic::where("slug", "wheatland-hop-riot")->first();
if (! $t) {
    $maxSort = (int) \App\Models\Topic::where("parent_id", $parent->id)->max("sort_order");
    $t = new \App\Models\Topic();
    $t->slug = "wheatland-hop-riot";
    $t->title = "Wheatland Hop Riot";
    $t->parent_id = $parent->id;
    $t->sort_order = $maxSort + 1;
    $t->published = true;
    echo "Creating sub-topic under {$parent->title}.\n";
} else {
    if (empty($t->parent_id)) { $t->parent_id = $parent->id; }
    echo "Updating existing sub-topic.\n";
}
$t->body = $body;
$t->save();
echo "Saved topic: /topics/{$t->slug}\n";
echo "Done.\n";
'

echo
echo "Done. Wheatland Hop Riot sub-topic added under the IWW."
