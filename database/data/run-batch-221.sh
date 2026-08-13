#!/usr/bin/env bash
#
# BATCH 221 -- the itsabouttimebpp.com political-prisoner PDFs.
#
#   SEVENTEEN FILES, 87 PAGES, about 9.3 MB, from a Black Panther Party
#   alumni site. Two consolidated rosters, five case files, an appeal, a
#   statement, a news clipping, two Angolite scans, three event flyers, a
#   conference programme and a defense committee file.
#
#   FETCHED AT DEPLOY, NOT COMMITTED. Every other PDF corpus in this
#   archive works this way -- Boston ABC, Freedom Archives, Nuclear
#   Resister -- so this follows the same shape: a manifest in
#   database/data and an artisan command that downloads into
#   public/pdfs/itsabouttimebpp/ and registers each file as an
#   ArchiveRecord. Worth knowing that the source is served over plain HTTP
#   from a host with no redundancy and files dating back to 1995; if
#   preservation rather than access is the goal, these seventeen should be
#   committed to the repository instead, and that is a decision to make
#   deliberately rather than by default.
#
#   TITLES AND DATES COME FROM THE FILES, not from their URLs. Every PDF
#   was opened and read before the manifest was written, which is how the
#   Angolite files turned out to be two different scans rather than a
#   duplicate, and how the conference programme turned out to carry no
#   year at all.
#
#   SEVEN OF THE SEVENTEEN ARE IMAGE-ONLY SCANS with no text layer: Hugo
#   Pinell, Geronimo, the Angola 3 poster, both Angolite files, the Sekou
#   Odinga Defense Committee file, and in practice the Marilyn Buck scan
#   too, whose text layer exists but is unusable. They are tagged Needs
#   OCR in subjects so archive:audit-pdf-ocr finds them instead of someone
#   discovering it by opening the file.
#
#   ONE DATE IS DELIBERATELY UNSET. The Attica to Abu Ghraib programme is
#   dated 22 and 23 April with no year. Cynthia McKinney is described as
#   recently returned to office, which points at 2005, but that is an
#   inference and it is written in the description rather than into the
#   year column.
#
#   Idempotent: files already present are skipped unless --force, and
#   records are matched on slug and updated rather than duplicated.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-221.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run_tinker() {
    local label="$1" sentinel="$2" code="$3" out
    echo; echo "--- ${label}"
    out=$(php artisan tinker --execute="$code" 2>&1) || true
    printf '%s\n' "$out"
    if ! grep -q "$sentinel" <<<"$out"; then
        echo "  !! FAILED: ${label} — sentinel ${sentinel} missing (exception above?)"
        FAILED+=("${label}")
    fi
}

echo "==================================================================="
echo "  Batch 221 — itsabouttimebpp.com political-prisoner PDFs"
echo "==================================================================="

echo
echo "--- fetch-and-register"
if php artisan archive:fetch-itsabouttimebpp-pdfs; then
    echo "  command reported success"
else
    echo "  !! the fetch command reported failures — see above"
    FAILED+=("fetch-and-register")
fi

VERIFY_CODE='
use App\Models\ArchiveRecord;
use Illuminate\Support\Facades\File;

$payloads = json_decode(File::get(base_path("database/data/itsabouttimebpp-pdfs.json")), true);

if (! $payloads) { echo "Could not read the manifest.\n"; return; }

$missing = 0; $noFile = 0; $bytes = 0;

echo "\n  registered records\n";

foreach ($payloads as $p) {
    $r = ArchiveRecord::where("slug", $p["slug"])->first();

    if (! $r) { echo "    !! not registered: ", $p["slug"], "\n"; $missing++; continue; }

    // file is stored as a public web path, so check it on disk directly
    // rather than through Storage, which points at a different root.
    $abs = public_path(ltrim((string) $r->file, "/"));
    $size = is_file($abs) ? filesize($abs) : 0;

    if ($size < 1000) { $noFile++; }

    $bytes += $size;

    echo "    ", ($size >= 1000 ? "ok " : "!! "),
        str_pad(mb_substr($r->title, 0, 52), 54),
        str_pad(number_format($size / 1024, 0)." KB", 10),
        $p["pages"], "pp  ",
        ($p["has_text_layer"] ? "text" : "SCAN"), "\n";
}

$needsOcr = ArchiveRecord::where("collection", "like", "%About Time%")
    ->get()
    ->filter(fn ($r) => in_array("Needs OCR", (array) $r->subjects, true));

echo "\n  ", count($payloads), " in the manifest, ", count($payloads) - $missing, " registered\n";
echo "  ", number_format($bytes / 1048576, 1), " MB on disk\n";
echo "  ", $needsOcr->count(), " tagged Needs OCR:\n";

foreach ($needsOcr as $r) { echo "      ", $r->title, "\n"; }

echo "\n  the collection reads:\n";

foreach (ArchiveRecord::where("collection", "like", "%About Time%")->orderBy("title")->get() as $r) {
    echo "    ", str_pad((string) ($r->year ?: "----"), 6),
        str_pad((string) $r->source_format, 26),
        $r->file, "\n";
}

if ($missing === 0 && $noFile === 0) { echo "\nB221-OK\n"; }
'

run_tinker "verify" "B221-OK" "$VERIFY_CODE"

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 221 applied."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do echo "    - ${f}"; done
fi
echo "==================================================================="
