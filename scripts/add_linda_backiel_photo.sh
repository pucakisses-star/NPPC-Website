#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_linda_backiel_photo.sh
#
# Downloads a photo of Linda Backiel from underthevolcano.org and
# attaches it to her prisoner record.
set -e

PHOTO_URL="https://underthevolcano.org/wp-content/uploads/2024/01/linda-768x1024.jpg"
PHOTO_REL="prisoners/linda-backiel.jpg"
PHOTO_ABS="storage/app/public/${PHOTO_REL}"

mkdir -p "$(dirname "$PHOTO_ABS")"

if [ ! -s "$PHOTO_ABS" ]; then
    echo "Downloading: $PHOTO_URL"
    curl -fsSL -A "Mozilla/5.0 NPPC-archive (https://nppc.org)" -o "$PHOTO_ABS" "$PHOTO_URL"
fi

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Linda Backiel")
    ->orWhere("name", "like", "%Linda Backiel%")
    ->first();
if (! $p) { echo "Linda Backiel: NOT FOUND\n"; exit; }
$p->photo = "'"$PHOTO_REL"'";
$p->save();
echo "Updated: {$p->name} -> photo={$p->photo}\n";
'

echo "Done."
