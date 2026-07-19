#!/usr/bin/env bash
#
# Mines the May and June 1985 issues of Libertad (MLN-PR; Freedom
# Archives DOC26 scans), which fill the last gap in the site's 1985 run —
# both issues are registered by the extended archive:add-libertad-missing
# command.
#
# Already present from these issues' coverage: the FALN POWs and their
# video-surveillance appeal (Torres, Rodríguez, Cortés), Shelley Miller
# and the nine criminal-contempt resisters, Alan Berkman and Elizabeth
# Duke (captured May 1985), Pablo Marcano García, Nydia Cuevas, Guillermo
# Morales, Filiberto Ojeda Ríos, the Ohio 7, and Julio Rosado.
#
# Adds the two missing 1985 Washington grand-jury resisters, both
# subpoenaed March 27, 1985 in the Capitol-bombing / Red Guerrilla
# Resistance investigation, both of whom served three months for
# refusing to collaborate:
#  - Bob Lederer (New Movement in Solidarity with Puerto Rican
#    Independence and Socialism)
#  - Terry Bisson (John Brown Anti-Klan Committee; the writer)
# Their captioned Libertad portraits are attached.
#
# Idempotent: prisoner:add refuses duplicates; photo attaches are
# fill-if-empty.
#
# Run from the repo root:  bash database/data/add-libertad-1985.sh

set -euo pipefail
cd "$(dirname "$0")/../.."

php artisan prisoner:add '{"name":"Bob Lederer","first_name":"Bob","last_name":"Lederer","description":"Bob Lederer is a New York writer, editor and longtime anti-imperialist and AIDS activist, a member of the New Movement in Solidarity with Puerto Rican Independence and Socialism. On March 27, 1985 four FBI agents served him at his job with a subpoena to the federal grand jury in Washington, D.C. investigating the November 1983 Capitol bombing and related actions claimed by the Red Guerrilla Resistance and Armed Resistance Unit. Refusing on principle to testify or collaborate in any way — the fourth North American supporter of Puerto Rican independence subpoenaed in that wave — he was found in civil contempt and imprisoned for three months, one of six activists jailed for defying the 1985 subpoenas. He was never charged with a crime. He later became a producer and host at WBAI/Pacifica Radio in New York, where his programs have long covered U.S. political prisoners.","state":"New York","gender":"Male","ideologies":["Anti-imperialism","Puerto Rican independence solidarity"],"era":"1980s","released":true,"cases":[{"charges":"Civil contempt — refusing to testify before the Washington, D.C. federal grand jury investigating the 1983 Capitol bombing and related Red Guerrilla Resistance / Armed Resistance Unit actions","convicted":"No — jailed for civil contempt, never charged with a crime","sentence":"Imprisoned three months (1985)","imprisoned_for_days":90}]}' || true

php artisan prisoner:add '{"name":"Terry Bisson","first_name":"Terry","last_name":"Bisson","description":"Terry Bisson (1942–2024) was a science-fiction writer — later celebrated for \"Bears Discover Fire\" and \"They'"'"'re Made Out of Meat\" — and a longtime anti-imperialist activist, a member of the John Brown Anti-Klan Committee in New York. On March 27, 1985 he was served with a subpoena to the federal grand jury in Washington, D.C. investigating the November 1983 Capitol bombing and related actions claimed by the Red Guerrilla Resistance and Armed Resistance Unit. One of six John Brown Anti-Klan Committee-linked activists who refused to cooperate in that wave, he declared he would go to prison rather than become an informer, and served three months for contempt. He was never charged with a crime, and later recounted the episode in his poem \"RSVP to the FBI.\"","state":"New York","race":"White","gender":"Male","birthdate":"1942-02-12","death_date":"2024-01-10","ideologies":["Anti-imperialism","Anti-racism"],"affiliation":["John Brown Anti-Klan Committee"],"era":"1980s","released":true,"cases":[{"charges":"Contempt — refusing to testify before the Washington, D.C. federal grand jury investigating the 1983 Capitol bombing and related Red Guerrilla Resistance / Armed Resistance Unit actions","convicted":"No — jailed for contempt, never charged with a crime","sentence":"Imprisoned three months (1985)","imprisoned_for_days":90}]}' || true

php artisan tinker --execute='
\Illuminate\Support\Facades\Storage::disk("public")->makeDirectory("prisoners");
$photos = [
    "bob-lederer" => "nonfree/bob-lederer.jpg",
    "terry-bisson" => "nonfree/terry-bisson.jpg",
];
$linked = 0;
foreach ($photos as $slug => $file) {
    $p = \App\Models\Prisoner::withUnderReview()->where("slug", $slug)->first();
    if (! $p) { echo "MISS {$slug}\n"; continue; }
    if (! empty($p->photo)) { echo "SKIP {$slug} (already has a photo)\n"; continue; }
    $src = database_path("data/photos/{$file}");
    if (! is_file($src)) { echo "NOFILE {$file}\n"; continue; }
    $relative = "prisoners/" . basename($file);
    \Illuminate\Support\Facades\Storage::disk("public")->put($relative, (string) file_get_contents($src));
    $p->photo = $relative;
    $p->save();
    $linked++;
    echo "PHOTO {$slug}\n";
}
\Illuminate\Support\Facades\Cache::forget(\App\Http\Controllers\Api\PrisonerApiController::cacheKey());
echo "Done. {$linked} photo(s) linked.\n";
'

php artisan archive:add-libertad-missing

echo
echo "Done. Libertad May/June 1985 applied."
