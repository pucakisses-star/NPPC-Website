<?php

namespace App\Filament\Resources\PrisonerResource\Pages;

use App\Filament\Resources\PrisonerResource;
use App\Models\Prisoner;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListPrisoners extends ListRecords {
    protected static string $resource = PrisonerResource::class;

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
