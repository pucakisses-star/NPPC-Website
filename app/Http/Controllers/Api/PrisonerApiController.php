<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Prisoner;
use Illuminate\Http\JsonResponse;

class PrisonerApiController extends Controller {
    /**
     * Return all prisoners with cases and institutions in the same JSON shape
     * the frontend Vue components expect (matching the old Cloudflare worker output).
     */
    public function index(): JsonResponse {
        $prisoners = Prisoner::with(['cases.institution'])
            ->orderBy('sort_order')
            ->get();

        $data = $prisoners->map(function (Prisoner $prisoner) {
            $daysImprisoned = 0;
            $daysInExile = 0;

            $cases = $prisoner->cases->map(function ($case) use (&$daysImprisoned, &$daysInExile) {
                $daysImprisoned += $case->imprisoned_for_days ?? 0;
                $daysInExile += $case->in_exile_for_days ?? 0;

                return [
                    'Indicted'             => $case->indicted,
                    'Convicted'            => $case->convicted,
                    'Sentenced Date'       => $case->sentenced_date?->format('Y-m-d'),
                    'Release Date'         => $case->release_date?->format('Y-m-d'),
                    'Charges'              => $case->charges ? array_map('trim', explode("\n", $case->charges)) : [],
                    'Prosecutor'           => $case->prosecutor,
                    'Judge'                => $case->judge,
                    'Plead'                => $case->plead,
                    'Sentence'             => $case->sentence,
                    'Institution name'     => $case->institution ? [$case->institution->name] : [],
                    'Institution city'     => $case->institution ? [$case->institution->city] : [],
                    'Institution state'    => $case->institution?->state,
                    'Institution security' => $case->institution ? [$case->institution->security] : [],
                    'Arrest Date'          => $case->arrest_date?->format('Y-m-d'),
                    'Incarceration Date'   => $case->incarceration_date?->format('Y-m-d'),
                    'Mailing address'      => $case->institution?->mailing_address,
                    'Physical address'     => $case->institution?->physical_address,
                ];
            })->toArray();

            // Legacy fallback. ~272 prisoners imported from Airtable have
            // a stored years_in_prison integer but zero PrisonerCase rows
            // (or cases without imprisoned_for_days). Without this, the
            // /database card shows nothing for time served. Treat the raw
            // integer as a year count and convert to days so
            // calculatedPunishment renders. getRawOriginal bypasses the
            // accessor, which would otherwise return an array.
            if ($daysImprisoned === 0) {
                $legacyYears = (int) ($prisoner->getRawOriginal('years_in_prison') ?? 0);
                if ($legacyYears > 0) {
                    $daysImprisoned = $legacyYears * 365;
                }
            }

            return [
                'id'                    => $prisoner->id,
                'slug'                  => $prisoner->slug,
                'name'                  => $prisoner->name,
                'Photo'                 => $prisoner->photo ? asset('storage/'.$prisoner->photo) : null,
                'Description'           => $prisoner->description,
                'Age'                   => $prisoner->age,
                'Birthdate'             => $prisoner->birthdate?->format('Y-m-d'),
                'Death date'            => $prisoner->death_date?->format('Y-m-d'),
                'Gender'                => $prisoner->gender,
                'Race'                  => $prisoner->race,
                'AKA'                   => $prisoner->aka,
                'inmateNumber'          => $prisoner->inmate_number,
                'State'                 => $prisoner->state,
                'Address'               => $prisoner->address ?: $this->institutionAddress($prisoner),
                'latitude'              => $prisoner->lat ? (float) $prisoner->lat : $this->institutionLat($prisoner),
                'longitude'             => $prisoner->lng ? (float) $prisoner->lng : $this->institutionLng($prisoner),
                'Era'                   => $prisoner->era,
                'Ideologies'            => $prisoner->ideologies ?? [],
                'Affiliation'           => !empty($prisoner->affiliation) ? $prisoner->affiliation : null,
                'In Custody'            => $prisoner->in_custody,
                'Released'              => $prisoner->released,
                'In Exile'              => $prisoner->currently_in_exile,
                'Currently in Exile'    => $prisoner->currently_in_exile,
                'Imprisoned or Exiled'  => $prisoner->imprisoned_or_exiled ? 'T' : null,
                'Awaiting Trial'        => $prisoner->awaiting_trial,
                'Website'               => $prisoner->website,
                'Twitter'               => $prisoner->twitter,
                'Facebook'              => $prisoner->facebook,
                'Years Spent In Prison' => array_map('strval', $prisoner->getIncarcerationYearsArray()),
                'SortOrder'             => $prisoner->sort_order,
                'cases'                 => $cases,
                'imprisonedFor'         => $daysImprisoned,
                'inExileFor'            => $daysInExile,
                'calculatedPunishment'  => $this->calculatePunishment($daysImprisoned, $daysInExile),
                // Convenience boolean aliases used by Vue filter system
                'inCustody'             => $prisoner->in_custody,
                'released'              => $prisoner->released,
                'inExile'               => $prisoner->currently_in_exile,
                'awaitingTrial'         => $prisoner->awaiting_trial,
                'imprisonedOrExiled'    => $prisoner->imprisoned_or_exiled,
            ];
        });

        return response()->json($data);
    }

    /**
     * Pick the most-recent case's institution coordinates as a
     * fallback when the prisoner row itself doesn't carry lat/lng.
     * Institution coordinates are the right model: a place's
     * geolocation belongs to the place, not the people held there.
     */
    private function institutionLat(Prisoner $p): ?float {
        $inst = $this->latestInstitution($p);
        return $inst?->lat ? (float) $inst->lat : null;
    }
    private function institutionLng(Prisoner $p): ?float {
        $inst = $this->latestInstitution($p);
        return $inst?->lng ? (float) $inst->lng : null;
    }
    private function institutionAddress(Prisoner $p): ?string {
        $inst = $this->latestInstitution($p);
        if (! $inst) return null;
        return $inst->physical_address
            ?: $inst->mailing_address
            ?: trim(implode(', ', array_filter([$inst->name, $inst->city, $inst->state])))
            ?: null;
    }
    private function latestInstitution(Prisoner $p): ?\App\Models\Institution {
        // cases are eager-loaded with .institution in index();
        // sortByDesc on created_at gives the most-recent case first
        $latestCase = $p->cases->sortByDesc('created_at')->first();
        return $latestCase?->institution;
    }

    private function calculatePunishment(int $daysImprisoned, int $daysInExile): string {
        $result = '';

        if ($daysImprisoned > 0) {
            $years = intdiv($daysImprisoned, 365);
            $months = intdiv($daysImprisoned % 365, 30);
            $days = $daysImprisoned % 30;
            $result .= "Imprisoned For {$years} years {$months} months {$days} days";
        }

        if ($daysInExile > 0) {
            if ($result) {
                $result .= '<br/>';
            }
            $years = intdiv($daysInExile, 365);
            $months = intdiv($daysInExile % 365, 30);
            $days = $daysInExile % 30;
            $result .= "In Exile For {$years} years {$months} months {$days} days";
        }

        return $result;
    }
}
