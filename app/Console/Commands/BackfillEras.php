<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills the `era` field for prisoners that were missing one, chosen by
 * reading each record's bio and case dates:
 *   - date-derived where a case/arrest year exists (e.g. Occupy-era 2011–2013 →
 *     2010s; Iva Toguri / WWII → 1940s; Yu Kikumura 1988 → 1980s);
 *   - movement-inferred for the generic 1990 Special International Tribunal
 *     petitioners that carry no dates (MOVE / Puerto Rican / Plowshares → 1980s;
 *     New Afrikan–BLA → 1970s).
 *
 * Keyed by prisoner ID and only fills records whose era is still blank, so it
 * never overwrites an era set by hand. Idempotent; --dry-run previews.
 */
final class BackfillEras extends Command
{
    protected $signature = 'prisoners:backfill-eras {--dry-run : Preview without saving}';

    protected $description = 'Assign an era to prisoners missing one (only fills blank eras)';

    /** @var array<string,string> prisoner id => era */
    private const ERAS = [
        'a0cd207b-ebb4-4cb7-95b6-f0db9a34aec5' => '1940s', // Iva Toguri D'Aquino (WWII / 1949 trial)
        '799d10d2-8ba6-4475-a1d3-9f2e73409f89' => '2000s', // Gwendolyn Myers (arrested 2009)
        'aa78407c-0956-4f40-9e1b-5fe23ef9a773' => '2010s', // Brian Jacob Church (NATO 3, 2012)
        '6d8a59e2-ec13-47f0-a3f6-c71492c58345' => '2010s', // Matt Duran (PNW grand jury, 2012)
        'd08bae55-d5b2-4233-b645-a6b75edd61d8' => '2010s', // Katherine Olejnik (2012)
        '12d1d763-0cf0-4cf5-8518-820b7f5c9d1e' => '2010s', // Maddy Pfeiffer (2013)
        'dbaef6f9-5cb3-4fc1-b8f7-de0fabc49273' => '2010s', // Leah-Lynn Plante (2012)
        '2221216a-bada-4a0a-aa47-c99b7a242a00' => '2010s', // Pancho Ramos Stierle (Occupy Oakland, 2011)
        '7360f57e-d4d6-4245-ad67-60978065a669' => '2010s', // Mark Adams (OWS, 2011)
        '143bd05a-47db-4adf-802d-86651317ab9d' => '2010s', // Cesar Aguirre (Occupy Oakland, 2011)
        '6877769f-6a7c-4edc-b403-b8367d9adf1d' => '2010s', // Corey Donahue (Occupy Denver, 2011)
        '7561e781-a78b-48b9-8c76-eccd15bc3cfd' => '2010s', // Joshua Wollstein (Occupy Seattle, 2012)
        '9c31e533-1c3b-48c7-8b5b-ed0b93589eed' => '2010s', // Cody Ingram (Occupy Seattle, 2012)
        'b9cc9c50-355c-46bd-abd7-c59f4a994d88' => '2010s', // Andrew "Fish" Fisher (Occupy San Diego, 2011-12)
        '99d5781b-8f8e-4ab3-8ffd-d07dc0837818' => '2010s', // Aaron Minter (OWS, 2012)
        '90450803-3370-47b8-af3f-798131cb0d39' => '2010s', // Marcel "Khali" Johnson (Occupy Oakland, arr. 2011)
        'af4cec43-a60f-40a0-a8d8-43249b8d05d1' => '2010s', // Stanley Cohen (attorney, imprisoned ~2015)
        'e5fe23ea-aec5-41cc-8ea1-ef2451cff6e0' => '1980s', // Alberta Wicker Africa (MOVE)
        'd7be8a46-e05a-42a0-ac92-a68bafea868f' => '1980s', // Carlos Perez Africa (MOVE)
        '3b28c767-0e8c-43ef-bab3-581c37fc8a87' => '1980s', // Consuella Dotson Africa (MOVE)
        '51f726a3-e8b5-4f8d-9b5a-2dbe1595f1b6' => '1980s', // Michael Hill Africa (MOVE)
        'ec6c4029-aaa8-46f3-bdaf-3be797d262cb' => '1980s', // Sue Leon Africa (MOVE)
        '585f6893-b1fc-4593-af43-9fd8c87d52ff' => '1980s', // Lucy (Ida Luz) Rodríguez (FALN, arr. 1980)
        '22c1a9d8-22c2-4bc3-9f6a-cc887c59b71c' => '1980s', // Ana María Gelabert (Puerto Rican independence)
        'f61fdfa9-02dd-4093-9746-701648e87b91' => '1980s', // Dorothy Eber (Plowshares)
        '079e7c92-04b6-42f4-afa1-434afd3f1b45' => '1980s', // Jennifer Haines (Plowshares)
        'c8ce7755-5cca-4396-b431-45594a102ed0' => '1970s', // Raphael Kwesi Joseph (New Afrikan / BLA)
        '5832f591-a57e-40bc-9ced-225ebd0852af' => '1970s', // Mohaman Koti (New Afrikan / BLA)
        'bb123f55-7457-4cef-8856-3e6a88440117' => '1970s', // Ahmad Abdur Rahman (New Afrikan / BLA)
        'a1ff74a7-2e0f-4b56-83f6-08c6cc7dc840' => '1980s', // Yvonne Small (1990 Tribunal petitioner)
        '81fea1c0-8d87-4fed-898f-6318c52026d3' => '1980s', // Robert Taylor (1990 Tribunal petitioner)
        'c1b73baa-fa25-4dd5-8891-ace0331508a4' => '1980s', // Yu Kikumura (JRA, arr. 1988)
        '5db12221-b2c0-48c6-9843-7a7046243f0d' => '1970s', // Robert P. "Ed" Stover (IWW, arr. 1970)
        'c04eeb4d-fa27-45fe-8705-bd52c72b9964' => '2020s', // Sofia DeFerrari (current Oregon DOC prisoner)
        'cd7429b3-0ab0-4222-8dad-88f157f35eb5' => '2020s', // Aubrey Cottle (arrested 2025)
        'bc9e70c0-97e7-47c5-a5d2-8ae1818ccd26' => '1970s', // Jihad Abdulmumit (BLA, imprisoned ~1977)
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $set = 0;
        $alreadySet = 0;
        $notFound = 0;

        foreach (self::ERAS as $id => $era) {
            $prisoner = Prisoner::withUnderReview()->find($id);
            if (! $prisoner) {
                $this->warn("Not found: {$id}");
                $notFound++;

                continue;
            }
            if (! empty($prisoner->era)) {
                $this->line("  already has era ({$prisoner->era}), skipping: {$prisoner->name}");
                $alreadySet++;

                continue;
            }

            if ($dryRun) {
                $this->line("  would set {$era}: {$prisoner->name}");
            } else {
                $prisoner->era = $era;
                $prisoner->save();
                $this->info("  {$era}  ←  {$prisoner->name}");
            }
            $set++;
        }

        $this->info("\nDone".($dryRun ? ' (dry run)' : '').". set={$set} alreadyHadEra={$alreadySet} notFound={$notFound}");

        return self::SUCCESS;
    }
}
