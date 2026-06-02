<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Seeds the dashboard newswire with a curated set of external links to
 * reputable human-rights, press-freedom and criminal-justice organisations
 * that NPPC works alongside. These render in the newswire/ticker (opening in
 * a new tab) next to internal Articles. Each link is keyed by URL via
 * firstOrCreate, so the command is safe to re-run and never duplicates.
 *
 * Manage or replace them afterwards in /admin (Dashboard Links).
 */
final class AddNewswireLinks extends Command {
    protected $signature = 'newswire:add-links';
    protected $description = 'Add a curated set of external human-rights links to the dashboard newswire';

    /**
     * Evergreen, stable resource pages — [title, url, source]. Ordered newest
     * first; publish dates are spread back across recent weeks below so they
     * fill the newswire's default ~30-day window and the timeline.
     */
    private array $links = [
        ['Imprisoned Journalists Database', 'https://cpj.org/imprisoned/', 'Committee to Protect Journalists'],
        ['Latest Human Rights News', 'https://www.amnesty.org/en/latest/', 'Amnesty International'],
        ['World Report on Human Rights', 'https://www.hrw.org/world-report', 'Human Rights Watch'],
        ['Human Rights Defenders at Risk', 'https://www.frontlinedefenders.org/', 'Front Line Defenders'],
        ['Writers at Risk: Freedom to Write', 'https://pen.org/', 'PEN America'],
        ['World Press Freedom Index', 'https://rsf.org/en', 'Reporters Without Borders'],
        ['Defending Civil Liberties', 'https://www.aclu.org/', 'ACLU'],
        ['Protecting Press Freedom', 'https://freedom.press/', 'Freedom of the Press Foundation'],
        ['Constitutional Rights Litigation', 'https://ccrjustice.org/', 'Center for Constitutional Rights'],
        ['Movement Defense & Know Your Rights', 'https://www.nlg.org/', 'National Lawyers Guild'],
        ['U.S. Incarceration Data & Reports', 'https://www.prisonpolicy.org/', 'Prison Policy Initiative'],
        ['Sentencing & Criminal Justice Reform', 'https://www.sentencingproject.org/', 'The Sentencing Project'],
    ];

    public function handle(): int {
        $today   = Carbon::today();
        $created = 0;

        foreach ($this->links as $i => [$title, $url, $source]) {
            // Stagger publish dates a couple of days apart, newest first, so the
            // batch lands inside the newswire's default ~30-day window.
            $publishedAt = $today->copy()->subDays($i * 2 + 1)->setTime(9, 0);

            $link = DashboardLink::firstOrCreate(
                ['url' => $url],
                ['title' => $title, 'source' => $source, 'published_at' => $publishedAt],
            );

            if ($link->wasRecentlyCreated) {
                $created++;
                $this->line("  + {$title}  —  {$source}");
            } else {
                $this->line("  · already present: {$title}");
            }
        }

        $this->info("Done. {$created} new link(s) added, " . (count($this->links) - $created) . ' already present.');

        return self::SUCCESS;
    }
}
