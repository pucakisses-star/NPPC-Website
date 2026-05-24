<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PrisonerApiController extends Controller {
    private const CACHE_KEY = 'api.prisoners.index.v1';
    private const CACHE_TTL = 600; // 10 minutes

    /**
     * Return all prisoners with cases and institutions in the same JSON shape
     * the frontend Vue components expect (matching the old Cloudflare worker output).
     *
     * The full dataset (~700 rows × cases × institutions) is built once and
     * cached for 10 minutes. Pass ?bust=1 to skip the cache on demand. Model
     * observers in App\Providers\AppServiceProvider invalidate the cache on
     * Prisoner / PrisonerCase / Institution save/delete so admin edits show
     * up immediately.
     */
    public function index(Request $request): JsonResponse {
        if ($request->boolean('bust')) {
            Cache::forget(self::CACHE_KEY);
        }

        $data = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->buildPayload();
        });

        // Public-cacheable for 10 minutes — same TTL as the server-side
        // cache. The data is non-sensitive (public political-prisoner
        // database) so browsers, intermediate proxies, and CDNs can all
        // reuse the same response. Cuts ~1.12 MB off every page load
        // after the first on a given browser. Pass ?bust=1 to force
        // both server and client to revalidate.
        return response()->json($data)->header(
            'Cache-Control',
            'public, max-age=600, stale-while-revalidate=300',
        );
    }

    private function buildPayload(): array {
        $data = [];

        // lazy() streams rows in chunks of 1000 so the full Eloquent
        // collection never lives in memory at once. Relationships are
        // eager-loaded per chunk.
        Prisoner::with(['cases.institution'])
            ->orderBy('sort_order')
            ->lazy()
            ->each(function (Prisoner $prisoner) use (&$data) {
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

                $data[] = [
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
                    'Address'               => $prisoner->address,
                    'latitude'              => $prisoner->lat ? (float) $prisoner->lat : null,
                    'longitude'             => $prisoner->lng ? (float) $prisoner->lng : null,
                    'Era'                   => $prisoner->era,
                    'Ideologies'            => $prisoner->ideologies ?? [],
                    'Affiliation'           => ! empty($prisoner->affiliation) ? $prisoner->affiliation : null,
                    'In Custody'            => $prisoner->in_custody,
                    'Released'              => $prisoner->released,
                    'In Exile'              => $prisoner->currently_in_exile,
                    'Currently in Exile'    => $prisoner->currently_in_exile,
                    'Imprisoned or Exiled'  => $prisoner->imprisoned_or_exiled ? 'T' : null,
                    'Awaiting Trial'        => $prisoner->awaiting_trial,
                    'Website'               => $prisoner->website,
                    'Twitter'               => $prisoner->twitter,
                    'Facebook'              => $prisoner->facebook,
                    'Instagram'             => $prisoner->instagram,
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

        return $data;
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

    /**
     * Cache key exposed so model observers can invalidate it.
     */
    public static function cacheKey(): string {
        return self::CACHE_KEY;
    }
}
