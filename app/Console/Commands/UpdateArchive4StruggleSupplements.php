<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

final class UpdateArchive4StruggleSupplements extends Command {
    protected $signature = 'archive:update-4struggle-supplements';
    protected $description = 'Repoint /archive supplement links (Court Solidarity, Legal Solidarity Handbook) to in-repo PDFs';

    public function handle(): int {
        $page = Page::where('slug', 'archive')->first();

        if (! $page) {
            $this->error('Archive page not found.');

            return self::FAILURE;
        }

        $map = [
            '/storage/archive/courtsolidarity.pdf' => '/pdfs/4strugglemag/courtsolidarity.pdf',
            '/storage/archive/legalsolidarityhandbook.pdf' => '/pdfs/4strugglemag/legalsolidarityhandbook.pdf',
        ];

        $body = $page->body;
        $replaced = 0;

        foreach ($map as $from => $to) {
            $count = 0;
            $body = str_replace($from, $to, $body, $count);
            $replaced += $count;
            $this->line(basename($to).": {$count} replacement(s)");
        }

        $page->body = $body;
        $page->save();

        $this->info("Done. {$replaced} link(s) updated.");

        return self::SUCCESS;
    }
}
