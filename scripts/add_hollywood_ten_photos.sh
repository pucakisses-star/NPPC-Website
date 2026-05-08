#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_hollywood_ten_photos.sh
#
# Downloads Wikipedia infobox photos for each of the Hollywood Ten and
# attaches them to the corresponding prisoner record. Uses Wikipedia's
# REST API page-summary endpoint, which follows the current article
# image regardless of underlying filename changes.
set -e

PRISONERS_DIR="storage/app/public/prisoners"
mkdir -p "$PRISONERS_DIR"

fetch_wikipedia_photo() {
    # $1 = URL-encoded Wikipedia article title
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

# [wikipedia_title, prisoner_name_search_pattern, file_slug]
declare -a entries=(
    "Alvah_Bessie|Alvah Bessie|alvah-bessie"
    "Herbert_J._Biberman|Herbert Biberman|herbert-biberman"
    "Lester_Cole|Lester Cole|lester-cole"
    "Edward_Dmytryk|Edward Dmytryk|edward-dmytryk"
    "Ring_Lardner_Jr.|Ring Lardner Jr.|ring-lardner-jr"
    "John_Howard_Lawson|John Howard Lawson|john-howard-lawson"
    "Albert_Maltz|Albert Maltz|albert-maltz"
    "Samuel_Ornitz|Samuel Ornitz|samuel-ornitz"
    "Adrian_Scott_(producer)|Adrian Scott|adrian-scott"
    "Dalton_Trumbo|Dalton Trumbo|dalton-trumbo"
)

for entry in "${entries[@]}"; do
    IFS='|' read -r wiki_title name_pattern file_slug <<< "$entry"
    rel="prisoners/${file_slug}.jpg"
    abs="${PRISONERS_DIR}/${file_slug}.jpg"
    echo "Fetching ${name_pattern}..."

    if [ ! -f "$abs" ] || [ ! -s "$abs" ]; then
        if ! fetch_wikipedia_photo "$wiki_title" "$abs"; then
            # Try fallback without disambiguator
            base_title="${wiki_title%%_(*}"
            echo "  primary fetch failed, trying ${base_title}"
            if ! fetch_wikipedia_photo "$base_title" "$abs"; then
                echo "  WARNING: download failed for ${name_pattern}; skipping"
                continue
            fi
        fi
    else
        echo "  already downloaded -> $abs"
    fi

    php artisan tinker --execute="
\$p = App\\Models\\Prisoner::where('name', '${name_pattern}')
    ->orWhere('name', 'like', '%${name_pattern}%')
    ->first();
if (! \$p) { echo \"  PRISONER NOT FOUND\n\"; exit; }
if (! \$p->photo) {
    \$p->photo = '${rel}';
    \$p->save();
    echo \"  Attached: {\$p->name} -> {\$p->photo}\n\";
} else {
    echo \"  Already has photo: {\$p->name} -> {\$p->photo}\n\";
}
"
done

echo
echo "Done."
