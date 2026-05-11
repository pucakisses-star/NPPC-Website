<?php

declare(strict_types=1);

/**
 * Create a new "Archive" page under the existing "Learn More"
 * navigation entry. Idempotent — bails out if a page with the
 * same slug already exists. The body is a small placeholder
 * the user can replace via the Filament admin (Pages > Archive)
 * to add the magazine PDFs.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Page;

$slug = 'archive';

if (Page::where('slug', $slug)->exists()) {
    echo "Page '{$slug}' already exists. Nothing to do.\n";
    return;
}

$parent = Page::where('slug', 'learn-more')->first();
if (! $parent) {
    echo "Parent page 'learn-more' not found. Aborting — manually set parent_id later if you want it nested.\n";
    exit(1);
}

$body = <<<'HTML'
<p>Movement publications — magazine PDFs and other periodical archives that document political-prisoner cases, repression, and resistance over the last century.</p>
<p style="opacity: 0.6; font-style: italic;">Edit this page in the Filament admin (Pages → Archive) to add PDF links and descriptions.</p>
HTML;

$page = Page::create([
    'title'        => 'Archive',
    'slug'         => $slug,
    'body'         => $body,
    'parent_id'    => $parent->id,
    'show_in_nav'  => true,
]);

echo "[create] Page id={$page->id}, slug={$page->slug}, parent={$parent->title} (id={$parent->id})\n";
echo "URL: /{$page->slug}\n";
echo "Done.\n";
