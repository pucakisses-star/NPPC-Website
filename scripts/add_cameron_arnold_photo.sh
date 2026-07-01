#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_cameron_arnold_photo.sh
set +e

echo "Downloading Cameron Arnold photo from The Guardian..."
mkdir -p storage/app/public/prisoners
curl -sL 'https://i.guim.co.uk/img/media/54d452fb1e2d18e60b7eaee19e5648f5e6e339a6/0_0_1022_1024/master/1022.jpg?width=300&dpr=2&s=none&crop=none' \
    -o storage/app/public/prisoners/cameron-arnold.jpg

echo "Setting photo on prisoner record..."
php artisan tinker --execute="
\$p = App\Models\Prisoner::withoutGlobalScopes()->where('slug', 'cameron-arnold')->firstOrFail();
\$p->photo = 'prisoners/cameron-arnold.jpg';
\$p->save();
echo 'Updated: ' . \$p->name . PHP_EOL;
"

echo "Done."
