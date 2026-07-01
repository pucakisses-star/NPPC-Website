#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_gabriel_megahey_photo.sh
set -e

echo "Downloading photo..."
curl -L "https://www.irishecho.com/uploads/article/2025/7/14012/Gabe.jpg?t=1752586533" \
  -o storage/app/public/prisoners/gabriel-megahey.jpg
chmod 644 storage/app/public/prisoners/gabriel-megahey.jpg
echo "Photo saved."

echo "Updating database..."
php artisan tinker --execute='
$p = \App\Models\Prisoner::where("slug", "gabriel-megahey")->first();
if (!$p) { echo "ERROR: prisoner not found\n"; exit(1); }
$p->update([
    "photo"     => "prisoners/gabriel-megahey.jpg",
    "birthdate" => "1943-01-01",
]);
echo "Updated: " . $p->name . "\n";
echo "Photo: " . $p->photo . "\n";
echo "Birthdate: " . $p->birthdate . "\n";
'
