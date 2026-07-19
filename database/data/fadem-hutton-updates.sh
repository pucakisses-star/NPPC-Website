#!/usr/bin/env bash
#
# Two corrections/enrichments (July 2026):
#
#  1. Pam Fadem: her record said she was "convicted and sentenced in
#     November 1984." Contemporaneous reporting shows sentencing was
#     POSTPONED for her serious medical condition: she was arrested
#     February 16, 1984 (leaving the United Nations, where she had gone
#     to challenge the Brooklyn grand jury investigation, and jailed
#     after telling the court she would not appear), and on April 19,
#     1985 Judge Eugene Nickerson heard medical testimony, suspended the
#     three-year prison sentence, and imposed three years' probation.
#     She served only the brief initial detention, of unverified length.
#  2. Bobby Hutton: attaches the public-domain Black Panther Party
#     posthumous memorial portrait (Wikimedia Commons).
#
# Idempotent: guarded exact-match corrections, fill-if-empty attaches.
#
# Run from the repo root:  bash database/data/fadem-hutton-updates.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
$p = \App\Models\Prisoner::withoutGlobalScopes()->where("slug", "pam-fadem")->first();
if ($p) {
    $wrong = "She was convicted and sentenced in November 1984.";
    $right = "Ordered to appear before the grand jury in Brooklyn on February 16, 1984, she went instead to the United Nations to challenge the investigation and was arrested leaving the UN building; after telling the court she would not appear, she was jailed. Convicted of criminal contempt and facing a three-year prison term, she saw sentencing postponed because of her serious medical condition — and on April 19, 1985 Judge Eugene Nickerson, after hearing medical testimony, suspended the prison sentence and placed her on three years of probation. She served only that brief initial detention.";
    if (str_contains((string) $p->description, $wrong)) {
        $p->description = str_replace($wrong, $right, (string) $p->description);
        $p->save();
        echo "DESC pam-fadem corrected\n";
    }
    if ($p->cases()->count() === 1) {
        $case = $p->cases()->first();
        $changed = false;
        if (str_contains((string) $case->convicted, "sentenced in November 1984")) {
            $case->convicted = "Yes — convicted of criminal contempt; on April 19, 1985 Judge Eugene Nickerson suspended the three-year prison sentence on medical testimony and imposed three years of probation";
            $changed = true;
        }
        if (empty($case->arrest_date)) { $case->arrest_date = "1984-02-16"; $changed = true; }
        if (empty($case->incarceration_date)) { $case->incarceration_date = "1984-02-16"; $changed = true; }
        if (empty($case->sentence)) {
            $case->sentence = "Three years for criminal contempt, suspended April 19, 1985; three years of probation. Actual custody: the brief February 1984 detention, of unverified duration; her release date from it is not documented";
            $changed = true;
        }
        if ($changed) { $case->save(); echo "CASE pam-fadem corrected\n"; }
    }
}

\Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
$p = \App\Models\Prisoner::withUnderReview()->where("slug", "bobby-hutton")->first();
if ($p && empty($p->photo) && is_file(database_path("data/photos/bobby-hutton.jpg"))) {
    \Illuminate\Support\Facades\Storage::disk("public")->put("prisoners/bobby-hutton.jpg", (string) file_get_contents(database_path("data/photos/bobby-hutton.jpg")));
    $p->photo = "prisoners/bobby-hutton.jpg";
    $p->save();
    echo "PHOTO bobby-hutton\n";
}

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done. Fadem correction and Hutton photo applied."
