<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds the two people from the Wikipedia "Native American suffragists"
 * category who were criminally charged in connection with their Native-rights
 * activism and were missing from the database. The category is overwhelmingly
 * advocacy figures; only these two were prosecuted.
 *
 * Included:
 *  - Laura Cornelius Kellogg — arrested 1913 (Oklahoma/Colorado) and 1925
 *    (Canada) on contested fraud/impersonation charges arising from her Six
 *    Nations land-claim fundraising; a directed acquittal in 1914 and charges
 *    dropped in 1925 (no conviction).
 *  - Tillie Paul — criminally charged in 1923 with "inducing an Indian to vote"
 *    for helping Charlie Jones cast a ballot at Wrangell, Alaska, the landmark
 *    Alaska Native voting-rights case; charges dismissed after his acquittal.
 *
 * Excluded after vetting (no arrest/charge for political activity): Marie Louise
 * Bottineau Baldwin, Lou Bruce (likely mis-categorized; the bearers are male and
 * not suffragists), Bula Croker (her only arrest was a 1943 family burglary
 * dispute, dismissed), Lucy Nicolar Poolaw, Wa-o-wa-wa-na-onk / Harriet Maxwell
 * Converse (an adopted non-Native ally; no personal arrest), and Zitkala-Ša.
 * Borderline, left out: William Paul (disbarred 1937 in a politically tinged fee
 * dispute, but a professional sanction — no criminal arrest; the voting-case
 * arrest in his family was his mother Tillie's, not his). Idempotent.
 */
final class AddNativeSuffragists extends Command
{
    protected $signature = 'prisoners:add-native-suffragists';

    protected $description = 'Add criminally charged Native American suffragists from the Wikipedia category that were missing';

    public function handle(): int
    {
        $added = 0;
        $skipped = 0;

        foreach ($this->records() as $r) {
            if (Prisoner::withUnderReview()->where('name', $r['name'])->exists()) {
                $this->warn("Exists, skipping: {$r['name']}");
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($r) {
                $cases = $r['cases'] ?? [];
                unset($r['cases']);
                $prisoner = Prisoner::create($r);
                foreach ($cases as $c) {
                    $c['prisoner_id'] = $prisoner->id;
                    PrisonerCase::create($c);
                }
            });

            $this->info("Added: {$r['name']}");
            $added++;
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }

    private function records(): array
    {
        return [
            [
                'name' => 'Laura Cornelius Kellogg',
                'first_name' => 'Laura',
                'middle_name' => 'Cornelius',
                'last_name' => 'Kellogg',
                'aka' => 'Minnie Kellogg; Wynnogene; Laura Miriam Cornelius Kellogg',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'New York',
                'era' => '1910s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Society of American Indians'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Laura Cornelius Kellogg (1880–1949) was an Oneida author, orator, and a 1911 co-founder of the Society of American Indians who became the driving force behind the 20th-century Six Nations land claims. She traveled widely and raised funds to pursue Haudenosaunee land restoration, and her sovereignty work made her a target of fraud/impersonation prosecutions in 1913 (Oklahoma/Colorado) and 1925 (Canada). She was acquitted by a directed verdict in 1914 and had the 1925 charges dropped — never convicted — but the arrests cost her her standing in the Society of American Indians.',
                'cases' => [
                    [
                        'charges' => 'Obtaining money under false pretenses and impersonating federal officials — arising from her Six Nations land-claim fundraising and Indigenous-rights investigations; arrested at Pawhuska, Oklahoma',
                        'arrest_date' => '1913-10-11',
                        'convicted' => 'Directed acquittal — found not guilty (U.S. District Court, Denver, January 31, 1914)',
                    ],
                    [
                        'charges' => 'Arrested in Canada over fundraising for the Six Nations / Oneida land claims (alleged fraudulent solicitation), with her husband Orrin Kellogg and Chief Wilson K. Cornelius',
                        'convicted' => 'Charges dropped / not sustained (no conviction)',
                    ],
                ],
            ],
            [
                'name' => 'Tillie Paul',
                'first_name' => 'Tillie',
                'last_name' => 'Paul',
                'aka' => 'Matilda Kinnon Paul Tamaree; Tillie Paul Tamaree',
                'gender' => 'Female',
                'race' => 'Native American',
                'state' => 'Alaska',
                'era' => '1920s',
                'ideologies' => ['Indigenous Sovereignty', 'Native American'],
                'affiliation' => ['Alaska Native Sisterhood'],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => 'Matilda "Tillie" Paul Tamaree (1863–1952) was a Tlingit missionary, educator, and Alaska Native-rights pioneer whose organizing helped seed the Alaska Native Brotherhood and Sisterhood. On November 7, 1922 she acted as interpreter to help Charlie Jones, a Tlingit man, cast a ballot at Wrangell, Alaska, and was criminally charged in 1923 with "inducing an Indian to vote." Her son, attorney William Paul, defended the case; after Jones was acquitted, her charges were dismissed — a foundational victory for Alaska Native voting rights.',
                'cases' => [[
                    'charges' => 'Indicted/charged with "inducing an Indian to vote" (aiding and abetting illegal voting) for helping Charlie Jones cast a ballot at Wrangell, Alaska — the landmark Alaska Native voting-rights case',
                    'convicted' => 'Charges dismissed October 9, 1923, after Charlie Jones was found not guilty',
                ]],
            ],
        ];
    }
}
