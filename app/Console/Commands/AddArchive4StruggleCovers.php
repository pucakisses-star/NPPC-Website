<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

final class AddArchive4StruggleCovers extends Command {
    protected $signature = 'archive:add-4struggle-covers';
    protected $description = 'Inject cover <img> tags into each 4StruggleMag card on the /archive page (idempotent)';

    public function handle(): int {
        $page = Page::where('slug', 'archive')->first();

        if (! $page) {
            $this->error('Archive page not found.');

            return self::FAILURE;
        }

        $body = $page->body;

        $body = preg_replace_callback(
            '#(<a\b[^>]*\bdata-issue="4strugglemag-(\d+)"[^>]*>)(\s*)(<div)#',
            function (array $m): string {
                $issue = $m[2];
                $img = sprintf(
                    '<img src="/images/archive/4strugglemag/4sm%s-cover.jpg" alt="4strugglemag issue #%s cover" loading="lazy" style="width: 100%%; height: auto; aspect-ratio: 7 / 8.5; object-fit: cover; display: block; margin-bottom: 12px; border: 1px solid rgba(255,255,255,0.15);" />',
                    $issue,
                    $issue
                );

                return $m[1]."\n        ".$img.$m[3].$m[4];
            },
            $body,
            -1,
            $count
        );

        if ($body === null) {
            $this->error('Regex failure.');

            return self::FAILURE;
        }

        $page->body = $body;
        $page->save();

        $this->info("Done. {$count} cover image(s) inserted.");

        return self::SUCCESS;
    }
}
