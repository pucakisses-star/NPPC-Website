<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * The Riverside, California case — Gary Lawton and Zurebu Gardner, two Black
 * community activists falsely charged with the April 1971 murder of two
 * Riverside police officers. Supported by the Riverside Political Prisoners
 * Defense Committee, they were tried repeatedly through the early 1970s, with
 * trials ending in hung juries (reportedly 11-1 for acquittal) and no
 * conviction. Documented in The Black Panther (April 13, 1974).
 *
 * Idempotent: skips any name already present.
 */
final class AddRiversideCase extends Command
{
    protected $signature = 'prisoners:add-riverside-case';

    protected $description = 'Add the Riverside, CA defendants Gary Lawton and Zurebu Gardner';

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
                    unset($c['institution_name'], $c['institution_city'], $c['institution_state']);
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
        $riverside = function (string $name, string $first, string $last, string $descExtra = ''): array {
            return [
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'gender' => 'Male',
                'race' => 'Black',
                'state' => 'California',
                'era' => '1970s',
                'ideologies' => ['Black liberation'],
                'affiliation' => [],
                'in_custody' => false,
                'released' => true,
                'awaiting_trial' => false,
                'description' => "{$name} was a Black community activist in Riverside, California, one of two men charged with the April 1971 murder of two Riverside police officers — a prosecution his supporters, organized as the Riverside Political Prisoners Defense Committee, denounced as a frame-up.{$descExtra} By 1974 Lawton and Gardner had been tried twice, with both trials ending in hung juries (reportedly 11 to 1 for acquittal), and a third trial was pending; the case never produced a conviction. Documented in The Black Panther (April 13, 1974).",
                'cases' => [[
                    'institution_state' => 'California',
                    'charges' => 'Murder of two Riverside, California police officers (April 1971)',
                    'convicted' => 'No — repeated trials ended in hung juries (reportedly 11-1 for acquittal); no conviction',
                ]],
            ];
        };

        return [
            $riverside('Gary Lawton', 'Gary', 'Lawton',
                ' Lawton was the central defendant, tried again and again over the killings; his wife, Charlene, was among the committee members injured in a March 1974 Riverside police attack on a defense-committee picket line.'),
            $riverside('Zurebu Gardner', 'Zurebu', 'Gardner'),
        ];
    }
}
