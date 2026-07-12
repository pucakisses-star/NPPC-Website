<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Repairs calendar-entry images after a visual audit of all 97 pictured
 * entries (July 2026).
 *
 * Two failure classes were found:
 *
 *   1. Keyword-matched junk from calendar:backfill-photos' Wikipedia pass —
 *      the Pelican Bay hunger strike illustrated with a photo of a pelican,
 *      Guantánamo with a beach, the IWW's founding with a starfield, Bunchy
 *      Carter/John Huggins with an Anubis statue, Maile Hampton with a maile
 *      vine, state flags for Mississippi/Oklahoma/Guam events, skylines for
 *      Chicago and Seattle, and so on.
 *
 *   2. Broken references — 15 entries whose image paths 404 on the public
 *      disk (e.g. Rosa Parks' arrest, the Attica assault, Fred Hampton's
 *      murder).
 *
 * REPLACE swaps in curated, correctly-licensed images (all Wikimedia
 * Commons; see database/data/photos/calendar-fixes/CREDITS.md) shipped in
 * the repo. CLEAR nulls images with no good free replacement so the entry
 * falls back to its prisoner photo or the no-image treatment instead of
 * showing something wrong or broken. Entries are matched by unique title
 * fragments; idempotent and safe to re-run.
 */
final class FixCalendarImages extends Command {
    protected $signature = 'calendar:fix-images {--dry-run : Report changes without writing}';
    protected $description = 'Replace keyword-mismatched calendar images and clear broken references';

    /** title fragment => repo image (database/data/photos/calendar-fixes/). */
    private const REPLACE = [
        'Pelican Bay hunger strike'            => 'pelican-bay-hunger-strike-begins-against-solitary-confinement.jpg',
        'Guantanamo Bay detention camp opens'  => 'guantanamo-bay-detention-camp-opens.jpg',
        'Empire Zinc strike'                   => 'empire-zinc-strike-begins-in-new-mexico.jpg',
        'Freedom Summer workers go missing'    => 'mississippi-freedom-summer-workers-go-missing.jpg',
        'Oklahoma State Penitentiary uprising' => 'oklahoma-state-penitentiary-uprising-crushed.jpg',
        'Fort Leavenworth military prisoners'  => 'fort-leavenworth-military-prisoners-go-on-strike.jpg',
        'Seattle passes gun law'               => 'seattle-passes-gun-law-to-disarm-black-panthers.jpg',
        'Chicago Seven verdict'                => 'chicago-seven-verdict-returned.jpg',
        'Rosa Parks arrested in Montgomery'    => 'rosa-parks-arrested-in-montgomery.jpg',
        'Attica prison uprising ends'          => 'attica-prison-uprising-ends-with-state-assault-43-killed.jpg',
        'Cleaver flee to Algiers'              => 'eldridge-and-kathleen-cleaver-flee-to-algiers.jpg',
        'PATRIOT Act signed'                   => 'usa-patriot-act-signed-into-law.jpg',
        'Everett massacre'                     => 'everett-massacre-of-iww-members-in-washington.jpg',
        'Fred Hampton murdered'                => 'fred-hampton-murdered.jpg',
        'Marie Equi convicted'                 => 'marie-equi-convicted.jpg',
        'Goldman and Berkman speak'            => 'goldman-and-berkman-speak-at-no-conscription-rally.jpg',
        'Orangeburg massacre'                  => 'orangeburg-massacre-of-black-students-in-south-carolina.jpg',
    ];

    /** title fragment => reason (image cleared; no good free replacement). */
    private const CLEAR = [
        'Bunchy Carter and John Huggins'   => 'was an Anubis statue',
        'Maile Hampton'                    => 'was a maile vine (plant)',
        'Eve\'s Hangout'                   => 'was a modern police officer',
        'Citizens Commission burgles'      => 'file 404s',
        'Black Panthers march into California capitol' => 'file 404s',
        'Alex Rackley'                     => 'file 404s',
        'First deportation under Anarchist Exclusion'  => 'file 404s',
        'bodies of Chaney, Goodman'        => 'file 404s (June entries carry the trio portrait)',
        'general strike against Sacco'     => 'file 404s',
    ];

    public function handle(): int {
        $dry = (bool) $this->option('dry-run');
        $done = 0; $miss = 0;

        foreach (self::REPLACE as $fragment => $file) {
            $entry = CalendarEntry::where('title', 'like', '%'.$fragment.'%')->first();
            if (! $entry) {
                $this->warn("No entry matching \"{$fragment}\" — skipped.");
                $miss++;
                continue;
            }
            $src = database_path('data/photos/calendar-fixes/'.$file);
            if (! is_file($src)) {
                $this->warn("Missing repo image {$file} — skipped.");
                $miss++;
                continue;
            }
            $dest = 'calendar/'.$file;
            $bytes = file_get_contents($src);
            $disk = Storage::disk('public');
            if ($entry->image === $dest && $disk->exists($dest) && md5($disk->get($dest)) === md5($bytes)) {
                $this->line("Already fixed: {$entry->title}");
                continue;
            }
            if ($dry) {
                $this->info("Would replace image on: {$entry->title}");
                $done++;
                continue;
            }
            $disk->makeDirectory('calendar');
            $disk->put($dest, $bytes);
            $entry->image = $dest;
            $entry->save();
            $this->info("Replaced: {$entry->title}");
            $done++;
        }

        foreach (self::CLEAR as $fragment => $why) {
            $entry = CalendarEntry::where('title', 'like', '%'.$fragment.'%')->first();
            if (! $entry) {
                $this->warn("No entry matching \"{$fragment}\" — skipped.");
                $miss++;
                continue;
            }
            if ($entry->image === null) {
                $this->line("Already cleared: {$entry->title}");
                continue;
            }
            if ($dry) {
                $this->info("Would clear image ({$why}) on: {$entry->title}");
                $done++;
                continue;
            }
            $entry->image = null;
            $entry->save();
            $this->info("Cleared ({$why}): {$entry->title}");
            $done++;
        }

        $this->info("\nDone. Changed={$done}  Missing/skipped={$miss}".($dry ? '  (dry run)' : ''));

        return self::SUCCESS;
    }
}
