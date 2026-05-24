<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Backfill photos onto calendar entries that don't have an image set.
 *
 * Strategy (two passes per entry):
 *   1. Try to link the entry to a Prisoner row by matching the
 *      prisoner's full name (case-insensitive) against the entry's
 *      title. The calendar view falls back to $entry->prisoner->photo
 *      when entry.image is null — no new image fetch needed.
 *   2. For entries still without a usable image, hit Wikipedia's REST
 *      summary endpoint with a likely article title extracted from
 *      the entry. If a page_image exists, download it to the public
 *      disk and set entry.image.
 *
 * Honors --dry-run.
 */
final class CalendarBackfillPhotos extends Command {
    protected $signature = 'calendar:backfill-photos
        {--dry-run : Show proposed changes without persisting them}
        {--no-prisoner-link : Skip pass 1 (don\'t link entries to prisoners)}
        {--no-wikipedia : Skip pass 2 (don\'t fetch Wikipedia images)}';
    protected $description = 'Find calendar entries without photos; link to prisoners or fetch from Wikipedia';

    public function handle(): int {
        $dryRun = (bool) $this->option('dry-run');

        $entries = CalendarEntry::query()
            ->whereNull('image')
            ->orderBy('month')
            ->orderBy('day')
            ->get();

        $this->info('Photoless calendar entries: '.$entries->count());

        $linked = 0; $wiki = 0; $unmatched = [];

        // Pass 1 — link to existing prisoners by name match.
        if (! $this->option('no-prisoner-link')) {
            $prisoners = Prisoner::query()
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->get(['id', 'name', 'first_name', 'last_name', 'photo']);

            foreach ($entries as $entry) {
                if ($entry->prisoner_id) {
                    // Already linked; check if that prisoner has a photo.
                    $existing = $prisoners->firstWhere('id', $entry->prisoner_id);
                    if ($existing) {
                        continue;
                    }
                }

                $title = $entry->title;
                $match = $prisoners->first(function ($p) use ($title) {
                    if (! $p->name) {
                        return false;
                    }
                    // Full name match
                    if (stripos($title, $p->name) !== false) {
                        return true;
                    }
                    // First + last name appearing together (within ~30 chars)
                    if ($p->first_name && $p->last_name) {
                        $pattern = '/'.preg_quote($p->first_name, '/').'.{0,30}'.preg_quote($p->last_name, '/').'/i';
                        if (preg_match($pattern, $title)) {
                            return true;
                        }
                    }
                    return false;
                });

                if ($match) {
                    $entry->prisoner_id = $match->id;
                    if (! $dryRun) {
                        $entry->save();
                    }
                    $this->line(sprintf('  LINK   %02d-%02d %s  →  prisoner: %s', $entry->month, $entry->day, $entry->title, $match->name));
                    $linked++;
                }
            }
        }

        // Reload entries that are STILL photoless (no prisoner link OR prisoner has no photo).
        $stillPhotoless = $entries->filter(function ($e) {
            if ($e->image) {
                return false;
            }
            if ($e->prisoner_id) {
                $p = Prisoner::query()->find($e->prisoner_id);
                if ($p && $p->photo) {
                    return false;
                }
            }
            return true;
        });

        // Pass 2 — Wikipedia
        if (! $this->option('no-wikipedia')) {
            foreach ($stillPhotoless as $entry) {
                $candidates = $this->extractArticleCandidates($entry->title);
                $imageUrl = null;
                $articleTitle = null;
                foreach ($candidates as $candidate) {
                    [$imageUrl, $articleTitle] = $this->wikipediaImage($candidate);
                    if ($imageUrl) {
                        break;
                    }
                }

                if (! $imageUrl) {
                    $unmatched[] = sprintf('%02d-%02d %s', $entry->month, $entry->day, $entry->title);
                    continue;
                }

                $imagePath = 'calendar/'.Str::slug($entry->title).'.jpg';
                if (! $dryRun) {
                    try {
                        $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)'])
                            ->timeout(60)
                            ->get($imageUrl);
                        if (! $resp->successful() || strlen($resp->body()) < 2000) {
                            $unmatched[] = sprintf('%02d-%02d %s  (download failed)', $entry->month, $entry->day, $entry->title);
                            continue;
                        }
                        Storage::disk('public')->put($imagePath, $resp->body());
                        $entry->image = $imagePath;
                        $entry->save();
                    } catch (\Throwable $e) {
                        $unmatched[] = sprintf('%02d-%02d %s  (error: %s)', $entry->month, $entry->day, $entry->title, $e->getMessage());
                        continue;
                    }
                }
                $this->line(sprintf('  WIKI   %02d-%02d %s  →  %s', $entry->month, $entry->day, $entry->title, $articleTitle));
                $wiki++;
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] ' : '')."Linked {$linked} to existing prisoners.");
        $this->info(($dryRun ? '[DRY RUN] ' : '')."Backfilled {$wiki} from Wikipedia.");
        if ($unmatched) {
            $this->newLine();
            $this->warn('Still without a photo ('.count($unmatched).'):');
            foreach ($unmatched as $u) {
                $this->line('  - '.$u);
            }
        }

        if ($dryRun) {
            $this->newLine();
            $this->info('Dry run — no changes made. Re-run without --dry-run to persist.');
        }
        return self::SUCCESS;
    }

    /**
     * Extract likely Wikipedia article titles from a calendar entry title.
     * Returns an ordered list of candidates, most specific first.
     */
    private function extractArticleCandidates(string $title): array {
        $candidates = [];

        // 1. Strip common verb tails to leave a noun phrase.
        $stripped = preg_replace(
            '/\s+(arrested|convicted|murdered|sentenced|begins?|founded|hanged|executed|killed|charged|tried|dies|born|attacked|raided|bombed|seized|seizes|opens?|wins?|files?|signs?|escapes?|speaks?|defies?|releases?|imprisoned|martyred|assassinated|lynched|crushed|hauled|exposed|leaves?|flee|attack|march|raids?|elected|signed|destroys?|destroyed|founded|legalises?|legalizes?)\b.*/i',
            '',
            $title
        );

        // Pull out runs of capitalized words (proper noun phrases).
        if (preg_match_all('/(?:[A-Z][a-z\']+(?:\s+(?:and|of|de|la|y|von|van|the)\s+)?)+(?:[A-Z][a-z\']+)?/u', $stripped, $m)) {
            foreach ($m[0] as $phrase) {
                $phrase = trim($phrase);
                if (strlen($phrase) >= 4 && ! in_array(strtolower($phrase), ['us', 'usa', 'american', 'the', 'and'])) {
                    $candidates[] = $phrase;
                }
            }
        }

        // 2. Some hand-curated mappings for events whose titles are
        // descriptive rather than name-based — bumped to the FRONT of
        // the candidate list so they win over generic substrings.
        $manual = $this->manualArticleMap();
        foreach ($manual as $needle => $article) {
            if (stripos($title, $needle) !== false) {
                array_unshift($candidates, $article);
            }
        }

        return array_values(array_unique($candidates));
    }

    /**
     * @return array{0: ?string, 1: ?string}  [imageUrl, articleTitle]
     */
    private function wikipediaImage(string $articleTitle): array {
        $slug = str_replace(' ', '_', $articleTitle);
        try {
            $resp = Http::withHeaders(['User-Agent' => 'NPPC-Archive/1.0 (https://nationalpoliticalprisonercoalition.org)'])
                ->timeout(20)
                ->get('https://en.wikipedia.org/api/rest_v1/page/summary/'.rawurlencode($slug));
        } catch (\Throwable $e) {
            return [null, null];
        }
        if (! $resp->successful()) {
            return [null, null];
        }
        $data = $resp->json();
        // Prefer originalimage (full-res Commons file); fall back to
        // thumbnail. Skip if it's the default Wikipedia "no image" SVG.
        $imageUrl = $data['originalimage']['source'] ?? $data['thumbnail']['source'] ?? null;
        if (! $imageUrl || str_contains($imageUrl, 'wikipedia-logo')) {
            return [null, null];
        }
        return [$imageUrl, $data['title'] ?? $articleTitle];
    }

    /**
     * Curated overrides for event-style titles that won't otherwise
     * resolve to a useful Wikipedia article via the generic
     * proper-noun extractor.
     */
    private function manualArticleMap(): array {
        return [
            'Attica prison uprising begins'  => 'Attica Prison riot',
            'Attica prison uprising ends'    => 'Attica Prison riot',
            'MOVE house'                     => '1985 MOVE bombing',
            'Jackson State'                  => 'Jackson State killings',
            'Lucasville prison rebellion'    => 'Lucasville prison riot',
            'Camp Logan mutiny'              => 'Houston riot of 1917',
            'Sacco and Vanzetti'             => 'Sacco and Vanzetti',
            'Braintree robbery'              => 'Sacco and Vanzetti',
            'Letter from Birmingham Jail'    => 'Letter from Birmingham Jail',
            'Wounded Knee occupation'        => 'Wounded Knee Occupation',
            'Palmer Raids'                   => 'Palmer Raids',
            'COINTELPRO'                     => 'COINTELPRO',
            'J20'                            => 'J20 protest',
            'inauguration mass arrests'      => 'Trump inauguration protests',
            'MOVE'                           => '1985 MOVE bombing',
            'Bonus Army'                     => 'Bonus Army',
            'Anarchist Exclusion Act'        => 'Immigration Act of 1903',
            'PATRIOT Act'                    => 'Patriot Act',
            'Black Panther Party'            => 'Black Panther Party',
            'Empire Zinc strike'             => 'Empire Zinc strike',
            'Everett massacre'               => 'Everett massacre',
            'Centralia incident'             => 'Centralia massacre (1919)',
            'Ponce massacre'                 => 'Ponce massacre',
            'Comstock Act'                   => 'Emma Goldman',
            'Eve\'s Hangout'                 => 'Eve Adams',
            'IWW'                            => 'Industrial Workers of the World',
            'Espionage Act'                  => 'Espionage Act of 1917',
            'Dakota men hanged'              => 'Dakota War of 1862',
            'Captain Jack'                   => 'Captain Jack (Modoc leader)',
            'Modoc War'                      => 'Modoc War',
            'Newark, NJ'                     => '1974 Newark Puerto Rican riots',
            'FALN'                           => 'Fuerzas Armadas de Liberación Nacional Puertorriqueña',
            'Andrea Salsedo'                 => 'Andrea Salsedo',
            'Goldman'                        => 'Emma Goldman',
            'Berkman'                        => 'Alexander Berkman',
            'Tom Mooney'                     => 'Tom Mooney',
            'Carlo Tresca'                   => 'Carlo Tresca',
            'Yetta Stromberg'                => 'Stromberg v. California',
            'Joan Little'                    => 'Joan Little',
            'Abbie Hoffman'                  => 'Abbie Hoffman',
            'Sam Melville'                   => 'Sam Melville',
            'Helen Woodson'                  => 'Plowshares movement',
            'Wesley Everest'                 => 'Wesley Everest',
            'Steunenberg'                    => 'Frank Steunenberg',
            'Reality Winner'                 => 'Reality Winner',
            'Carter and Huggins'             => 'Bunchy Carter',
            'Bobby Hutton'                   => 'Bobby Hutton',
            'Alex Rackley'                   => 'Alex Rackley',
            'Frank Valdes'                   => 'Florida Department of Corrections',
            'Judi Bari'                      => 'Judi Bari',
            'Scopes arrested'                => 'Scopes trial',
            'Czolgosz'                       => 'Leon Czolgosz',
            'Letelier'                       => 'Orlando Letelier',
            'Mary Jones tried'               => 'Mary Jones (sex worker)',
            'Hilliard'                       => 'David Hilliard',
            'Peltier'                        => 'Leonard Peltier',
            'Rosa Parks'                     => 'Rosa Parks',
            'Mumia'                          => 'Mumia Abu-Jamal',
            'Kuwasi Balagoon'                => 'Kuwasi Balagoon',
            'Lincoln Brigade'                => 'Abraham Lincoln Brigade',
            'ACT UP'                         => 'ACT UP',
            'Pinochet'                       => 'Augusto Pinochet',
            'Sostre'                         => 'Martin Sostre',
            'Gabriel\'s'                     => 'Gabriel Prosser',
            'Fred Hampton'                   => 'Fred Hampton',
            'Maile Hampton'                  => 'Felony lynching',
            'Guam teachers'                  => 'Guam Federation of Teachers',
            'Cleaver'                        => 'Eldridge Cleaver',
            'Julius Jones'                   => 'Julius Jones (prisoner)',
            'Fonda'                          => 'Jane Fonda',
            'UFW'                            => 'United Farm Workers',
            'Huey Newton'                    => 'Huey P. Newton',
            'NYC Panther 21'                 => 'Panther 21',
            'striking Guam teachers'         => 'Guam',
            'Fort Leavenworth'               => 'Leavenworth, Kansas',
        ];
    }
}
