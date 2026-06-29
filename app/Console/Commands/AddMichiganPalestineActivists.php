<?php

namespace App\Console\Commands;

use App\Models\DashboardLink;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Adds the four pro-Palestine activists arrested by the FBI in June 2026 —
 * Zainab Hakim, Jonathan Zou, Paige Feyock, and Colin Weger — to the prisoner
 * database, and files the Michigan Daily report on the case as a /dashboard
 * newswire link.
 *
 * Per the source, the four were arrested Wednesday, June 10, 2026 and released
 * two days later on Friday, June 12, 2026 on $10,000 unsecured bonds. They were
 * charged with conspiracy to transmit threats in interstate and foreign
 * commerce; two of the four were additionally charged with intimidating a
 * federal witness (which two is not specified, so the per-person charge text
 * only states the common count). Case venue: U.S. District Court, Eastern
 * District of Michigan, Detroit.
 *
 * Idempotent: the dashboard link is updateOrCreate-by-URL; prisoner:add refuses
 * duplicates, and this command then backfills the in-custody/release flags and
 * the case arrest/incarceration/release dates so a re-run enriches records
 * created by an earlier run.
 */
final class AddMichiganPalestineActivists extends Command
{
    protected $signature = 'prisoners:add-michigan-palestine-activists';

    protected $description = 'Add the four June 2026 pro-Palestine activists (Hakim, Zou, Feyock, Weger) + the dashboard link';

    private const ARREST_DATE = '2026-06-10';      // Wednesday

    private const RELEASE_DATE = '2026-06-12';     // Friday

    public function handle(): int
    {
        // 1) Dashboard newswire link for the source article.
        $url = 'https://www.michigandaily.com/news/news-briefs/four-pro-palestine-activists-released-on-bond-after-fbi-arrests/';
        $link = DashboardLink::updateOrCreate(
            ['url' => $url],
            [
                'title' => 'Four pro-Palestine activists released on bond after FBI arrests',
                'source' => 'The Michigan Daily',
                'category' => 'prosecution',
                'published_at' => Carbon::parse('2026-06-14'),
                'location_label' => 'Detroit, MI',
                'lat' => 42.3314,
                'lng' => -83.0458,
            ],
        );
        $this->info(($link->wasRecentlyCreated ? 'Added' : 'Refreshed')." dashboard link: {$link->title}");

        // 2) The four activists.
        $names = [
            ['Zainab', 'Hakim'],
            ['Jonathan', 'Zou'],
            ['Paige', 'Feyock'],
            ['Colin', 'Weger'],
        ];

        $charges = 'Conspiracy to transmit threats in interstate and foreign commerce (up to five years per count) in connection with a pro-Palestine protest campaign at the University of Michigan — graffiti, broken windows, butyric acid, and threatening messages directed at University officials. Federal prosecutors additionally charged two of the four defendants with intimidating a federal witness (up to 20 years).';

        foreach ($names as [$first, $last]) {
            $name = "{$first} {$last}";

            $description = "{$name} is one of four pro-Palestine activists arrested by the FBI in June 2026 in connection with a protest campaign at the University of Michigan. Federal prosecutors charged the four with conspiracy to transmit threats in interstate and foreign commerce — citing graffiti, broken windows, butyric acid, and threatening messages directed at University officials — and additionally charged two of them with intimidating a federal witness. Arrested on Wednesday, June 10, 2026, all four were released two days later, on Friday, June 12, 2026, on \$10,000 unsecured bonds with conditions such as GPS monitoring, curfews, or home detention, after Magistrate Judge Anthony Patti found they had not acted on their statements. The case was filed in the U.S. District Court for the Eastern District of Michigan in Detroit.";

            $payload = [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'description' => $description,
                'state' => 'Michigan',
                'ideologies' => ['Pro-Palestine activism', 'Palestinian solidarity'],
                'affiliation' => ['Pro-Palestine movement'],
                'era' => '2020s',
                'in_custody' => false,
                'released' => true,
                'cases' => [
                    [
                        'institution_name' => 'Theodore Levin United States Courthouse',
                        'institution_city' => 'Detroit',
                        'institution_state' => 'Michigan',
                        'charges' => $charges,
                        'arrest_date' => self::ARREST_DATE,
                        'incarceration_date' => self::ARREST_DATE,
                        'release_date' => self::RELEASE_DATE,
                        'judge' => 'Magistrate Judge Anthony Patti',
                    ],
                ],
            ];

            $this->call('prisoner:add', ['json' => json_encode($payload)]);

            // prisoner:add won't update an existing record, so enrich here too
            // (flags + case dates) for safe re-runs.
            $prisoner = Prisoner::withoutGlobalScopes()->where('name', $name)->first();
            if (! $prisoner) {
                continue;
            }
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            $case = $prisoner->cases()->first();
            if ($case) {
                $case->arrest_date = self::ARREST_DATE;
                $case->incarceration_date = self::ARREST_DATE;
                $case->release_date = self::RELEASE_DATE;
                if (empty($case->charges)) {
                    $case->charges = $charges;
                }
                $case->save();
            }
        }

        $this->info("\nDone. Dashboard link ensured and 4 activists added (arrested Wed 2026-06-10, released Fri 2026-06-12).");

        return self::SUCCESS;
    }
}
