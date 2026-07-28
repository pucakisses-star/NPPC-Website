#!/usr/bin/env bash
#
# Louis William "L. W." Rogers -- corrected record.
#
# The record had no life dates, no incarceration date at all, and a release
# date of 1895-11-22 -- which is Eugene Debs’s release, not Rogers’s. Debs
# drew six months and stayed to the end; Rogers and the other directors drew
# three months and were let out on August 22, 1895. With no incarceration
# date the counter also showed nothing for three months of jail.
#
# The bio carried two errors the dossier corrects: that Cleveland "invoked
# the Insurrection Act" (he deployed federal troops and marshals to Chicago
# over Governor Altgeld’s objections -- the safer description), and that the
# directors were enjoined "from any further communication that might
# continue the strike" (the injunction reached obstruction of interstate rail
# and mail service, interference with railroad property, and ordering or
# inducing employees to continue it; ARU headquarters telegrams were the
# EVIDENCE of violation, not the thing forbidden).
#
# CUSTODY -- three separate periods, which is why there are three case rows.
# The profile sums imprisoned_for_days across cases, so one row per period is
# the only way to reach the true 91 days without inventing a continuous span
# across the bail gap between January 24 and June 15, 1895:
#
#   Jul 17-25, 1894   Cook County Jail            8 days
#                     Rogers, Debs, Howard and Keliher were brought before
#                     Judge William H. Seaman on a contempt attachment and
#                     deliberately refused \$3,000 bail.
#   Jan  9-24, 1895   McHenry County Jail        15 days
#                     Began the three-month sentence; released while the
#                     Supreme Court proceedings continued.
#   Jun 15 - Aug 22   McHenry County Jail        68 days
#                     Resumed after In re Debs was decided May 27, 1895.
#                                                --------
#                                                91 days
#
# A FOURTH ROW records the separate federal indictment for conspiracy to
# obstruct the mails, on which he was arrested July 10, 1894 and released the
# same day on \$10,000 bail after a few hours. It carries an arrest date only
# and no incarceration date, so it adds nothing to the counter -- hours in a
# marshal’s custody are not a jail term.
#
# THE AUGUST 24 TRAP: the release was reported in an August 24 newspaper
# item, which is sometimes mistaken for the date of the event itself. The
# release date here is August 22.
#
# NOT TOUCHED: the photograph already on the record. Confirmed solo portraits
# of Rogers do exist (one from about 1892, others in Theosophical Society
# archives), but the commonly reproduced group photograph of seven jailed ARU
# officers does NOT contain him -- its own caption says "Not shown: L. W.
# Rogers." If the attached file turns out to be that group shot, it needs
# replacing; this script does not assume either way.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-louis-w-rogers.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use App\Models\PrisonerCase;

$p = Prisoner::withoutGlobalScopes()->where("slug", "louis-w-rogers")->with("cases")->first();
if (! $p) {
    echo "NOT FOUND: louis-w-rogers\n";
    exit(1);
}

$p->middle_name = "William";
$p->aka = "L. W. Rogers";
$p->in_custody = false;
$p->released = true;
$p->awaiting_trial = false;
$p->setPartialDate("birthdate", 1859, 5, 28);
$p->setPartialDate("death_date", 1953, 4, 18);
$p->affiliation = ["American Railway Union", "Social Democracy of America"];
$p->description = "Louis William “L. W.” Rogers (May 28, 1859–April 18, 1953) was an American railway worker, labor journalist and member of the American Railway Union’s executive board. He edited the union newspaper, the Railway Times, during the 1894 Pullman Strike and nationwide boycott of trains carrying Pullman cars. Rogers was first arrested on July 10, 1894, under a federal indictment alleging conspiracy to obstruct the United States mails. He was detained for several hours and released on bail. On July 17, he and fellow ARU officers Eugene V. Debs, George Howard and Sylvester Keliher surrendered under a federal contempt attachment. They refused bail and spent eight days in the Cook County Jail before being released on July 25. On December 14, 1894, Judge William A. Woods found Rogers and the other ARU directors guilty of criminal contempt for violating a federal injunction against interference with interstate rail and mail service. Rogers received a three-month jail sentence. He entered the McHenry County Jail at Woodstock on January 9, 1895, but was released on January 24 while the defendants sought Supreme Court review. After the Supreme Court denied relief in In re Debs, Rogers returned to Woodstock Jail on June 15 and was released on August 22, 1895. Across the three contempt-related custody periods, he spent approximately ninety-one days in jail. Rogers remained involved in labor organizing and socialist politics after his release, editing the Social Democrat and helping organize Eugene Debs’s speaking tours. He later became a prominent writer and national president of the Theosophical Society in America. He died in Santa Barbara, California, on April 18, 1953.";
$p->save();

$woodstock = Institution::firstOrCreate(
    ["name" => "Woodstock Jail (McHenry County)"],
    ["city" => "Woodstock", "state" => "Illinois"],
);
$cook = Institution::firstOrCreate(
    ["name" => "Cook County Jail"],
    ["city" => "Chicago", "state" => "Illinois"],
);

$injunction = "The July 2, 1894 federal injunction barred the ARU directors from obstructing interstate rail service and the mails, interfering with railroad property, and ordering, encouraging or inducing railroad employees to continue such obstruction. Telegrams and other communications sent from ARU headquarters after they had notice of the injunction were the evidence that they had kept directing the boycott.";

// The four rows, keyed by a marker in the charges text so re-runs update
// rather than duplicate. Order: chronological.
$rows = [
    [
        "key"   => "[mail-conspiracy]",
        "inst"  => null,
        "arr"   => [1894, 7, 10],
        "inc"   => null,
        "rel"   => null,
        "conv"  => null,
        "judge" => null,
        "charges" => "[mail-conspiracy] Conspiracy to obstruct the United States mails — a separate federal indictment from the contempt case. Rogers was arrested on July 10, 1894, held a few hours and released the same day on \$10,000 bail.",
        "sentence" => "No custody beyond a few hours on the day of arrest, so this case adds nothing to the time-imprisoned figure.",
    ],
    [
        "key"   => "[contempt-1]",
        "inst"  => $cook->id,
        "arr"   => [1894, 7, 17],
        "inc"   => [1894, 7, 17],
        "rel"   => [1894, 7, 25],
        "conv"  => null,
        "judge" => "William H. Seaman (contempt attachment)",
        "charges" => "[contempt-1] Criminal contempt of the federal injunction, first custody period. ".$injunction,
        "sentence" => "Pre-sentence custody. Rogers, Eugene V. Debs, George Howard and Sylvester Keliher were brought before Judge William H. Seaman on a contempt attachment on July 17, 1894 and deliberately declined \$3,000 bail, entering the Cook County Jail rather than post it. They were released on July 25 — eight days. This period appears to have been credited toward the eventual three-month term.",
    ],
    [
        "key"   => "[contempt-2]",
        "inst"  => $woodstock->id,
        "arr"   => null,
        "inc"   => [1895, 1, 9],
        "rel"   => [1895, 1, 24],
        "conv"  => "Found guilty of criminal contempt on December 14, 1894 by Judge William A. Woods. Debs received six months; Rogers and the other directors received three.",
        "judge" => "William A. Woods",
        "charges" => "[contempt-2] Criminal contempt of the federal injunction, sentence begun. ".$injunction,
        "sentence" => "Three months in county jail, imposed December 14, 1894. Rogers entered the McHenry County Jail at Woodstock on January 9, 1895 and was released on January 24, while the defendants sought Supreme Court review — fifteen days.",
    ],
    [
        "key"   => "[contempt-3]",
        "inst"  => $woodstock->id,
        "arr"   => null,
        "inc"   => [1895, 6, 15],
        "rel"   => [1895, 8, 22],
        "conv"  => null,
        "judge" => "William A. Woods",
        "charges" => "[contempt-3] Criminal contempt of the federal injunction, sentence completed. ".$injunction,
        "sentence" => "Sentence resumed after the Supreme Court denied habeas relief in In re Debs on May 27, 1895, upholding the federal court’s authority to issue and enforce the injunction. Rogers returned to Woodstock on June 15, 1895 and was released on August 22 with the other three-month prisoners, leaving Debs to finish his six months alone — sixty-eight days. An August 24 newspaper item reported the release, which is sometimes mistaken for the date of the release itself.",
    ],
];

foreach ($rows as $row) {
    $case = $p->cases->first(fn ($c) => str_contains((string) $c->charges, $row["key"]));

    // First run: reuse the single existing case for the first row that has
    // no marker yet, so the original record is corrected rather than orphaned.
    if (! $case) {
        $case = $p->cases->first(fn ($c) => ! str_contains((string) $c->charges, "[contempt-") && ! str_contains((string) $c->charges, "[mail-"));
    }
    if (! $case) {
        $case = new PrisonerCase(["prisoner_id" => $p->id]);
    }

    $case->prisoner_id = $p->id;
    $case->institution_id = $row["inst"];
    $case->charges = $row["charges"];
    $case->sentence = $row["sentence"];
    $case->convicted = $row["conv"];
    $case->judge = $row["judge"];
    $case->plead = null;
    $case->sentenced_date = null;
    $case->arrest_date = null;
    $case->incarceration_date = null;
    $case->release_date = null;
    if ($row["arr"]) { $case->setPartialDate("arrest_date", ...$row["arr"]); }
    if ($row["inc"]) { $case->setPartialDate("incarceration_date", ...$row["inc"]); }
    if ($row["rel"]) { $case->setPartialDate("release_date", ...$row["rel"]); }
    if ($row["key"] === "[contempt-2]") { $case->setPartialDate("sentenced_date", 1894, 12, 14); }
    $case->save();

    $p->load("cases");
}

$p->refresh()->load("cases");
echo "Louis William Rogers  [{$p->slug}]  aka ".($p->aka ?: "-")."\n";
echo "  born ".($p->formatPartialDate("birthdate") ?: "-")
    ."   died ".($p->formatPartialDate("death_date") ?: "-")
    ."   age ".($p->age ?? "-")."  (expect 93)\n";
$total = 0;
foreach ($p->cases->sortBy(fn ($c) => (string) ($c->arrest_date ?? $c->incarceration_date)) as $c) {
    $total += (int) $c->imprisoned_for_days;
    echo "  inc=".($c->incarceration_date ? $c->incarceration_date->toDateString() : "-       ")
        ."  rel=".($c->release_date ? $c->release_date->toDateString() : "-       ")
        ."  days=".str_pad((string) ($c->imprisoned_for_days ?? "null"), 5)
        .substr((string) $c->charges, 0, 14)."\n";
}
echo "  TOTAL days = {$total}  (expect 91: 8 + 15 + 68)\n";

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done.\n";
'

echo
echo "Done."
