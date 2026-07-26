<?php

namespace App\Filament\Resources\PrisonerResource\Pages;

use App\Filament\Resources\PrisonerResource;
use App\Http\Controllers\Api\PrisonerApiController;
use App\Models\Prisoner;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListPrisoners extends ListRecords {
    protected static string $resource = PrisonerResource::class;

    public function getMaxContentWidth(): MaxWidth {
        return MaxWidth::Full;
    }

    /**
     * Drag-and-drop reordering with GLOBAL positional numbering.
     *
     * Filament's stock implementation renumbers only the dragged page as
     * 1..N, so page 2 would collide with page 1. This override instead
     * splices the page's new arrangement back into the full sequence (after
     * the record preceding the page in the current — possibly filtered —
     * view) and renumbers every prisoner 1..N, so sort_order always equals
     * list position and duplicates self-heal on any drag.
     */
    public function reorderTable(array $order): void {
        if (! $this->getTable()->isReorderable()) {
            return;
        }

        $order = array_values($order);

        // Where the dragged page sits within the current (filtered) view.
        $perPage = $this->getTableRecordsPerPage();
        $pageOffset = is_numeric($perPage)
            ? (max(1, $this->getTablePage()) - 1) * (int) $perPage
            : 0;

        // The visible record just before the page — unchanged by a
        // within-page drag — anchors where the block re-enters globally.
        $prevId = null;
        if ($pageOffset > 0) {
            $prevId = $this->getFilteredSortedTableQuery()
                ->reorder()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->offset($pageOffset - 1)
                ->limit(1)
                ->value('id');
        }

        // Full sequence (including filtered-out / under-review records)
        // minus the dragged block, block spliced back after its anchor.
        $ids = Prisoner::withoutGlobalScopes()
            ->whereNotIn('id', $order)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('id')
            ->all();
        $at = 0;
        if ($prevId !== null) {
            $pos = array_search($prevId, $ids, true);
            $at = $pos === false ? 0 : $pos + 1;
        }
        array_splice($ids, $at, 0, $order);

        // Renumber 1..N, writing only rows whose position changed.
        $current = Prisoner::withoutGlobalScopes()->pluck('sort_order', 'id');
        DB::transaction(function () use ($ids, $current) {
            foreach ($ids as $index => $id) {
                if ((int) ($current[$id] ?? -1) !== $index + 1) {
                    Prisoner::withoutGlobalScopes()->whereKey($id)->update(['sort_order' => $index + 1]);
                }
            }
        });

        Cache::forget(PrisonerApiController::cacheKey());
    }

    protected function getHeaderActions(): array {
        return [
            Actions\Action::make('exportAll')
                ->label('Export All (JSON)')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn () => $this->exportAll()),
            Actions\CreateAction::make(),
        ];
    }

    private function exportAll(): StreamedResponse {
        $filename = 'prisoners-'.now()->format('Y-m-d').'.json';

        return response()->streamDownload(function () {
            $prisoners = Prisoner::with(['cases.institution'])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get()
                ->map(fn (Prisoner $p) => [
                    'id'           => $p->id,
                    'name'         => $p->name,
                    'first_name'   => $p->first_name,
                    'middle_name'  => $p->middle_name,
                    'last_name'    => $p->last_name,
                    'aka'          => $p->aka,
                    'slug'         => $p->slug,
                    'era'          => $p->era,
                    'state'        => $p->state,
                    'race'         => $p->race,
                    'gender'       => $p->gender,
                    'birthdate'    => optional($p->birthdate)->toDateString(),
                    'death_date'   => optional($p->death_date)->toDateString(),
                    'ideologies'   => $p->ideologies,
                    'affiliation'  => $p->affiliation,
                    'in_custody'   => $p->in_custody,
                    'released'     => $p->released,
                    'in_exile'     => $p->in_exile,
                    'description'  => $p->description,
                    'cases'        => $p->cases->map(fn ($c) => [
                        'institution'         => optional($c->institution)->name,
                        'charges'             => $c->charges,
                        'arrest_date'         => optional($c->arrest_date)->toDateString(),
                        'incarceration_date'  => optional($c->incarceration_date)->toDateString(),
                        'release_date'        => optional($c->release_date)->toDateString(),
                        'sentence'            => $c->sentence,
                        'convicted'           => $c->convicted,
                        'imprisoned_for_days' => $c->imprisoned_for_days,
                    ])->all(),
                ]);

            echo $prisoners->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }
}
