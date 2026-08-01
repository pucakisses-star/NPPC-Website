#!/usr/bin/env bash
#
# BATCH 59 -- five sit-in and Freedom Ride records verified: Due,
# Chatham, Jenkins, Sullivan, Gaither.
#
#   fix-civil-rights-five
#
#     PATRICIA GLORIA STEPHENS DUE (renamed from Patricia Stephens,
#     canonical form): vitals 1939-12-09 to 2012-02-07, the full 1960
#     jail-in custody record (arrested Feb 20, convicted Mar 17,
#     entered Leon County Jail ~Mar 18, released May 5 after 49 of 60
#     days), the curator-s biography with the jail letter retitled
#     "Letter from Leon County Jail", and the State Archives DUE053
#     portrait replacing the later-life press photo.
#
#     PRICE CHATHAM: born 1931-06-05 in New Gulf, TEXAS (East
#     Rockaway was residence, not birthplace); arrested 1961-06-02 at
#     the Jackson Trailways terminal; ~24-day Parchman hunger strike;
#     release ~1961-07-11 entered as PROBABLE pending the discharge
#     register. Sovereignty Commission mugshot (placard 6-2-61).
#
#     ROBERT LEE JENKINS (renamed from Robert Jenkins): 27-year-old
#     St. Louis University student, born ~1934 circa; arrested
#     1961-06-07 at the Jackson airport; the CORE-chairmanship claim
#     downgraded to reported, the technicality acquittal to
#     unresolved, and NO release entered — a named open question.
#     Sovereignty Commission mugshot (placard 6-7-61).
#
#     TERRY SULLIVAN: age corrected 19 -> 23 (the surveillance record
#     against the press account), born ~1938 circa; arrested
#     1961-06-06; served the FULL four-month Parchman sentence;
#     release at month precision (~October 1961). The wrist-breakers
#     and cattle-prod abuse with Felix Singer stays, well supported.
#     Sovereignty Commission mugshot (placard 6-6-61).
#
#     THOMAS GAITHER: vitals 1938-11-12 to 2024-12-23; the biography
#     corrected — he was the CORE field secretary who ORGANIZED the
#     Friendship students, not one of them; ten arrested, one paid,
#     Gaither plus eight Friendship students became the Friendship
#     Nine. Custody to the day: York County Prison Farm Feb 2 to
#     Mar 2, 1961. Obituary lead photo, "JIM CROW MUST GO" tie.
#
# Run from the repo root, after git pull:
#   bash database/data/run-batch-59.sh

set -uo pipefail
cd "$(dirname "$0")/../.."

FAILED=()

run() {
    local label="$1"
    shift
    echo
    echo "--- ${label}"
    if "$@"; then
        return 0
    fi
    echo "  !! FAILED: ${label} — recorded, continuing with the rest"
    FAILED+=("${label}")
    return 0
}

echo "==================================================================="
echo "  Batch 59 — Due, Chatham, Jenkins, Sullivan, Gaither verified"
echo "==================================================================="

run "fix-civil-rights-five" bash database/data/fix-civil-rights-five.sh

echo
echo "==================================================================="
if [ ${#FAILED[@]} -eq 0 ]; then
    echo "  Batch 59 applied. No failures."
else
    echo "  Finished with ${#FAILED[@]} failed step(s):"
    for f in "${FAILED[@]}"; do
        echo "    - ${f}"
    done
fi
echo "==================================================================="
