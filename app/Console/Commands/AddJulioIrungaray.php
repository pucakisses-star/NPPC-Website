<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds Julio Cesar Irungaray — a longtime Salt Lake City resident and community
 * activist detained by ICE on June 24, 2026 and placed in deportation
 * proceedings (see the Salt Lake Tribune report added to the dashboard newswire).
 *
 * Idempotent and update-capable: prisoner:add creates a missing record, then
 * this command backfills the status flags + case fields so a re-run enriches a
 * record created by an earlier run.
 */
final class AddJulioIrungaray extends Command
{
    protected $signature = 'prisoner:add-julio-irungaray';

    protected $description = 'Add Julio Cesar Irungaray (Utah activist detained by ICE)';

    private const SOURCE = 'images/prisoners/julio-cesar-irungaray.jpg';

    private const PHOTO = 'prisoners/julio-cesar-irungaray.jpg';

    public function handle(): int
    {
        $payload = [
            'name' => 'Julio Cesar Irungaray',
            'first_name' => 'Julio',
            'middle_name' => 'Cesar',
            'last_name' => 'Irungaray',
            'description' => "Julio Cesar Irungaray is a 56-year-old longtime Salt Lake City resident, father, and community activist, originally from Mexico, who had lived in Utah for more than 35 years. On June 24, 2026, U.S. Immigration and Customs Enforcement (ICE) agents detained him outside his home as he returned from his night job, and he was placed in deportation proceedings while he was appealing an earlier removal order. His family said he has diabetes and feared for his safety in detention; roughly 90 supporters rallied for him the following evening, with more demonstrations planned. The Department of Homeland Security said he had prior convictions for driving under the influence and had entered the country in 1991.",
            'state' => 'Utah',
            'gender' => 'Male',
            'ideologies' => ['Immigrant rights'],
            'era' => '2020s',
            'in_custody' => true,
            'released' => false,
            'cases' => [[
                'charges' => 'Detained by U.S. Immigration and Customs Enforcement (ICE) and placed in deportation proceedings. A longtime Utah resident and community activist, he was taken into custody outside his home while appealing an earlier removal order; supporters and family say he was targeted and fear for his health in detention. DHS cited prior DUI convictions and a 1991 entry to the United States.',
                'arrest_date' => '2026-06-24',
                'incarceration_date' => '2026-06-24',
            ]],
        ];

        $this->call('prisoner:add', ['json' => json_encode($payload)]);

        $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first();
        if (! $prisoner) {
            $this->warn('No Irungaray record found after prisoner:add — nothing to enrich.');

            return self::SUCCESS;
        }

        $prisoner->in_custody = true;
        $prisoner->released = false;

        $source = public_path(self::SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
            $prisoner->photo = self::PHOTO;
            $this->info('Copied photo to public disk: '.self::PHOTO);
        } else {
            $this->warn('Source image not found: public/'.self::SOURCE);
        }
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if ($case) {
            $caseData = $payload['cases'][0];
            foreach (['charges', 'arrest_date', 'incarceration_date'] as $f) {
                if (! empty($caseData[$f])) {
                    $case->{$f} = $caseData[$f];
                }
            }
            $case->save();
        }

        $this->info("Done. {$prisoner->name} ensured (in ICE custody since 2026-06-24). View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
