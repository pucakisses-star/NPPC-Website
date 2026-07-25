#!/usr/bin/env bash
#
# Correct the Ailene Holmes record (Van Etten, NY 1930 flag case) from
# researched sources, and fix the shared institution that was wrongly located.
#
#   * Institution: "Monroe County Penitentiary" is in ROCHESTER, New York, not
#     Elmira. Elmira (Chemung County) is where the camp, arrest and trial
#     happened; the original entry conflated the two. Fixing the shared
#     Institution corrects the map location for both Holmes and Mabel Husa;
#     stale Elmira coordinates are cleared so it can re-geocode.
#   * Ailene Holmes: add birth (1906, Worcester MA), an accurate biography, the
#     confirmed final confinement (30 Oct 1930 - 22 Jan 1931, 84 days) and
#     arrest date (11 Aug 1930). Affiliation folded to Communist Party USA
#     (the YCL youth wing), ideology Communism.
#   * Mabel Husa: correct the "at Elmira" wording in her description to
#     "in Rochester" (dates for her are not documented, so left as-is).
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-ailene-holmes.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Prisoner;
use App\Models\Institution;

// 1) Fix the shared institution: Monroe County Penitentiary is in Rochester.
$inst = Institution::where("name", "Monroe County Penitentiary")->first();
if ($inst) {
    $inst->city = "Rochester";
    $inst->state = "New York";
    $inst->lat = null;   // clear stale Elmira coordinates for re-geocoding
    $inst->lng = null;
    $inst->save();
    echo "Institution updated: Monroe County Penitentiary -> Rochester, NY\n";
} else {
    echo "Institution Monroe County Penitentiary not found (skipped).\n";
}

// 2) Ailene Holmes — full correction.
$holmes = Prisoner::withoutGlobalScopes()->where("slug", "ailene-holmes")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "Ailene Holmes")->first();

if (! $holmes) {
    echo "Ailene Holmes not found — nothing to update.\n";
} else {
    $holmes->description =
        "Ailene Holmes (born 1906 in Worcester, Massachusetts) was a Finnish-American Communist organizer. "
        ."The daughter of Finnish immigrants, she attended Boston University for about two years before leaving "
        ."to work in Communist organizing. By 1930 she was helping run a Communist-affiliated summer camp for "
        ."children near Van Etten, in Chemung County, New York, together with fellow organizer Mabel Husa. "
        ."In early August 1930 American Legion members and other anti-Communists entered the camp and tried to "
        ."impose an American flag ceremony; Communist accounts said the camp leaders and children were assaulted. "
        ."Local authorities instead prosecuted Holmes and Husa, charging that they had insulted the American flag. "
        ."Holmes denied desecrating the flag but acknowledged saying that it did not represent exploited workers. "
        ."She was convicted on August 16, 1930 and sentenced to three months and a fifty-dollar fine. After her "
        ."appeal failed she served the sentence at the Monroe County Penitentiary in Rochester, New York, from "
        ."October 30, 1930 until her release on January 22, 1931. At trial she testified that she had earlier been "
        ."arrested for picketing outside a Boston courthouse during the campaign to save Nicola Sacco and "
        ."Bartolomeo Vanzetti.";
    $holmes->state = "New York";
    $holmes->gender = "Female";
    $holmes->ideologies = ["Communism"];
    $holmes->affiliation = ["Communist Party USA"];
    $holmes->era = "1930s";
    $holmes->in_custody = false;
    $holmes->released = true;
    $holmes->setPartialDate("birthdate", 1906, null, null);
    $holmes->save();

    $case = $holmes->cases()->first();
    if ($case) {
        $case->charges = "Convicted under the New York state penal code for insulting the American flag at a Young Communist League summer camp for children at Van Etten.";
        $case->convicted = "Convicted, 16 August 1930";
        $case->sentence = "Three months and a fifty-dollar fine; served at the Monroe County Penitentiary in Rochester, New York. Final continuous confinement 30 October 1930 to 22 January 1931 (84 days).";
        $case->setPartialDate("arrest_date", 1930, 8, 11);
        $case->setPartialDate("incarceration_date", 1930, 10, 30);
        $case->setPartialDate("release_date", 1931, 1, 22);
        $case->save();  // imprisoned_for_days auto-recomputes on save
        echo "Ailene Holmes updated (birth 1906; confinement 1930-10-30 to 1931-01-22; days={$case->imprisoned_for_days}).\n";
    } else {
        echo "Ailene Holmes has no case row — biography/birth updated only.\n";
    }
}

// 3) Mabel Husa — correct the location wording only.
$husa = Prisoner::withoutGlobalScopes()->where("slug", "mabel-husa")->first()
    ?? Prisoner::withoutGlobalScopes()->where("name", "Mabel Husa")->first();
if ($husa && $husa->description) {
    $new = str_replace("Monroe County Penitentiary at Elmira", "Monroe County Penitentiary in Rochester", $husa->description);
    if ($new !== $husa->description) {
        $husa->description = $new;
        $husa->save();
        echo "Mabel Husa description corrected (Elmira -> Rochester).\n";
    } else {
        echo "Mabel Husa description already correct.\n";
    }
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'
