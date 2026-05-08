#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_maduro_and_flores.sh
#
# Updates Nicolas Maduro and Cilia Flores prisoner records:
#   1. Adds Cilia Flores's date of birth (1956-10-15, Tinaquillo, Cojedes,
#      Venezuela)
#   2. Sets in_custody=false on both. Neither has been in U.S. custody;
#      Maduro is a $50M-bounty fugitive under SDNY narco-terrorism
#      indictment, Cilia is OFAC-sanctioned but not arrested. The
#      previous in_custody=true was misleading and is the reason the
#      "imprisoned for X days" widget didn't render — the model
#      computes that from incarceration_date which is null because
#      they were never incarcerated.
#   3. Downloads the canonical Wikipedia infobox photo for each via
#      the Wikipedia REST API page-summary endpoint (which follows the
#      current article image regardless of filename changes) and
#      attaches it to the prisoner record.
set -e

PRISONERS_DIR="storage/app/public/prisoners"
mkdir -p "$PRISONERS_DIR"

fetch_wikipedia_photo() {
    # $1 = URL-encoded Wikipedia article title (e.g. "Nicol%C3%A1s_Maduro")
    # $2 = output absolute path
    local title="$1"
    local out="$2"
    local api="https://en.wikipedia.org/api/rest_v1/page/summary/${title}"
    local summary
    summary=$(curl -fsSL -A "Mozilla/5.0 NPPC-archive (https://nppc.org)" "$api" 2>/dev/null) || return 1
    local img
    img=$(php -r '
        $j = json_decode(stream_get_contents(STDIN));
        echo $j->originalimage->source ?? $j->thumbnail->source ?? "";
    ' <<< "$summary")
    if [ -z "$img" ]; then return 1; fi
    echo "  Photo: $img"
    curl -fsSL -A "Mozilla/5.0 NPPC-archive (https://nppc.org)" -o "$out" "$img" || return 1
    return 0
}

# ---- Maduro photo ----
MADURO_PHOTO_REL="prisoners/nicolas-maduro.jpg"
MADURO_PHOTO_ABS="${PRISONERS_DIR}/nicolas-maduro.jpg"
echo "Fetching Maduro photo..."
if fetch_wikipedia_photo "Nicol%C3%A1s_Maduro" "$MADURO_PHOTO_ABS"; then
    echo "  -> $MADURO_PHOTO_ABS"
else
    echo "  WARNING: Maduro photo download failed; skipping photo update."
    MADURO_PHOTO_REL=""
fi

# ---- Cilia Flores photo ----
FLORES_PHOTO_REL="prisoners/cilia-flores.jpg"
FLORES_PHOTO_ABS="${PRISONERS_DIR}/cilia-flores.jpg"
echo "Fetching Cilia Flores photo..."
if fetch_wikipedia_photo "Cilia_Flores" "$FLORES_PHOTO_ABS"; then
    echo "  -> $FLORES_PHOTO_ABS"
else
    echo "  WARNING: Cilia Flores photo download failed; skipping photo update."
    FLORES_PHOTO_REL=""
fi

# ---- Update prisoner rows ----
php artisan tinker --execute='
$maduro = App\Models\Prisoner::where("name", "Nicolas Maduro Moros")
    ->orWhere("name", "Nicolás Maduro Moros")
    ->orWhere("name", "like", "%Maduro Moros%")
    ->first();
if ($maduro) {
    $maduro->in_custody = false;
    $maduro->released = false;
    if ("'"$MADURO_PHOTO_REL"'" !== "") $maduro->photo = "'"$MADURO_PHOTO_REL"'";
    $maduro->birthdate = $maduro->birthdate ?: "1962-11-23";
    $maduro->save();
    echo "Maduro: photo={$maduro->photo}, birthdate={$maduro->birthdate}, in_custody={$maduro->in_custody}\n";
} else {
    echo "Maduro: not found\n";
}

$cilia = App\Models\Prisoner::where("name", "Cilia Adela Flores de Maduro")
    ->orWhere("name", "Cilia Flores")
    ->orWhere("aka", "like", "%Cilia Flores%")
    ->first();
if ($cilia) {
    $cilia->birthdate = "1956-10-15";
    $cilia->in_custody = false;
    $cilia->released = false;
    if ("'"$FLORES_PHOTO_REL"'" !== "") $cilia->photo = "'"$FLORES_PHOTO_REL"'";
    $cilia->save();
    echo "Cilia Flores: photo={$cilia->photo}, birthdate={$cilia->birthdate}, in_custody={$cilia->in_custody}\n";
} else {
    echo "Cilia Flores: not found\n";
}
'

echo "Done."
