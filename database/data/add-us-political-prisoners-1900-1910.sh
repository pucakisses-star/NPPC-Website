#!/usr/bin/env bash
#
# Add U.S. political prisoners and political detainees of 1900-1910.
#
# Covers people confined during 1900-1910 by federal, state, local, military,
# territorial or U.S. colonial authorities for political speech, labor or
# anarchist/socialist organizing, racial protest, Native sovereignty, or
# anti-colonial activity. The full roster (135 people, 138 cases) lives in
# database/data/us-political-prisoners-1900-1910.json and includes:
#   - the 43 Filipino leaders deported to Guam in 1901, and the Sakay/Bilibid
#     prisoners of 1906-07;
#   - Partido Liberal Mexicano members jailed in U.S. border states and the
#     six PLM refugees rendered to Hermosillo in 1906;
#   - Western Federation of Miners / IWW labor-war and free-speech prisoners
#     (Haywood, Moyer, Pettibone, Preston, the Spokane & Missoula fights);
#   - anarchist, free-speech and Comstock-law prisoners (Berkman, Goldman,
#     Most, Turner, MacQueen, Galleani, Harman, Craddock, Buwalda);
#   - Black civil-rights prisoners (Trotter, Martin) and the Muscogee
#     sovereignty leader Chitto Harjo.
#
# Approximate dates are stored at their true precision (year/month/day) via
# the case's setPartialDate() so the site never shows a day the record does
# not actually have.
#
# Idempotent create-or-augment: a person already in the database (e.g. Emma
# Goldman) is matched by name and NOT duplicated — only cases they do not yet
# have are added. Cases are de-duplicated by their arrest/incarceration date,
# or by charge text when a case carries no date. Run from the repo root:
#   bash database/data/add-us-political-prisoners-1900-1910.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$path = base_path("database/data/us-political-prisoners-1900-1910.json");
$records = json_decode(file_get_contents($path), true);
if (! is_array($records)) { echo "Could not read roster JSON\n"; return; }

$applyDate = function ($case, $field, $data) {
    if (empty($data[$field])) { return; }
    $parts = explode("-", $data[$field]);
    $y = (int) ($parts[0] ?? 0);
    $m = (int) ($parts[1] ?? 1);
    $d = (int) ($parts[2] ?? 1);
    $prec = $data[$field . "_precision"] ?? "day";
    if ($prec === "year")  { $case->setPartialDate($field, $y); }
    elseif ($prec === "month") { $case->setPartialDate($field, $y, $m); }
    else { $case->setPartialDate($field, $y, $m, $d); }
};

$created = 0; $augmented = 0; $casesAdded = 0;

foreach ($records as $rec) {
    $cases = $rec["cases"] ?? [];
    unset($rec["cases"]);

    $p = \App\Models\Prisoner::withoutGlobalScopes()->where("name", $rec["name"])->first();
    if (! $p) {
        $fields = [
            "name" => $rec["name"],
            "first_name" => $rec["first_name"] ?? null,
            "last_name" => $rec["last_name"] ?? null,
            "middle_name" => $rec["middle_name"] ?? null,
            "aka" => $rec["aka"] ?? null,
            "description" => $rec["description"] ?? null,
            "affiliation" => $rec["affiliation"] ?? [],
            "ideologies" => $rec["ideologies"] ?? [],
            "state" => $rec["state"] ?? null,
            "era" => "1900s",
            "released" => $rec["released"] ?? true,
            "in_custody" => $rec["in_custody"] ?? false,
        ];
        $p = \App\Models\Prisoner::create(array_filter($fields, fn ($v) => $v !== null));
        echo "  created  {$p->name} (slug {$p->slug})\n";
        $created++;
    } else {
        echo "  augment  {$p->name}\n";
        $augmented++;
    }

    foreach ($cases as $cd) {
        $hasDate = ! empty($cd["incarceration_date"]) || ! empty($cd["arrest_date"]);
        if ($hasDate) {
            $key = $cd["incarceration_date"] ?? $cd["arrest_date"];
            $dup = $p->cases()->where(function ($q) use ($key) {
                $q->whereDate("incarceration_date", $key)->orWhereDate("arrest_date", $key);
            })->exists();
        } else {
            $dup = $p->cases()->where("charges", $cd["charges"] ?? "")->exists();
        }
        if ($dup) { continue; }

        $institutionId = null;
        if (! empty($cd["institution_name"])) {
            $inst = \App\Models\Institution::firstOrCreate(
                ["name" => $cd["institution_name"]],
                array_filter([
                    "city"  => $cd["institution_city"] ?? null,
                    "state" => $cd["institution_state"] ?? null,
                ])
            );
            $institutionId = $inst->id;
        }

        $c = new \App\Models\PrisonerCase();
        $c->prisoner_id = $p->id;
        $c->institution_id = $institutionId;
        $c->charges = $cd["charges"] ?? null;
        if (! empty($cd["convicted"])) { $c->convicted = $cd["convicted"]; }
        if (! empty($cd["sentence"]))  { $c->sentence = $cd["sentence"]; }
        if (isset($cd["imprisoned_for_days"])) { $c->imprisoned_for_days = (int) $cd["imprisoned_for_days"]; }
        $applyDate($c, "arrest_date", $cd);
        $applyDate($c, "incarceration_date", $cd);
        $applyDate($c, "release_date", $cd);
        $applyDate($c, "death_in_custody_date", $cd);
        $c->save();
        $casesAdded++;
    }
}

echo "Created {$created} prisoner(s), augmented {$augmented}; cases added {$casesAdded}.\n";
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. 1900-1910 U.S. political prisoners roster added."
