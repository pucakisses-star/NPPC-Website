#!/usr/bin/env bash
#
# Create the record for Samuel H. Kashinowitz -- a Los Angeles Communist who,
# with Ben Dobbs and Henry Steinberg, was sentenced in 1949 to one year in jail
# for contempt after refusing to answer a federal grand jury investigating
# Communism in Los Angeles -- and attach his portrait cropped from the 1949
# UCLA/LA Times press photo (ark 21198/zz0002vqh8).
#
# Idempotent. Run from the repo root:
#   bash database/data/add-samuel-kashinowitz.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", ["samuel h. kashinowitz"])->first();
if (! $p) {
    $p = Prisoner::create([
        "name" => "Samuel H. Kashinowitz", "first_name" => "Samuel", "last_name" => "Kashinowitz",
        "gender" => "Male", "state" => "California", "era" => "1940s",
        "ideologies" => ["Communism"], "affiliation" => ["Communist Party USA"],
        "in_custody" => false, "released" => true,
        "description" => "Samuel H. Kashinowitz was a Los Angeles Communist who, together with Ben Dobbs and Henry Steinberg, was sentenced in 1949 to one year in jail for contempt after refusing to answer the questions of a federal grand jury investigating Communism in Los Angeles.",
    ]);
    echo "created Samuel H. Kashinowitz (slug {$p->slug}).\n";
} else {
    echo "Samuel H. Kashinowitz already exists (slug {$p->slug}).\n";
}

if (! $p->cases()->where("charges", "like", "%grand jury%")->exists()) {
    $c = $p->cases()->create([
        "charges" => "Contempt of a federal grand jury investigating Communism in Los Angeles, for refusing to answer its questions (1949).",
        "convicted" => "Convicted of contempt and sentenced to one year, 1949",
        "sentence" => "One year in jail for contempt (exact custody dates not documented).",
    ]);
    $c->setPartialDate("sentenced_date", 1949, null, null);
    $c->save();
    echo "  added 1949 contempt case.\n";
}

// Attach the portrait if he has none.
$src = base_path("database/data/photos/nonfree/samuel-kashinowitz.jpg");
if (is_file($src) && empty($p->photo)) {
    $dstRel = "prisoners/{$p->slug}.jpg";
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
