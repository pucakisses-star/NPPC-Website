<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Boyd E. Payton, the Textile Workers Union of America (TWUA) southern
 * regional director convicted in a 1959–60 "dynamite conspiracy" frame-up during
 * the Harriet-Henderson Cotton Mills strike (Henderson, NC) and later pardoned by
 * Gov. Terry Sanford. Surfaced while reading the Labor Today archive (which cited
 * his framing in 1974); sourced to the Wikipedia/NCpedia profiles. Idempotent.
 */
class AddBoydPayton extends Command {
    protected $signature = 'prisoners:add-boyd-payton';
    protected $description = 'Add Boyd E. Payton (TWUA), framed in the 1958–59 Harriet-Henderson textile strike';

    private const BIO = <<<'TXT'
Boyd Ellsworth Payton (April 21, 1908 – 1984) was a regional director of the Textile Workers Union of America (TWUA), heading the union's southern region from Charlotte, North Carolina. He became one of the most prominent labor frame-up cases of the postwar South after the bitter 1958–59 strike at the Harriet-Henderson Cotton Mills in Henderson, North Carolina.

TWUA Locals 578 and 584 struck in November 1958 after the mills refused to renew an arbitration clause; the company reopened in February 1959 with strikebreakers, touching off violence that included some sixteen bombings, more than 150 arrests, and an assault on Payton himself. In June 1959 Payton and seven others were charged with conspiring to dynamite a Carolina Power & Light Company substation and to destroy two mill buildings. The case was widely regarded as a frame-up of the union's leadership; by Payton's account, the only evidence the state had against him was a telephone call in which he warned the person on the line that the phone was bugged.

Payton was convicted in 1960 and sentenced to six to ten years in prison. Governor Terry Sanford commuted the sentences on July 4, 1961, and granted Payton a full pardon on December 31, 1964. Payton recounted the ordeal in his memoir, "Scapegoat: Prejudice / Politics / Prison." The case remained a touchstone for the labor and civil-rights movements; in 1974 the National Alliance Against Racist and Political Repression cited Payton's framing alongside more recent prosecutions as it organized against political repression in the South.
TXT;

    public function handle(): int {
        if (Prisoner::where('name', 'Boyd Payton')->exists()) {
            $this->error('Boyd Payton already exists.');
            return self::FAILURE;
        }

        DB::transaction(function () {
            $prisoner = Prisoner::create([
                'name'           => 'Boyd Payton',
                'first_name'     => 'Boyd',
                'last_name'      => 'Payton',
                'description'    => self::BIO,
                'gender'         => 'Male',
                'birthdate'      => '1908-04-21',
                'state'          => 'North Carolina',
                'era'            => '1950s',
                'ideologies'     => ['Labor', 'Trade unionism'],
                'affiliation'    => ['Textile Workers Union of America (TWUA)'],
                'in_custody'     => false,
                'released'       => true,
                'awaiting_trial' => false,
            ]);

            PrisonerCase::create([
                'prisoner_id' => $prisoner->id,
                'charges'     => 'Conspiracy to dynamite a Carolina Power & Light Company electrical substation and to destroy two textile-mill buildings during the 1958–59 Harriet-Henderson Cotton Mills strike — a charge widely regarded as a frame-up of the union\'s leadership (Payton said the only evidence against him was a phone call in which he warned a caller that the line was bugged). Charged with seven others in June 1959.',
                'convicted'   => 'Yes — convicted in 1960 in North Carolina superior court and sentenced to six to ten years. Governor Terry Sanford commuted the sentences on July 4, 1961 and granted Payton a full pardon on December 31, 1964.',
                'sentence'    => 'Six to ten years in prison (1960); commuted July 4, 1961; full pardon December 31, 1964.',
            ]);

            $this->info("Added: {$prisoner->name} (slug: {$prisoner->slug})");
        });

        return self::SUCCESS;
    }
}
