<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Adds Dr. Edward S. Sharpe — a physician and anti-war ("Peace") Democrat from
 * Salem, New Jersey, detained without trial at Fort Delaware as a political
 * prisoner from August 1862 to January 17, 1863 during the Civil War crackdown
 * on Northern war opponents under the suspension of habeas corpus — and sets his
 * profile photo (from the Salem County Historical Society portrait in the Fort
 * Delaware Society's PDF).
 *
 * Idempotent and update-capable: prisoner:add creates a missing record, then this
 * command backfills the status flags + case dates and attaches the photo, so a
 * re-run enriches a record created by an earlier run.
 */
final class AddEdwardSharpe extends Command
{
    protected $signature = 'prisoner:add-edward-sharpe';

    protected $description = 'Add Dr. Edward S. Sharpe (anti-war Democrat, Fort Delaware political prisoner) with photo';

    private const SOURCE = 'data/photos/legacy/edward-sharpe.jpg';

    private const PHOTO = 'prisoners/edward-sharpe.jpg';

    public function handle(): int
    {
        $payload = [
            'name' => 'Edward S. Sharpe',
            'first_name' => 'Edward',
            'last_name' => 'Sharpe',
            'aka' => 'Dr. Edward S. Sharpe',
            'description' => "Dr. Edward S. Sharpe was a physician in Salem, New Jersey, and an anti-war (\"Peace\") Democrat who was detained at Fort Delaware as a political prisoner from August 1862 until January 17, 1863. He was among the Northern opponents of the war held without trial during the Civil War under the Lincoln administration's suspension of the writ of habeas corpus.",
            'state' => 'Delaware',
            'gender' => 'Male',
            'ideologies' => ['Anti-war', 'Peace Democrats'],
            'affiliation' => ['Democratic Party'],
            'era' => '1860s',
            'in_custody' => false,
            'released' => true,
            'cases' => [[
                'institution_name' => 'Fort Delaware',
                'institution_city' => 'Delaware City',
                'institution_state' => 'Delaware',
                'charges' => 'Detained without trial as a political prisoner — a physician and anti-war ("Peace") Democrat from Salem, New Jersey — during the Civil War crackdown on Northern war opponents under the suspension of habeas corpus.',
                'arrest_date' => '1862-08-01',
                'incarceration_date' => '1862-08-01',
                'release_date' => '1863-01-17',
                'convicted' => 'Held without trial as a political prisoner.',
                'sentence' => 'Detained roughly five months (August 1862 – January 17, 1863) without trial.',
            ]],
        ];

        $this->call('prisoner:add', ['json' => json_encode($payload)]);

        $prisoner = Prisoner::withoutGlobalScopes()->where('name', $payload['name'])->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'like', '%Edward S. Sharpe%')->first();

        if (! $prisoner) {
            $this->warn('No Edward S. Sharpe record found after prisoner:add — nothing to enrich.');

            return self::SUCCESS;
        }

        $prisoner->in_custody = false;
        $prisoner->released = true;

        $source = database_path(self::SOURCE);
        if (is_file($source)) {
            Storage::disk('public')->put(self::PHOTO, file_get_contents($source));
            $prisoner->photo = self::PHOTO;
            $this->info('Copied photo to public disk: '.self::PHOTO);
        } else {
            $this->warn('Source image not found: database/'.self::SOURCE);
        }
        $prisoner->save();

        $case = $prisoner->cases()->first();
        if ($case) {
            $caseData = $payload['cases'][0];
            foreach (['charges', 'arrest_date', 'incarceration_date', 'release_date', 'convicted', 'sentence'] as $f) {
                if (! empty($caseData[$f])) {
                    $case->{$f} = $caseData[$f];
                }
            }
            $case->save();
        }

        $this->info("Done. {$prisoner->name} ensured with photo. View: /prisoner/{$prisoner->slug}");

        return self::SUCCESS;
    }
}
