<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Corrects the Gary Watson entry. He was previously (and wrongly) described as
 * a 2012 Delaware police-shooting case. In fact Gary Watson (#098990) is the
 * last still-imprisoned member of the "Smyrna Five" (S-5) — radical Black
 * prisoners who rose up against the administration of Delaware's maximum-
 * security prison at Smyrna (the Delaware Correctional Center, now the James T.
 * Vaughn Correctional Center) amid the wave of prison rebellion that followed
 * the August 1971 killing of George Jackson; the five were convicted in 1973
 * of rioting and assault. Idempotent; matches by slug then name.
 */
final class FixGaryWatson extends Command
{
    protected $signature = 'prisoners:fix-gary-watson';

    protected $description = 'Correct the Gary Watson entry to the "Smyrna Five" case';

    public function handle(): int
    {
        $p = Prisoner::withoutGlobalScopes()->where('slug', 'gary-watson')->first()
            ?? Prisoner::withoutGlobalScopes()->where('name', 'Gary Watson')->first();

        if (! $p) {
            $this->error('No Gary Watson record found.');

            return self::FAILURE;
        }

        $p->fill([
            'inmate_number' => '098990',
            'state' => 'Delaware',
            'era' => '1970s',
            'ideologies' => ['Black liberation'],
            'affiliation' => ['Smyrna Five'],
            'in_custody' => true,
            'released' => false,
            'under_review' => false,
            'description' => 'Gary Watson (inmate #098990) is the last still-imprisoned member of the "Smyrna Five" (S-5) — a group of radical Black prisoners who rose up against the administration of Delaware\'s maximum-security prison at Smyrna (the Delaware Correctional Center, today the James T. Vaughn Correctional Center) amid the wave of prison rebellion that followed the August 1971 killing of George Jackson at San Quentin. The five were convicted in 1973 of rioting and assault. Watson has remained imprisoned ever since — held in the prison\'s Security Housing Unit (SHU 17) — making him one of the longest-held prisoners of that era\'s prison-radical movement.',
        ]);
        $p->save();
        $this->info("Updated prisoner: {$p->name}");

        $case = $p->cases()->first();
        if ($case) {
            $case->charges = 'Rioting and assault on the prison administration at the Delaware Correctional Center in Smyrna, as one of the "Smyrna Five."';
            $case->convicted = 'Yes (Smyrna Five case, 1973)';
            $case->sentence = null;                            // clear the incorrect "106 years"
            $case->setPartialDate('incarceration_date', 1973); // Smyrna Five conviction
            $case->save();
            $this->info('Corrected his case to the Smyrna Five (1973).');
        } else {
            $this->warn('No case found to correct.');
        }

        $this->info("View: /prisoner/{$p->slug}");

        return self::SUCCESS;
    }
}
