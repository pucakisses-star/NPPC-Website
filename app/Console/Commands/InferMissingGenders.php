<?php

namespace App\Console\Commands;

use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Fills the gender field for prisoners who have none, inferring it from the
 * first name against a curated dictionary of clearly-gendered given names.
 *
 * Deliberately conservative: it sets a gender ONLY when the first name is an
 * unambiguous match. Initials ("J.", "A. W."), titles other than Mr./Mrs.,
 * unisex names (Lee, Pat, Sidney, Jordan, …) and unrecognized names are left
 * untouched and reported, so a wrong guess never overwrites a real person's
 * record. Existing genders are never changed. Run with --dry to preview.
 */
final class InferMissingGenders extends Command
{
    protected $signature = 'prisoners:infer-missing-genders {--dry : Preview without writing} {--show-skipped : List every record left unset}';

    protected $description = 'Infer and set gender from first name for prisoners with no gender';

    private const MALE = 'aaron abe abraham aditya adolph adolphus al albert albin alex alexander ammon amos andre andres anthony antonin archie armin arthur august ben benedict benjamin benton bernard bert bill braulio bryce burt caesar carl charles charlie chester christian christopher clarence clark clinton clyde colin conrad corey cornelius cris curt cyril damean damion dan daniel danny dante dave david dean delbert dennis devonte don earl ed eddy edgar eduardo edward edwin elbert elias elmer emanuel emil emmanuel enrique eric ernest erwin esmond ezra felix florencio floyd forest forrest francis frank franz fred frederick frits fritz gabriel garrett george gerard gerhard giovanni godfrey gregory grover gus gustav gustave hans harold harry heinrich henry herbert hercules herman holger homer howard hugo ignatz ira isaac isadore israel ivan jabari jack jackson jacob jacques james jerome jerry jesse jim joe johannes john johnny jonathan jose joseph joshua judah julius karl khalil kyle lawrence leo leonard leroy librado lincoln linwood lonnie louis ludwig lyman manuel mark martin max mayer michael mickey mike milan morris mortimer moses muhammad myron nathan nathaniel nicholas nick norm norris olin omar orville oscar otto paul percy perley pete peter phil phillip phineas pierce pierre pietro placido rakem ralph raymond reuben richard robert ronald roy rudolf rudolph sam samuel scott seth shamar sherman sigfrid sigmund silas simon solomon stanley stephen steven talib ted terrence tetsuji theo theodore thomas tobe tom tommy tony torazo tyler valentine van vernon vicente victor vincent vincente virgil vladimir wallace walter warren wenzel wilfred wilhelm will william wilt zachary zeb';

    private const FEMALE = 'agnes amy angela anna antoinette assunta audrey ayla barbara bertha beth brenda cara carmela carolyn catherine celeste clara concetta cynthia deborah devonna deyanna donna dorothea dorothy edith elizabeth ellen emily emma esther ethel evelyn filomena florence frances frieda gabriella gertrude giovanna gladys grace hazel helen henrietta ida joan joanna josephine kateri katherine laura lauren lillian lotta louise lucia lynette mabel margaret maria mary michelle minnie nancy nicole paige pamela paula paulette pearl rachel rosa rose ruth sadie samantha sandra sarah shante sharon shirley susan tina viola wilhelmina zainab';

    // Unisex / noisy tokens we must never guess.
    private const SKIP = 'alexis although amost angel artell ashanti billie carol casey channel clure cyan dana de earlja fornandous hedin hulet jamie jean jessie jordan kenyatta kim laurri lee linn lynn maukt mena monserrate morgan obe oliva pat robin sandy semaj shelby sidney sioux taylor terry';

    private const TITLES_FEMALE = ['mrs', 'ms', 'miss'];

    private const TITLES_MALE = ['mr', 'mister', 'sir'];

    private const TITLES_SKIP = ['rev', 'dr', 'lt', 'capt', 'col', 'gen', 'sgt', 'prof', 'fr', 'gov', 'sen'];

    public function handle(): int
    {
        $male = array_flip(explode(' ', self::MALE));
        $female = array_flip(explode(' ', self::FEMALE));
        $skip = array_flip(explode(' ', self::SKIP));

        $dry = (bool) $this->option('dry');

        $setM = 0;
        $setF = 0;
        $skipped = [];

        Prisoner::withUnderReview()
            ->where(fn ($q) => $q->whereNull('gender')->orWhere('gender', ''))
            ->orderBy('name')
            ->chunkById(500, function ($prisoners) use ($male, $female, $skip, $dry, &$setM, &$setF, &$skipped) {
                foreach ($prisoners as $p) {
                    [$gender, $reason] = $this->infer($p, $male, $female, $skip);

                    if ($gender === null) {
                        $skipped[] = [$p->name, $reason];

                        continue;
                    }

                    if (! $dry) {
                        // Query-builder update: sets the column without firing
                        // model hooks (no slug regeneration, no age recompute).
                        Prisoner::withUnderReview()->whereKey($p->getKey())->update(['gender' => $gender]);
                    }

                    $gender === 'Male' ? $setM++ : $setF++;
                }
            });

        $verb = $dry ? 'Would set' : 'Set';
        $this->info("{$verb}: Male={$setM}, Female={$setF}. Left unset: ".count($skipped).'.');

        $byReason = [];
        foreach ($skipped as [$name, $reason]) {
            $byReason[$reason] = ($byReason[$reason] ?? 0) + 1;
        }
        foreach ($byReason as $reason => $n) {
            $this->line("  unset [{$reason}]: {$n}");
        }

        if ($this->option('show-skipped')) {
            $this->line('');
            foreach ($skipped as [$name, $reason]) {
                $this->line("  [{$reason}] {$name}");
            }
        } else {
            $this->comment('  (run with --show-skipped to list every unset record)');
        }

        if (! $dry && ($setM + $setF) > 0) {
            Cache::forget(PrisonerApiController::cacheKey());
        }

        return self::SUCCESS;
    }

    /** @return array{0: 'Male'|'Female'|null, 1: string} */
    private function infer(Prisoner $p, array $male, array $female, array $skip): array
    {
        $raw = trim((string) $p->first_name);
        if ($raw === '') {
            $raw = trim(explode(' ', trim((string) $p->name))[0] ?? '');
        }
        if ($raw === '') {
            return [null, 'empty'];
        }

        $bare = strtolower(rtrim($raw, '.'));               // "Mrs." -> "mrs"
        $norm = preg_replace('/[^a-z]/', '', strtolower($raw)); // strip to letters

        if ($norm === '') {
            return [null, 'empty'];
        }
        if (strlen($norm) === 1) {
            // Initials-first names are assumed male, EXCEPT when a spelled-out
            // given name sits between the initials and the surname (e.g.
            // "F. Emily Semple", "J. Emma Martin") — honor that name's gender.
            $tokens = preg_split('/\s+/', trim((string) $p->name)) ?: [];
            $middles = array_slice($tokens, 1, max(0, count($tokens) - 2));
            foreach ($middles as $t) {
                $tn = preg_replace('/[^a-z]/', '', strtolower($t));
                if (strlen($tn) < 2) {
                    continue; // another initial
                }
                if (isset($female[$tn])) {
                    return ['Female', 'initial-middle'];
                }
                if (isset($male[$tn])) {
                    return ['Male', 'initial-middle'];
                }
            }

            return ['Male', 'initial-assumed'];
        }
        if (in_array($bare, self::TITLES_FEMALE, true)) {
            return ['Female', 'title'];
        }
        if (in_array($bare, self::TITLES_MALE, true)) {
            return ['Male', 'title'];
        }
        if (in_array($bare, self::TITLES_SKIP, true)) {
            return [null, 'title-skip'];
        }
        if (isset($skip[$norm])) {
            return [null, 'ambiguous'];
        }
        if (isset($male[$norm])) {
            return ['Male', 'name'];
        }
        if (isset($female[$norm])) {
            return ['Female', 'name'];
        }

        return [null, 'unknown'];
    }
}
