#!/usr/bin/env bash
#
# Rename Cornstalk to Hokoleskwa (Cornstalk kept as aka) with birth year 1720,
# death November 10, 1777, corrected bio, and his portrait from the 1872
# engraving in Frosts pictorial history (public domain, via Wikimedia
# Commons). Also rename his son Elinipsico to Allanawissica (Elinipsico kept
# as aka), birth year 1745, death November 10, 1777 -- both were murdered by
# militiamen inside Fort Randolph while held as hostages.
#
# Idempotent. Run from the repo root:
#   bash database/data/update-hokoleskwa-allanawissica.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

// ---- Hokoleskwa (Cornstalk) ----
$p = Prisoner::withoutGlobalScopes()->where("slug", "cornstalk")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Cornstalk%")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Hokoleskwa%")->first();
if (! $p) {
    echo "NOT FOUND: Cornstalk / Hokoleskwa\n";
} else {
    $p->name = "Hokoleskwa";
    $p->first_name = "Hokoleskwa";
    $p->middle_name = null;
    $p->last_name = null;
    $p->aka = "Cornstalk";
    $p->description = "Hokoleskwa, commonly called Cornstalk, was a prominent Shawnee political and military leader who advocated neutrality during the American Revolution. In October or early November 1777, Cornstalk visited Fort Randolph at Point Pleasant, West Virginia on a diplomatic mission but was detained by Captain Matthew Arbuckle as a hostage. On November 10, 1777, American militiamen murdered Cornstalk, his son Elinipsico, and two other Indigenous prisoners inside the fort.";
    $p->setPartialDate("birthdate", 1720, null, null);
    $p->setPartialDate("death_date", 1777, 11, 10);
    $p->in_custody = false;
    $p->released = false;
    $p->save();  // regenerates slug from the new name
    echo "renamed to {$p->name} (slug {$p->slug}), 1720 - 1777-11-10.\n";

    $c = $p->cases()->orderBy("created_at")->first();
    if ($c) {
        if (! $c->incarceration_date && ! $c->arrest_date) {
            $c->setPartialDate("incarceration_date", 1777, 10, null);
        }
        $c->setPartialDate("release_date", 1777, 11, 10);
        if (! $c->convicted) { $c->convicted = "Never charged -- detained as a hostage"; }
        $c->save();
        echo "  case updated: detention ends 1777-11-10 (killed in custody), days={$c->imprisoned_for_days}.\n";
    }

    $src = base_path("database/data/photos/hokoleskwa.jpg");
    $dstRel = "prisoners/{$p->slug}.jpg";
    if (is_file($src)) {
        File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
        File::copy($src, storage_path("app/public/{$dstRel}"));
        $p->photo = $dstRel;
        $p->save();
        echo "  photo set -> {$dstRel}\n";
    } else {
        echo "  PHOTO SOURCE MISSING: database/data/photos/hokoleskwa.jpg\n";
    }
}

// ---- Allanawissica (Elinipsico) ----
$s = Prisoner::withoutGlobalScopes()->where("slug", "elinipsico")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Elinipsico%")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "like", "%Allanawissica%")->first();
if (! $s) {
    echo "NOT FOUND: Elinipsico / Allanawissica\n";
} else {
    $oldPhoto = $s->photo;
    $s->name = "Allanawissica";
    $s->first_name = "Allanawissica";
    $s->middle_name = null;
    $s->last_name = null;
    $s->aka = "Elinipsico";
    $s->setPartialDate("birthdate", 1745, null, null);
    $s->setPartialDate("death_date", 1777, 11, 10);
    $s->in_custody = false;
    $s->released = false;
    $s->save();
    echo "renamed to {$s->name} (slug {$s->slug}), 1745 - 1777-11-10.\n";

    // Keep any existing photo reachable under the regenerated slug.
    if ($oldPhoto && $s->photo === $oldPhoto) {
        $oldAbs = storage_path("app/public/{$oldPhoto}");
        $newRel = "prisoners/{$s->slug}.jpg";
        if (is_file($oldAbs) && $oldPhoto !== $newRel) {
            File::copy($oldAbs, storage_path("app/public/{$newRel}"));
            $s->photo = $newRel;
            $s->save();
            echo "  photo re-pointed -> {$newRel}\n";
        }
    }

    $c = $s->cases()->orderBy("created_at")->first();
    if ($c) {
        $c->setPartialDate("release_date", 1777, 11, 10);
        if (! $c->convicted) { $c->convicted = "Never charged -- detained as a hostage"; }
        $c->save();
        echo "  case updated: detention ends 1777-11-10 (killed in custody), days={$c->imprisoned_for_days}.\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
