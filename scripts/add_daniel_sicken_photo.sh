#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_daniel_sicken_photo.sh
#
# Downloads a 1987 press photo of Daniel Sicken (Minuteman III
# Plowshares) from a public eBay listing, crops out the handwritten
# caption strip on the right side, and attaches the resulting square
# headshot to his prisoner record.
#
# Photo details:
#   - Source URL: https://i.ebayimg.com/00/s/MTYwMFgxMjAw/z/mhUAAOSwkNReTSyD/$_57.JPG
#   - Original credit: George Martell, dated September 30, 1987,
#     Westborough, MA (anti-GTE court testimony)
#   - Original dimensions: 1200 x 1600 (portrait)
#   - Crop: 800 x 800 starting at offset (50, 50) - selects the
#     printed photograph and excludes the caption strip and bottom
#     handwriting / catalog numbers
set -e

PHOTO_URL='https://i.ebayimg.com/00/s/MTYwMFgxMjAw/z/mhUAAOSwkNReTSyD/$_57.JPG?set_id=8800005007'
TMP_ORIG=/tmp/sicken_orig.jpg
PHOTO_REL="prisoners/daniel-sicken.jpg"
PHOTO_ABS="storage/app/public/${PHOTO_REL}"

mkdir -p "$(dirname "$PHOTO_ABS")"

echo "Downloading original image..."
curl -fsSL -A "Mozilla/5.0 NPPC-archive" -o "$TMP_ORIG" "$PHOTO_URL"

echo "Cropping to square headshot (800x800 +50+50)..."
if command -v convert >/dev/null 2>&1; then
    convert "$TMP_ORIG" -crop 800x800+50+50 +repage -strip -quality 90 "$PHOTO_ABS"
elif command -v magick >/dev/null 2>&1; then
    magick "$TMP_ORIG" -crop 800x800+50+50 +repage -strip -quality 90 "$PHOTO_ABS"
else
    echo "ERROR: neither 'convert' (ImageMagick) nor 'magick' is installed."
    echo "Install with: apt-get install imagemagick"
    exit 1
fi

rm -f "$TMP_ORIG"
ls -la "$PHOTO_ABS"

php artisan tinker --execute='
$p = App\Models\Prisoner::where("name", "Daniel Sicken")->first();
if (!$p) {
    echo "ERROR: Daniel Sicken not found\n";
    exit(1);
}
$p->photo = "prisoners/daniel-sicken.jpg";
$p->save();
echo "Set Daniel Sicken photo = prisoners/daniel-sicken.jpg\n";
'

echo
echo "Daniel Sicken photo add complete."
