<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Corrects and fills out the five Iranian nationals whom the United States
 * released in the September 18, 2023 U.S.–Iran prisoner swap (the deal in which
 * five imprisoned Americans were freed by Iran and about $6 billion in Iranian
 * funds unfrozen). All five were already in the database but with vague
 * descriptions, empty cases, and — in two instances — wrong names
 * ("Attar Kashani" was missing his first name; "Reza Sarhangpour Kambiz"
 * conflated Kashani's first name into Sarhangpour's). This rewrites each record
 * with the documented charge, disposition, and sentence, corrects the names, and
 * records the September 18, 2023 release.
 *
 * Matched by the existing slug (stable even where the name was wrong). Idempotent
 * — re-running rewrites the same values. Sources: Al Jazeera, AP, and DOJ
 * reporting on the September 2023 swap.
 */
class FixIranSwapPrisoners extends Command
{
    protected $signature = 'prisoners:fix-iran-swap-prisoners';

    protected $description = 'Correct names and fill cases for the five Iranians released in the Sept 2023 US-Iran swap';

    private const RELEASE = '2023-09-18';

    public function handle(): int
    {
        foreach ($this->records() as $slug => $r) {
            $this->fix($slug, $r);
        }

        Cache::forget(PrisonerApiController::cacheKey());

        return self::SUCCESS;
    }

    private function fix(string $slug, array $r): void
    {
        DB::transaction(function () use ($slug, $r) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found by slug: {$slug}");

                return;
            }

            $prisoner->name = $r['name'];
            $prisoner->first_name = $r['first_name'];
            $prisoner->last_name = $r['last_name'];
            $prisoner->description = $r['description'];
            $prisoner->era = '2020s';
            if (isset($r['state'])) {
                $prisoner->state = $r['state'];
            }
            $prisoner->in_custody = false;
            $prisoner->released = true;
            $prisoner->save();

            // Fill the single (currently empty) case.
            $case = $prisoner->cases()->first() ?? new PrisonerCase(['prisoner_id' => $prisoner->id]);
            $case->fill([
                'prisoner_id' => $prisoner->id,
                'charges' => $r['charges'],
                'convicted' => $r['convicted'],
                'sentence' => $r['sentence'],
                'release_date' => self::RELEASE,
            ]);
            $case->save();

            $this->info('Fixed: '.$prisoner->name.' (slug: '.$prisoner->slug.')');
        });
    }

    private function records(): array
    {
        return [
            'mehrdad-moein-ansari' => [
                'name' => 'Mehrdad Moein Ansari',
                'first_name' => 'Mehrdad',
                'last_name' => 'Ansari',
                'state' => 'Texas',
                'description' => 'Mehrdad Moein Ansari is an Iranian national who was prosecuted in the United States for running a procurement scheme that obtained U.S. and Western electronic components with military and dual-use applications and shipped them to Iran through front companies in the United Arab Emirates and Germany, in violation of U.S. export controls and sanctions. Convicted in federal court, he was sentenced in 2021 to 63 months in prison. He was one of the five Iranians the United States released in the September 18, 2023 U.S.–Iran prisoner swap, the deal that freed five imprisoned Americans held by Iran in exchange for the release of these prisoners and the unfreezing of about $6 billion in Iranian oil revenue.',
                'charges' => 'Conspiracy and export-control/sanctions violations — for procuring U.S. and Western electronic components with military and dual-use applications and shipping them to Iran through front companies in the UAE and Germany.',
                'convicted' => 'Yes — convicted in U.S. federal court.',
                'sentence' => 'Sentenced in 2021 to 63 months in prison. Released and returned to Iran as one of the five Iranians in the September 18, 2023 U.S.–Iran prisoner swap.',
            ],
            'attar-kashani' => [
                'name' => 'Kambiz Attar Kashani',
                'first_name' => 'Kambiz',
                'last_name' => 'Attar Kashani',
                'state' => 'New York',
                'description' => 'Kambiz Attar Kashani is a dual U.S.–Iranian citizen who pleaded guilty to conspiring to illegally export U.S. goods, technology, and services to end users in Iran — including the Central Bank of Iran — using front companies in the United Arab Emirates. He was sentenced in February 2023 to 30 months in prison. He was one of the five Iranians the United States released in the September 18, 2023 U.S.–Iran prisoner swap, the deal that freed five imprisoned Americans held by Iran in exchange for the release of these prisoners and the unfreezing of about $6 billion in Iranian oil revenue.',
                'charges' => 'Conspiracy to illegally export U.S. goods, technology, and services to end users in Iran, including the Central Bank of Iran, through front companies in the UAE (a sanctions/export-control violation).',
                'convicted' => 'Yes — pleaded guilty.',
                'sentence' => 'Sentenced in February 2023 to 30 months in prison. Released in the September 18, 2023 U.S.–Iran prisoner swap.',
            ],
            'reza-sarhangpour-kambiz' => [
                'name' => 'Reza Sarhangpour Kafrani',
                'first_name' => 'Reza',
                'last_name' => 'Sarhangpour Kafrani',
                'state' => 'District of Columbia',
                'description' => 'Reza Sarhangpour Kafrani is an Iranian national who was charged in the United States in 2021 with sanctions and export-control violations for unlawfully exporting laboratory equipment — including mass spectrometers subject to sanctions and nuclear-nonproliferation controls — to Iran by routing the goods through Canada and the United Arab Emirates. His case was still pending when he was released as one of the five Iranians in the September 18, 2023 U.S.–Iran prisoner swap, the deal that freed five imprisoned Americans held by Iran in exchange for the release of these prisoners and the unfreezing of about $6 billion in Iranian oil revenue.',
                'charges' => 'Sanctions and export-control violations — for unlawfully exporting sanctioned laboratory equipment (including mass spectrometers) to Iran via Canada and the UAE.',
                'convicted' => 'Charged in 2021; the case was pending (no conviction) when he was released in the swap.',
                'sentence' => 'No sentence — released as one of the five Iranians in the September 18, 2023 U.S.–Iran prisoner swap.',
            ],
            'amin-hasanzadeh' => [
                'name' => 'Amin Hasanzadeh',
                'first_name' => 'Amin',
                'last_name' => 'Hasanzadeh',
                'state' => 'Michigan',
                'description' => 'Amin Hasanzadeh is an Iranian engineer and U.S. permanent resident who was arrested in late 2019 and indicted on charges that he stole sensitive technical and engineering data from his U.S. employer and transmitted it to his brother in Iran, along with related export/sanctions and fraud counts. As part of the September 18, 2023 U.S.–Iran prisoner swap he was granted clemency and the federal indictment against him was dismissed. The swap freed five imprisoned Americans held by Iran in exchange for the release of these five Iranians and the unfreezing of about $6 billion in Iranian oil revenue.',
                'charges' => 'Theft of trade secrets, transporting stolen property, fraud, and acting to transmit sensitive technical/engineering data from his U.S. employer to his brother in Iran (linked in the indictment to Iranian military work).',
                'convicted' => 'Indicted in 2019; not convicted — granted clemency and the indictment was dismissed as part of the swap.',
                'sentence' => 'No sentence — granted clemency and the indictment dismissed as part of the September 18, 2023 U.S.–Iran prisoner swap.',
            ],
            'kaveh-l-afrasiabi' => [
                'name' => 'Kaveh L. Afrasiabi',
                'first_name' => 'Kaveh',
                'last_name' => 'Afrasiabi',
                'description' => 'Kaveh Lotfolah Afrasiabi is an Iranian-American political scientist and author who was arrested by the FBI in January 2021 and charged with acting and conspiring to act in the United States as an unregistered agent of the government of Iran (a Foreign Agents Registration Act violation), along with related money-laundering counts, for allegedly lobbying and writing on Iran\'s behalf while secretly paid by the Iranian mission to the United Nations. He was granted clemency before any trial and released as one of the five Iranians in the September 18, 2023 U.S.–Iran prisoner swap, the deal that freed five imprisoned Americans held by Iran in exchange for the release of these prisoners and the unfreezing of about $6 billion in Iranian oil revenue.',
                'charges' => 'Acting and conspiring to act as an unregistered agent of the government of Iran (Foreign Agents Registration Act), plus related money-laundering counts, for lobbying and writing on Iran\'s behalf while paid by Iran\'s U.N. mission.',
                'convicted' => 'Charged in January 2021; not convicted — granted clemency before trial.',
                'sentence' => 'No sentence — granted clemency and released in the September 18, 2023 U.S.–Iran prisoner swap.',
            ],
        ];
    }
}
