<?php

namespace App\Console\Commands;

use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Second-pass dedup across the full prisoner database: removes 20 confirmed
 * duplicate records (each the redundant copy of a person who already has a more
 * complete entry). Deletes strictly by UUID, clearing related cases/podcast/
 * calendar rows first. Before deleting a copy, if the kept record has no photo
 * but the copy does, the photo path is copied to the keeper so nothing is lost
 * (this applies to Reies López Tijerina). Idempotent — skips any id already gone.
 *
 * NOT touched: the "James Johnson" pair, which is two different people
 * (Mohamman Koti / James Carter Johnson vs. an Oregon prisoner).
 */
final class RemoveDuplicatePrisoners2 extends Command
{
    protected $signature = 'prisoners:remove-duplicates-2';

    protected $description = 'Remove 20 confirmed duplicate prisoner records (full-DB second pass, by id)';

    /** [remove_id, keep_id, label] */
    private const PAIRS = [
        ['0253c4d0-b943-4b2a-a289-c1f93aaabd04', '32f32ede-2221-483f-92a0-c784933fb2bd', 'Albert Nuh Washington'],
        ['8f3efc31-f191-4ed2-8f74-250f871b4b78', '3daa625b-ed7f-475b-b485-cb2d18c8867f', 'Alicia Rodriguez'],
        ['5de7286f-4350-4cd3-b47a-ae2fdb404818', '6b80a3b8-9a43-4cce-9e0b-cc4c5abd4a46', 'Antonio Camacho Negron'],
        ['aa78407c-0956-4f40-9e1b-5fe23ef9a773', 'e4dc673f-8462-43e6-8d9a-5f685158ed28', 'Brian "Jacob" Church'],
        ['f6754634-2604-4586-82fa-5d84a19b34fb', '677cf9d7-0fcf-488e-81b2-b8e718b919d5', 'Carmen Valentin'],
        ['8090d31a-0ae2-4fcf-b154-518348cf41f8', 'a3de2e67-d962-4200-a87a-b56cd88e2d40', 'Edwin Cortes'],
        ['20ca2497-ebde-4cd2-93ee-2de63377f143', 'a8309d36-445a-4d64-ac6d-3c3c435928a0', 'Enrique Flores Magon'],
        ['3ca3c8f2-0034-4f94-8b0b-415b27e04f8b', '07d56d61-3516-49cd-96f5-261ae120f6e9', 'Filiberto Ojeda Rios'],
        ['10784e0c-a76f-403a-af07-aff8673961c3', '447a47a4-10d1-4b9e-a0c3-0173f2aa1343', 'Haydee Beltran Torres'],
        ['a00510cd-864b-4f9f-84ee-89c9b675e9bc', '7e3b4c73-3d16-4de5-b939-90a8758d4f9b', 'Ida Luz Rodriguez'],
        ['4c4e8b70-96fc-4d39-9f39-ab416a94fb77', '4336e9f8-943f-4f43-9dad-d3e16ed12c0a', 'Johnny "Imani" Harris'],
        ['f6536d9d-a825-40f7-8df0-ae3d6247bfd2', 'e32ec29a-9116-49a8-be77-fcd7b76a5384', 'Jose Perez Gonzalez'],
        ['0bd1a8c0-83bd-4ed9-956c-b287b688cee0', '08b447db-c294-4708-8eab-90d965419ca2', 'Luce Guillen-Givins'],
        ['0e4882a6-0ff1-471d-b569-6f35b03089db', 'd378c51b-31e2-4855-9e82-d1c42c53ff9a', "Luke O'Donovan"],
        ['6826d7e1-27db-45b3-a9fd-6357116d0a0d', '7593507d-edda-4e92-ac77-afbe1373d7c5', 'Ramsey Muniz'],
        ['c822f5e0-6f2f-4f43-9e73-9378881f7d6b', '5dd65e4d-1736-495a-8f53-66fd174e92ae', 'Reies Lopez Tijerina'],
        ['36f81385-1cb0-4fce-9b3a-9bcf1c4838ac', 'e842107e-3c6a-414d-b264-cfa0bccf13ab', 'Ricardo Jimenez'],
        ['4d355a20-7081-4837-8b3f-46c3d63d8daf', '0dbe4562-920e-4ab3-9dc3-295173c22d5d', 'Roberto Jose Maldonado'],
        ['155edcfa-1299-409d-83d4-9cae56d31d67', '9bf1a379-c047-4247-b9f2-ebaf609f52bc', 'Tearra NaAsia Guthrie'],
        ['c1b73baa-fa25-4dd5-8891-ace0331508a4', '5f86511d-f41d-4b7f-a66f-2dab9adca622', 'Yu Kikumura'],
    ];

    public function handle(): int
    {
        $removed = 0;

        foreach (self::PAIRS as [$removeId, $keepId, $label]) {
            $loser = Prisoner::withoutGlobalScopes()->find($removeId);
            if (! $loser) {
                $this->line("Already gone, skipping: {$label}");

                continue;
            }

            // Preserve the photo if the keeper lacks one but the copy has it.
            $keeper = Prisoner::withoutGlobalScopes()->find($keepId);
            if ($keeper && empty($keeper->photo) && ! empty($loser->photo)) {
                $keeper->photo = $loser->photo;
                $keeper->save();
                $this->info("  Copied photo to keeper ({$keeper->name}).");
            }

            $loser->cases()->delete();
            $loser->podcastEpisodes()->delete();
            $loser->calendarEntries()->delete();
            $loser->delete();

            $this->info("Removed duplicate: {$label}  ({$loser->slug})");
            $removed++;
        }

        $this->info("\nDone. Removed {$removed} duplicate record(s).");

        return self::SUCCESS;
    }
}
