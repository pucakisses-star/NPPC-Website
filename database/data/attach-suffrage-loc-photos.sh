#!/usr/bin/env bash
#
# Attach public-domain portraits of NWP suffrage prisoners, cropped to
# head-and-shoulders from the Library of Congress "Records of the National
# Woman's Party" collection (the LOC Gallery of Suffrage Prisoners, mnwp
# items). Source crops live in database/data/photos/suffrage/<key>.jpg.
#
# Each is matched to its record by EXACT name; only prisoners with NO existing
# photo are touched. Group photos and scenes where the individual could not be
# cleanly isolated/identified were rejected upstream and are not included.
#
# Idempotent. Run AFTER prisoners:apply-suffrage-roster (so every roster
# person exists), from the repo root:
#   bash database/data/attach-suffrage-loc-photos.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

// crop-file key => exact prisoner record name
$map = [
    "abby-scott-baker"      => "Abby Scott Baker",
    "agnes-morey"           => "Agnes H. Morey",
    "anne-martin"           => "Anne Martin",
    "caroline-spencer"      => "Dr. Caroline E. Spencer",
    "catherine-boyle"       => "Catherine Boyle",
    "edith-ainge"           => "Edith Mary Ainge",
    "elizabeth-green-kalb"  => "Elizabeth Green Kalb",
    "elizabeth-stuyvesant"  => "Elizabeth Stuyvesant",
    "gertrude-crocker"      => "Gertrude Crocker",
    "hazel-hunkins"         => "Hazel Hunkins",
    "iris-calderhead"       => "Iris Calderhead Walker",
    "janet-fotheringham"    => "Janet Fotheringham",
    "joy-young"             => "Joy Young",
    "kate-heffelfinger"     => "Kate Heffelfinger",
    "lillian-ascough"       => "Lillian Ascough",
    "lucy-ewing"            => "Lucy Ewing",
    "mary-gertrude-fendall" => "Mary Gertrude Fendall",
    "matilda-young"         => "Matilda Young",
    "minnie-hennessy"       => "Minnie Hennessy",
    "minnie-quay"           => "Mrs. R. B. Quay",
    "natalie-gray"          => "Natalie Gray",
    "nell-mercer"           => "Nell Mercer",
    "nina-samarodin"        => "Nina Samarodin",
];

$set = 0; $skipHas = 0; $skipMiss = 0;
foreach ($map as $key => $name) {
    $src = base_path("database/data/photos/suffrage/{$key}.jpg");
    if (! is_file($src)) { echo "  no source file for {$key}\n"; continue; }

    $p = Prisoner::withoutGlobalScopes()->whereRaw("LOWER(name) = ?", [strtolower($name)])->first();
    if (! $p) { echo "  not found: {$name} (run prisoners:apply-suffrage-roster first?)\n"; $skipMiss++; continue; }

    if (! empty($p->photo)) { echo "  skip (already has photo): {$p->name}\n"; $skipHas++; continue; }

    $dstRel = "prisoners/{$p->slug}.jpg";
    $dstAbs = storage_path("app/public/{$dstRel}");
    File::ensureDirectoryExists(dirname($dstAbs));
    File::copy($src, $dstAbs);
    $p->photo = $dstRel;
    $p->save();
    echo "  set photo: {$p->name} -> {$dstRel}\n";
    $set++;
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone. set={$set}, skipped_have_photo={$skipHas}, not_found={$skipMiss}\n";
'

echo
echo "Done. LOC suffrage portraits attached."
