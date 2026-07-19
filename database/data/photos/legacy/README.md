# Legacy staged prisoner photos

Files moved here from `public/images/prisoners/` (July 2026) when the
photo staging folders were consolidated into `database/data/photos/`.

These were staged by earlier photo batches — the antifawatch imports,
individual `Set…Photo` commands, and various prisoner-add commands —
which copy them onto the public storage disk (`storage/app/public/
prisoners/`) and point the prisoner record at the copy. Per-file
sourcing is documented in the command that introduced each file (see
`git log -- database/data/photos/legacy/<file>`); most are treated as
non-free and used at low resolution under the same fair-use /
documentation rationale as `../nonfree/`.

New photos should not be added here: use `../` (free, credited in
CREDITS-wikipedia.md) or `../nonfree/` (credited in CREDITS-nonfree.md).
