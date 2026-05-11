<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

final class UpdateArchive4StruggleLinks extends Command {
    protected $signature = 'archive:update-4struggle-links';
    protected $description = 'Repoint /archive 4StruggleMag links from /storage/archive/ to the in-repo /pdfs/4strugglemag/ files';

    public function handle(): int {
        $page = Page::where('slug', 'archive')->first();

        if (! $page) {
            $this->error('Archive page not found.');

            return self::FAILURE;
        }

        $map = [
            11 => '4sm11collated.pdf',
            12 => '4sm12collated.pdf',
            13 => '4sm13collated.pdf',
            14 => '4sm14collated.pdf',
            15 => '4sm15collated1.pdf',
            16 => '4sm16collated.pdf',
            17 => '4sm17collated.pdf',
            18 => '4sm18collated.pdf',
            19 => '4sm19collated.pdf',
            20 => '4sm20collated2.pdf',
            21 => '4sm21collated.pdf',
        ];

        $body = $page->body;
        $replaced = 0;

        foreach ($map as $issue => $filename) {
            $from = "/storage/archive/4strugglemag-issue-{$issue}.pdf";
            $to = "/pdfs/4strugglemag/{$filename}";
            $count = 0;
            $body = str_replace($from, $to, $body, $count);
            $replaced += $count;
            $this->line("issue {$issue}: {$count} replacement(s)");
        }

        $page->body = $body;
        $page->save();

        $this->info("Done. {$replaced} link(s) updated.");

        return self::SUCCESS;
    }
}
