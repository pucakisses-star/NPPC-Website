#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_elizabeth_catlett_photo.sh
#
# Downloads Elizabeth Catlett's Wikipedia infobox photo and attaches
# it to her prisoner record.
set -e

PHOTO_REL="prisoners/elizabeth-catlett.jpg"
PHOTO_ABS="storage/app/public/${PHOTO_REL}"

mkdir -p "$(dirname "$PHOTO_ABS")"

if [ ! -s "$PHOTO_ABS" ]; then
    SUMMARY=$(curl -fsSL -A "Mozilla/5.0 NPPC-archive (https://nppc.org)" \
        "https://en.wikipedia.org/api/rest_v1/page/summary/Elizabeth_Catlett")
    IMG=$(php -r '
        $j = json_decode(stream_get_contents(STDIN));
        echo $j->originalimage->source ?? $j->thumbnail->source ?? "";
    ' <<< "$SUMMARY")
    if [ -z "$IMG" ]; then
        echo "ERROR: could not resolve Wikipedia image URL"
        exit 1
    fi
    echo "Downloading: $IMG"
    curl -fsSL -A "Mozilla/5.0 NPPC-archive (https://nppc.org)" -o "$PHOTO_ABS" "$IMG"
fi

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Elizabeth Catlett")
    ->orWhere("name", "like", "%Elizabeth Catlett%")
    ->first();
if (! $p) { echo "Elizabeth Catlett: NOT FOUND\n"; exit; }
$p->photo = "'"$PHOTO_REL"'";
$p->save();
echo "Updated: {$p->name} -> photo={$p->photo}\n";
'

echo "Done."
