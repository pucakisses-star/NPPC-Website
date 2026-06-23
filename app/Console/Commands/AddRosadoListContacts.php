<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Backfills BOP register numbers and mailing addresses for six prisoners from a
 * prisoner-support contact list (the Puerto Rican / FALN grand-jury resisters
 * Julio & Andrés Rosado, María Cueto, Ricardo Romero and Steven Guerra, plus
 * Silvia Baraldini). The addresses are stored in the per-prisoner `address`
 * field (the schema's only per-person address; the "Mailing address" shown on a
 * profile is institution-level/shared). Keyed by slug; only fills blank fields,
 * so it won't overwrite anything already set. Idempotent; --dry-run previews.
 *
 * Note: two P.O. box numbers were cut off in the source image and completed from
 * the standard facility addresses — Bastrop (Box 1010) and Ray Brook (Box 9000).
 */
final class AddRosadoListContacts extends Command
{
    protected $signature = 'prisoners:add-rosado-list-contacts {--dry-run : Preview without saving}';

    protected $description = 'Add BOP numbers and mailing addresses for the FALN-resisters/Baraldini support-list group';

    /** @var array<string,array{inmate_number?:string,address:string}> slug => fields */
    private const CONTACTS = [
        'julio-rosado' => [
            'inmate_number' => '19793-053',
            'address' => "Federal Correctional Institution\nP.O. Box 888\nAshland, KY 41101",
        ],
        'andres-rosado' => [
            'inmate_number' => '19794-053',
            'address' => "Federal Correctional Institution\nTexarkana, TX 75501",
        ],
        'maria-cueto' => [
            'address' => "Federal Correctional Institution\nP.O. Box 1000\nPleasanton, CA 94566",
        ],
        'ricardo-romero' => [
            'address' => "Federal Correctional Institution\nP.O. Box 1010\nBastrop, TX 78602",
        ],
        'steven-guerra' => [
            'inmate_number' => '15883-053',
            'address' => "FCI Ray Brook\nP.O. Box 9000\nRay Brook, NY 12977",
        ],
        'silvia-baraldini' => [
            'address' => "Federal Correctional Institution\nP.O. Box 1000\nPleasanton, CA 94566",
        ],
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updated = 0;
        $notFound = 0;

        foreach (self::CONTACTS as $slug => $fields) {
            $prisoner = Prisoner::withUnderReview()->where('slug', $slug)->first();
            if (! $prisoner) {
                $this->warn("Not found: {$slug}");
                $notFound++;

                continue;
            }

            $changes = [];
            if (! empty($fields['inmate_number']) && empty($prisoner->inmate_number)) {
                $changes['inmate_number'] = $fields['inmate_number'];
            }
            if (! empty($fields['address']) && empty($prisoner->address)) {
                $changes['address'] = $fields['address'];
            }

            if (! $changes) {
                $this->line("  no change (already set): {$prisoner->name}");

                continue;
            }

            if ($dryRun) {
                $this->line("  would update {$prisoner->name}: ".implode(', ', array_keys($changes)));
            } else {
                $prisoner->fill($changes)->save();
                $this->info("  updated {$prisoner->name}: ".implode(', ', array_keys($changes)));
            }
            $updated++;
        }

        $this->info("\nDone".($dryRun ? ' (dry run)' : '').". updated={$updated} notFound={$notFound}");

        return self::SUCCESS;
    }
}
