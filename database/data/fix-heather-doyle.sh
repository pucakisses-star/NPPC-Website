#!/usr/bin/env bash
#
# Heather Glasgow Doyle -- corrected custody record, plus a portrait.
#
# WHAT WAS WRONG
#   * Federal register number 24776-050 was attached to her. Federal inmate
#     index data assigns that number to a different Heather Doyle, released
#     from FCI Danbury in February 2005 -- years before these protests. The
#     Houston detention was local/federal pretrial custody, not a Bureau of
#     Prisons sentence. The number is cleared.
#   * The record read as continuously imprisoned since September 12, 2019.
#     Houston custody ran roughly September 12-14, 2019 only.
#   * "55 days" was the sum of the two Maryland sentences imposed (40 + 15),
#     not time actually served.
#
# CORRECTED CUSTODY TIMELINE (four episodes, ~46 documented days)
#
#   Feb 3, 2015          Cove Point crane action. Doyle and Carling Sothoron
#                        climbed a crane at the Dominion Cove Point LNG
#                        construction site with an anti-export banner. Held at
#                        the Calvert County Detention Center pending a
#                        commissioner hearing; the initial release date has
#                        not surfaced, so none is recorded.
#
#   Apr 20 - ~May 23     Trespass sentence. Pleaded guilty April 20, 2015 and
#   2015                 chose immediate confinement over probation. Sentenced
#                        to 40 days; later reporting says she served 33, which
#                        puts release around May 23 -- no contemporary notice
#                        confirms the exact calendar date.
#
#   May 27 - Jun 7       False-statement case. After complaining that an
#   2016                 officer choked her during the crane arrest, she was
#                        charged with making a false statement to law
#                        enforcement. Trial began May 24, conviction May 27,
#                        taken into custody from the courtroom that day. Three
#                        months, all but 15 days suspended, 240 hours of
#                        community service, two years supervised probation.
#                        SEED announced she was free on June 7 -- about 11 days
#                        actually served. Affirmed by the Maryland Court of
#                        Special Appeals on July 13, 2017.
#
#   Sep 12-14, 2019      Fred Hartman Bridge. Held from the arrest until the
#                        September 14 federal appearance, then released on a
#                        personal-recognizance bond. The felony
#                        critical-infrastructure cases were no-billed by a
#                        Texas grand jury; remaining state misdemeanors were
#                        resolved by a 2021 agreement ($250 in court costs each,
#                        Greenpeace reimbursing response expenses) and the
#                        federal charges by May 2022 deferred-prosecution
#                        agreements. No prison sentence.
#
# NOT CHANGED
#   The biography is left exactly as it is, by instruction.
#
#   The birth year stays at the existing 1984 year-precision value. Reported
#   ages -- 31 on February 3, 2015 and 35 on September 12, 2019 -- narrow her
#   birth to roughly September 13, 1983 through February 3, 1984, which spans
#   two calendar years, so 1984 is one of two possibilities rather than a
#   documented fact. Left alone rather than guessed at again.
#
# PHOTO
#   Cropped from the Popular Resistance photograph of the two Cove Point crane
#   climbers; Doyle is the figure on the left, confirmed against her Harris
#   County booking photo. Replaces the mugshot.
#   Pass KEEP_MUGSHOT=1 to leave the existing photo in place.
#
# Idempotent. Run from the repo root:
#   bash database/data/fix-heather-doyle.sh
#   KEEP_MUGSHOT=1 bash database/data/fix-heather-doyle.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

KEEP_MUGSHOT="${KEEP_MUGSHOT:-0}" php artisan tinker --execute='
use App\Models\Institution;
use App\Models\Prisoner;
use Illuminate\Support\Facades\File;

$keepMugshot = getenv("KEEP_MUGSHOT") === "1";

$matches = Prisoner::withoutGlobalScopes()
    ->where(fn ($q) => $q->where("slug", "heather-doyle")
        ->orWhereRaw("LOWER(name) IN (?, ?)", ["heather doyle", "heather glasgow doyle"]))
    ->get();

if ($matches->isEmpty()) { echo "NOT FOUND: Heather Doyle\n"; exit(1); }
if ($matches->count() > 1) {
    echo "ABORT: {$matches->count()} records match. Refusing to guess:\n";
    foreach ($matches as $m) { echo "  {$m->slug}  {$m->name}\n"; }
    exit(1);
}

$p = $matches->first();
$oldSlug = $p->slug;

// ---- identity -------------------------------------------------------------
// The display name stays "Heather Doyle"; Glasgow is stored separately so the
// profile can show the full name above the date of birth.
$p->first_name = "Heather";
$p->middle_name = "Glasgow";
$p->last_name = "Doyle";

// ---- the wrong federal register number ------------------------------------
if ($p->inmate_number) {
    echo "clearing inmate_number {$p->inmate_number} (belongs to a different Heather Doyle)\n";
    $p->inmate_number = null;
}

// ---- status ---------------------------------------------------------------
$p->in_custody = false;
$p->awaiting_trial = false;
$p->released = true;
$p->save();

if ($p->slug !== $oldSlug) { $p->slug = $oldSlug; $p->save(); }   // keep the public URL

$calvert = Institution::firstOrCreate(
    ["name" => "Calvert County Detention Center"],
    ["city" => "Prince Frederick", "state" => "Maryland"],
);

// [marker matched against existing charges, charges, institution id,
//  arrest y/m/d, incarceration y/m/d, release y/m/d, sentenced y/m/d,
//  plead, convicted, sentence]
$episodes = [
    [
        "Cove Point LNG construction site",
        "Trespassing — the February 3, 2015 occupation of a crane at the Dominion Cove Point LNG construction site in Lusby, Maryland, a banner action against liquefied natural gas exports and fracking, carried out with Carling Sothoron.",
        [2015, 2, 3], null, null, null,
        "Guilty (entered April 20, 2015)",
        "Yes — pleaded guilty to trespass",
        "Arrested on the site and held at the Calvert County Detention Center pending a hearing before a commissioner. The date of that initial release has not been documented, so no release date is recorded here and this episode adds no days to the total.",
    ],
    [
        "sentencing on the Cove Point crane action",
        "Trespassing — sentencing on the Cove Point crane action, at which she chose immediate confinement over a suspended sentence with probation.",
        null, [2015, 4, 20], [2015, 5, 23], [2015, 4, 20],
        "Guilty",
        "Yes — guilty plea, April 20, 2015",
        "Sentenced to 40 days and taken into custody the same day. Later reporting says she physically served 33 days, which places release around May 23, 2015; no contemporary notice confirms the exact calendar date, so the release date here is approximate.",
    ],
    [
        "false statement to law enforcement",
        "Making a false statement to law enforcement — charged after she complained that an officer had choked her during the Cove Point crane arrest.",
        null, [2016, 5, 27], [2016, 6, 7], [2016, 5, 27],
        "Not guilty",
        "Yes — jury verdict, May 27, 2016; affirmed by the Maryland Court of Special Appeals on July 13, 2017",
        "Three months imprisonment, all but 15 days suspended, plus 240 hours of community service, two years of supervised probation and court costs. Taken into custody directly from the courtroom on May 27; SEED announced she was free on June 7, 2016, so about 11 days were actually served.",
    ],
];

foreach ($episodes as [$marker, $charges, $arrest, $inc, $rel, $sent, $plead, $convicted, $sentence]) {
    $c = $p->cases()->where("charges", "like", "%".$marker."%")->first();
    if (! $c) { $c = $p->cases()->create(["charges" => $charges]); }
    $c->charges = $charges;
    $c->institution_id = $calvert->id;
    $c->plead = $plead;
    $c->convicted = $convicted;
    $c->sentence = $sentence;
    if ($arrest) { $c->setPartialDate("arrest_date", ...$arrest); }
    if ($inc) { $c->setPartialDate("incarceration_date", ...$inc); }
    if ($rel) { $c->setPartialDate("release_date", ...$rel); }
    if ($sent) { $c->setPartialDate("sentenced_date", ...$sent); }
    $c->save();
}

// ---- the 2019 Houston case: correct the outcome text ----------------------
$houston = $p->cases()->where("charges", "like", "%navigable waters%")->first();
if ($houston) {
    $houston->charges = "Aiding and abetting obstruction of navigable waters (federal), alongside state allegations of criminal trespass, obstructing a highway and disrupting critical infrastructure — the September 12, 2019 Greenpeace blockade of the Houston Ship Channel from the Fred Hartman Bridge.";
    $houston->plead = "Not guilty";
    $houston->convicted = "No — a Texas grand jury declined to approve the felony critical-infrastructure cases; the state misdemeanors and the federal charges were both dismissed under agreements";
    $houston->sentence = "No prison sentence. Held from the September 12, 2019 arrest until September 14, when the defendants appeared in federal court, pleaded not guilty and were released on personal-recognizance bonds. The remaining state misdemeanors were resolved by a 2021 agreement under which each defendant paid \$250 in court costs and Greenpeace reimbursed emergency-response expenses; in May 2022 all 22 defendants entered federal deferred-prosecution agreements leading to dismissal.";
    $houston->setPartialDate("arrest_date", 2019, 9, 12);
    $houston->setPartialDate("incarceration_date", 2019, 9, 12);
    $houston->setPartialDate("release_date", 2019, 9, 14);
    $houston->save();
} else {
    echo "WARNING: no Fred Hartman Bridge case found to correct.\n";
}

// ---- portrait -------------------------------------------------------------
$src = base_path("database/data/photos/heather-doyle.jpg");
if (is_file($src) && ! $keepMugshot) {
    $dstRel = "prisoners/{$p->slug}.jpg";
    File::ensureDirectoryExists(dirname(storage_path("app/public/{$dstRel}")));
    File::copy($src, storage_path("app/public/{$dstRel}"));
    touch(storage_path("app/public/{$dstRel}"));   // bust the ?v=mtime photo cache
    $p->photo = $dstRel;
    $p->save();
    echo "photo set -> {$dstRel} (Cove Point crane climbers, left figure)\n";
} elseif ($keepMugshot) {
    echo "photo left alone (KEEP_MUGSHOT=1)\n";
}

// ---- receipt --------------------------------------------------------------
$p->refresh()->load("cases.institution");
echo "\n{$p->name}  [{$p->slug}]\n";
echo "  full name:      ".trim($p->first_name." ".$p->middle_name." ".$p->last_name)."\n";
echo "  inmate number:  ".($p->inmate_number ?: "(cleared)")."\n";
echo "  status:         in_custody=".($p->in_custody ? "yes" : "no")
    ."  awaiting_trial=".($p->awaiting_trial ? "yes" : "no")
    ."  released=".($p->released ? "yes" : "no")."\n";
echo "  birth year:     ".($p->birthdate ? $p->birthdate->year." (year precision, unchanged)" : "-")."\n";

$total = 0; $open = 0;
foreach ($p->cases->sortBy(fn ($c) => (string) ($c->arrest_date ?: $c->incarceration_date)) as $c) {
    $days = $c->imprisoned_for_days;
    $total += (int) $days;
    if ($c->incarceration_date && ! $c->release_date) { $open++; }
    echo "  case  ".str_pad((string) ($c->formatPartialDate("incarceration_date") ?: $c->formatPartialDate("arrest_date") ?: "?"), 14)
        ." -> ".str_pad((string) ($c->formatPartialDate("release_date") ?: "-"), 14)
        ."  ".str_pad((string) ($days ?? "-"), 5, " ", STR_PAD_LEFT)." days"
        ."  ".($c->institution->name ?? "-")."\n";
}
echo "  total counted:  {$total} days\n";
if ($open) { echo "  NOTE: {$open} case(s) have an incarceration date with no release date.\n"; }

\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "\nDone.\n";
'

echo
echo "Done."
