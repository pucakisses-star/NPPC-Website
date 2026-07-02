#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/update_uconn26_gallery.sh
# Wraps the 3 billboard images in the UConn 26 article into a responsive gallery.
set +e

cat > /tmp/update_uconn26_gallery.php << 'PHPEOF'
<?php

$article = \App\Models\Article::where('slug', 'nppc-launches-billboard-campaign-uconn-26')->firstOrFail();
$old = $article->body;

$gallery = '<style>
.uconn26-gallery { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin:1.5em 0; }
.uconn26-gallery a { display:block; overflow:hidden; border-radius:6px; background:#0a0a0a; }
.uconn26-gallery img { width:100%; height:210px; object-fit:cover; display:block; transition:transform 0.25s ease; }
.uconn26-gallery a:hover img { transform:scale(1.04); }
@media (max-width:640px) { .uconn26-gallery { grid-template-columns:1fr; } .uconn26-gallery img { height:220px; } }
</style>
<div class="uconn26-gallery">
<a href="/storage/images/2a966bb0-5bdd-4b07-9d17-40ab4d4e8d43.png" target="_blank" rel="noopener"><img src="/storage/images/2a966bb0-5bdd-4b07-9d17-40ab4d4e8d43.png" width="1536" height="1024" loading="lazy" alt="NPPC UConn 26 billboard"></a>
<a href="/storage/images/4dd589b0-2830-41b0-9b2b-a9f6e103e6a5.png" target="_blank" rel="noopener"><img src="/storage/images/4dd589b0-2830-41b0-9b2b-a9f6e103e6a5.png" width="1536" height="1024" loading="lazy" alt="NPPC UConn 26 billboard"></a>
<a href="/storage/images/67600a86-fa36-4917-8532-d6b68e934e04.png" target="_blank" rel="noopener"><img src="/storage/images/67600a86-fa36-4917-8532-d6b68e934e04.png" width="1536" height="1024" loading="lazy" alt="NPPC UConn 26 billboard"></a>
</div>';

// Replace the <p> containing all 3 images with the gallery
$new = preg_replace(
    '/<p>\s*<img[^>]*2a966bb0[^>]*>.*?<\/p>/s',
    $gallery,
    $old
);

if ($new === null || $new === $old) {
    echo 'WARNING: Pattern not found in body — dumping image context:' . PHP_EOL;
    $pos = strpos($old, '2a966bb0');
    if ($pos !== false) {
        echo substr($old, max(0, $pos - 100), 400) . PHP_EOL;
    } else {
        echo '  Image slug not found in body at all.' . PHP_EOL;
    }
    exit(1);
}

$article->body = $new;
$article->save();
echo 'Gallery installed successfully.' . PHP_EOL;
PHPEOF

echo "Updating UConn 26 article to add image gallery..."
php artisan tinker --execute="require '/tmp/update_uconn26_gallery.php';"
echo "Done."
