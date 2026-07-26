#!/usr/bin/env bash
#
# Canonical record for Samuel Harry Kasinowitz (legal name; the UCLA photo
# caption spelling "Kashinowitz" is kept as an aka). A Los Angeles Communist
# who, with Ben Dobbs and Henry Steinberg, was jailed for contempt after
# refusing to answer a federal grand jury investigating Communism in Los
# Angeles. Renames an existing "Kashinowitz" record if present (matched on the
# "nowitz" fragment) rather than duplicating, ensures the contempt case, and
# attaches his cropped portrait.
#
# Idempotent. Run from the repo root:
#   bash database/data/add-samuel-kashinowitz.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) LIKE ?", ["%nowitz%"])->first();
if (! $p) {
    $p = Prisoner::create([
        "name" => "Samuel Harry Kasinowitz", "first_name" => "Samuel", "last_name" => "Kasinowitz",
        "aka" => "Samuel H. Kashinowitz",
        "gender" => "Male", "state" => "California", "era" => "1940s",
        "ideologies" => ["Communism"], "affiliation" => ["Communist Party USA"],
        "in_custody" => false, "released" => true,
        "description" => "Samuel Harry Kasinowitz was a Los Angeles Communist who, together with Ben Dobbs and Henry Steinberg, was jailed for contempt after refusing to answer the questions of a federal grand jury investigating Communism in Los Angeles. His name is spelled Kashinowitz in the UCLA press-photo caption, but the federal appellate record uses Kasinowitz.",
    ]);
    echo "created {$p->name} (slug {$p->slug}).\n";
} else {
    $p->name = "Samuel Harry Kasinowitz";
    $p->first_name = "Samuel";
    $p->last_name = "Kasinowitz";
    $p->aka = "Samuel H. Kashinowitz";
    $p->save();  // regenerates slug from the corrected name
    echo "renamed to {$p->name} (slug {$p->slug}).\n";
}

if (! $p->cases()->where("charges", "like", "%grand jury%")->exists()) {
    $c = $p->cases()->create([
        "charges" => "Contempt of a federal grand jury investigating Communism in Los Angeles, for refusing to answer its questions.",
        "convicted" => "Jailed for contempt, 1948",
        "sentence" => "Held from October 26 to November 4, 1948.",
    ]);
    $c->setPartialDate("incarceration_date", 1948, 10, 26);
    $c->setPartialDate("release_date", 1948, 11, 4);
    $c->save();
    echo "  added 1948 contempt case.\n";
}

// Attach / re-point the portrait to the current slug.
$src = base_path("database/data/photos/nonfree/samuel-kashinowitz.jpg");
$dstRel = "prisoners/{$p->slug}.jpg";
if (is_file($src) && (empty($p->photo) || $p->photo !== $dstRel)) {
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    $p->photo = $dstRel; $p->save();
    echo "  set photo -> {$dstRel}\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
