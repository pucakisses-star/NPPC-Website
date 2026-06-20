<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Adds Huey P. Newton's Cuban exile and his arrest on returning home.
 *
 * In 1974 Newton was charged with the murder of Kathleen Smith and with
 * pistol-whipping his tailor Preston Callins in Oakland; he jumped bail and
 * fled to Cuba on August 30, 1974, living in exile in Havana for nearly three
 * years. He returned on July 4, 1977, was arrested on arrival, and was held
 * until released on bail on July 23, 1977. (Both Smith-murder trials later
 * ended in hung juries and the charges were dismissed in 1979.)
 *
 * His existing 1967–1970 manslaughter case is left untouched. Two cases are
 * added: the 1974–77 exile (in_exile_since/end_of_exile) and the 1977 return
 * detention. The prisoner's in_exile flag is set last, after the cases are
 * created, so the PrisonerCase saving hook does not auto-stamp in_exile_since
 * onto the detention case. Idempotent: re-running adds nothing.
 */
final class UpdateHueyNewtonExile extends Command
{
    protected $signature = 'prisoners:update-huey-newton-exile';

    protected $description = "Add Huey P. Newton's 1974–1977 Cuba exile and his 1977 return arrest";

    public function handle(): int
    {
        $p = Prisoner::withUnderReview()->where('slug', 'huey-p-newton')->first()
            ?? Prisoner::withUnderReview()->where('name', 'Huey P. Newton')->first();

        if (! $p) {
            $this->warn('Huey P. Newton not found — skipping (no-op).');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($p) {
            // 1974–1977 Cuban exile.
            $exileExists = PrisonerCase::where('prisoner_id', $p->id)
                ->whereNotNull('in_exile_since')
                ->whereDate('in_exile_since', '1974-08-30')
                ->exists();
            if ($exileExists) {
                $this->line('  Exile case already present — skipping.');
            } else {
                PrisonerCase::create([
                    'prisoner_id' => $p->id,
                    'charges' => 'Fled to Cuba after jumping bail on 1974 Oakland charges of murdering Kathleen Smith and assaulting (pistol-whipping) his tailor Preston Callins',
                    'in_exile_since' => '1974-08-30',
                    'end_of_exile' => '1977-07-04',
                    'convicted' => 'Not tried while abroad; lived in self-exile in Havana, Cuba',
                    'sentence' => 'Roughly three years in exile in Cuba (August 30, 1974 – July 4, 1977)',
                ]);
                $this->info('  Added exile case (1974-08-30 → 1977-07-04).');
            }

            // 1977 return arrest. Created while in_exile is still false so the
            // PrisonerCase saving hook does not derive in_exile_since from this
            // detention's release_date.
            $returnExists = PrisonerCase::where('prisoner_id', $p->id)
                ->whereNotNull('arrest_date')
                ->whereDate('arrest_date', '1977-07-04')
                ->exists();
            if ($returnExists) {
                $this->line('  Return-arrest case already present — skipping.');
            } else {
                PrisonerCase::create([
                    'prisoner_id' => $p->id,
                    'charges' => 'Murder of Kathleen Smith and assault on Preston Callins (Oakland, California)',
                    'arrest_date' => '1977-07-04',
                    'incarceration_date' => '1977-07-04',
                    'release_date' => '1977-07-23',
                    'convicted' => 'Arrested on his return to the United States; released on bail. Both Smith-murder trials ended in hung juries and the charges were dismissed in 1979',
                    'sentence' => 'Held about three weeks before release on bail (July 4–23, 1977)',
                ]);
                $this->info('  Added 1977 return-arrest case (1977-07-04 → 1977-07-23).');
            }

            if (! $p->in_exile) {
                $p->in_exile = true;
                $p->save();
                $this->info('  Marked as historically exiled (in_exile = true).');
            }
        });

        $this->info('Done updating Huey P. Newton.');

        return self::SUCCESS;
    }
}
