<?php

namespace App\Console\Commands;

use App\Models\ArchiveRecord;
use Illuminate\Console\Command;

/**
 * Self-host the remaining external-link archive records.
 *
 * Revised version (#482 follow-up): rejects HTML error pages
 * masquerading as PDFs and other content-mismatch failures from the
 * earlier mass-download. 128 records get repointed to real local
 * files; 41 records (mostly dead vault.fbi.gov subpaths, dead
 * search.freedomarchives.org pages, dead LoC tile-server URLs, and
 * dead Freedom Archives /Documents/Finder paths) get their file
 * field set to null.
 *
 * Idempotent.
 */
final class SelfHostExternalImports extends Command {
    protected $signature = 'archive:self-host-external-imports';
    protected $description = 'Repoint external-URL archive records to local mirrors (rev #482-fix): 128 self-hosted, 41 nulled';

    public function handle(): int {
        $mapping = [
            'church-committee-book-ii' => '/pdfs/external-imports/church-committee-book-ii.pdf',
            'church-committee-book-iii' => '/pdfs/external-imports/church-committee-book-iii.pdf',
            'church-committee-hearings-vol-2-huston-plan' => '/pdfs/external-imports/church-committee-hearings-vol-2-huston-plan.pdf',
            'church-committee-hearings-vol-6-fbi' => '/pdfs/external-imports/church-committee-hearings-vol-6-fbi.pdf',
            'church-committee-hearings-vol-7-covert-action' => '/pdfs/external-imports/church-committee-hearings-vol-7-covert-action.pdf',
            'fa-c101-theyre-talking-at-sing-sing' => '/pdfs/external-imports/fa-c101-theyre-talking-at-sing-sing.pdf',
            'fa-c101-why-assata-shakur-must-be-supported' => '/pdfs/external-imports/fa-c101-why-assata-shakur-must-be-supported.pdf',
            'fa-c104-an-unpublished-letter' => '/pdfs/external-imports/fa-c104-an-unpublished-letter.pdf',
            'fa-c104-interview-with-kiilu-nyasha' => '/pdfs/external-imports/fa-c104-interview-with-kiilu-nyasha.pdf',
            'fa-c105-lest-we-forget' => '/pdfs/external-imports/fa-c105-lest-we-forget.pdf',
            'fa-c105-notes-on-the-black-panther-party-its-basic-working-papers-and-policy-statem' => '/pdfs/external-imports/fa-c105-notes-on-the-black-panther-party-its-basic-working-papers-and-policy-statem.pdf',
            'fa-c107-still-angry-after-all-these-years' => '/pdfs/external-imports/fa-c107-still-angry-after-all-these-years.pdf',
            'fa-c108-conspiracy-against-angela-exposed' => '/pdfs/external-imports/fa-c108-conspiracy-against-angela-exposed.pdf',
            'fa-c108-lectures-on-liberation' => '/pdfs/external-imports/fa-c108-lectures-on-liberation.pdf',
            'fa-c108-on-trial-angela-davis-or-america' => '/pdfs/external-imports/fa-c108-on-trial-angela-davis-or-america.pdf',
            'fa-c111-remember-fred-hampton-mark-clark' => '/pdfs/external-imports/fa-c111-remember-fred-hampton-mark-clark.pdf',
            'fa-c119-new-afrikan-freedom-fighter-v-2-1-march-1983' => '/pdfs/external-imports/fa-c119-new-afrikan-freedom-fighter-v-2-1-march-1983.pdf',
            'fa-c119-new-afrikan-freedom-fighters-v-1-1-june-1982' => '/pdfs/external-imports/fa-c119-new-afrikan-freedom-fighters-v-1-1-june-1982.pdf',
            'fa-c126-geronimo-ji-jaga-from-cointelpro-101-video-clip' => '/pdfs/external-imports/fa-c126-geronimo-ji-jaga-from-cointelpro-101-video-clip.mp4',
            'fa-c126-geronimo-ji-jaga-from-cointelpro-101-video-clip-1' => '/pdfs/external-imports/fa-c126-geronimo-ji-jaga-from-cointelpro-101-video-clip-1.mp4',
            'fa-c126-geronimo-pratt-national-black-human-rights-coalition-nbhrc-video-clip' => '/pdfs/external-imports/fa-c126-geronimo-pratt-national-black-human-rights-coalition-nbhrc-video-clip.mp4',
            'fa-c128-application-for-repatriation-and-dual-citizenship-for-african-american-poli' => '/pdfs/external-imports/fa-c128-application-for-repatriation-and-dual-citizenship-for-african-american-poli.pdf',
            'fa-c128-new-african-ujamaa-the-economics-of-the-republic-of-new-africa' => '/pdfs/external-imports/fa-c128-new-african-ujamaa-the-economics-of-the-republic-of-new-africa.pdf',
            'fa-c128-the-new-afrikan-the-official-organ-of-the-republic-of-new-afrika-november-1' => '/pdfs/external-imports/fa-c128-the-new-afrikan-the-official-organ-of-the-republic-of-new-afrika-november-1.pdf',
            'fa-c128-the-new-afrikan-the-official-organ-of-the-republic-of-new-afrika-v-9-3-dec' => '/pdfs/external-imports/fa-c128-the-new-afrikan-the-official-organ-of-the-republic-of-new-afrika-v-9-3-dec.pdf',
            'fa-c132-words-from-a-sister-in-exile' => '/pdfs/external-imports/fa-c132-words-from-a-sister-in-exile.pdf',
            'fa-c139-justice-amerikkkan-style-in-the-dessie-woods-case' => '/pdfs/external-imports/fa-c139-justice-amerikkkan-style-in-the-dessie-woods-case.pdf',
            'fa-c139-night-of-solidarity' => '/pdfs/external-imports/fa-c139-night-of-solidarity.pdf',
            'fa-c139-the-story-of-dessie-woods' => '/pdfs/external-imports/fa-c139-the-story-of-dessie-woods.pdf',
            'fa-c144-attica-is-all-of-us' => '/pdfs/external-imports/fa-c144-attica-is-all-of-us.mp4',
            'fa-c144-attica-then-and-now' => '/pdfs/external-imports/fa-c144-attica-then-and-now.pdf',
            'fa-c145-arm-the-spirit-for-revolutionary-resistance' => '/pdfs/external-imports/fa-c145-arm-the-spirit-for-revolutionary-resistance.pdf',
            'fa-c145-arm-the-spirit-for-revolutionary-resistance-2' => '/pdfs/external-imports/fa-c145-arm-the-spirit-for-revolutionary-resistance-2.pdf',
            'fa-c145-arm-the-spirit-for-revolutionary-resistance-3' => '/pdfs/external-imports/fa-c145-arm-the-spirit-for-revolutionary-resistance-3.pdf',
            'fa-c251-for-the-symbionese-liberation-army' => '/pdfs/external-imports/fa-c251-for-the-symbionese-liberation-army.pdf',
            'fa-c251-huey-says-panthers-are-not-parties-to-extortion' => '/pdfs/external-imports/fa-c251-huey-says-panthers-are-not-parties-to-extortion.pdf',
            'fa-c251-revolution-or-education-through-the-media' => '/pdfs/external-imports/fa-c251-revolution-or-education-through-the-media.pdf',
            'fa-c251-weather-underground-february-20-1974' => '/pdfs/external-imports/fa-c251-weather-underground-february-20-1974.pdf',
            'fa-c267-arm-the-spirit-first-edition' => '/pdfs/external-imports/fa-c267-arm-the-spirit-first-edition.pdf',
            'fa-c267-arm-the-spirit-no-14' => '/pdfs/external-imports/fa-c267-arm-the-spirit-no-14.pdf',
            'fa-c267-arm-the-spirit-no-4' => '/pdfs/external-imports/fa-c267-arm-the-spirit-no-4.pdf',
            'fa-c267-harriet-tubman' => '/pdfs/external-imports/fa-c267-harriet-tubman.jpg',
            'fa-c6-breakthrough-june-july-1977' => '/pdfs/external-imports/fa-c6-breakthrough-june-july-1977.pdf',
            'fa-c6-breakthrough-march-1977' => '/pdfs/external-imports/fa-c6-breakthrough-march-1977.pdf',
            'fa-c6-breakthrough-spring-1978' => '/pdfs/external-imports/fa-c6-breakthrough-spring-1978.pdf',
            'fa-c6-breakthrough-winter-spring-1987' => '/pdfs/external-imports/fa-c6-breakthrough-winter-spring-1987.pdf',
            'fa-c7-death-to-the-klan-december-1979' => '/pdfs/external-imports/fa-c7-death-to-the-klan-december-1979.pdf',
            'fa-c7-death-to-the-klan-november-1979' => '/pdfs/external-imports/fa-c7-death-to-the-klan-november-1979.pdf',
            'fa-c7-death-to-the-klan-summer-1986' => '/pdfs/external-imports/fa-c7-death-to-the-klan-summer-1986.pdf',
            'fa-c7-nazi-skinheads-active-in-san-francisco' => '/pdfs/external-imports/fa-c7-nazi-skinheads-active-in-san-francisco.pdf',
            'fa-c7-no-kkk-no-fascist-usa-springsummer-1989' => '/pdfs/external-imports/fa-c7-no-kkk-no-fascist-usa-springsummer-1989.pdf',
            'fa-c8-mabel-williams-on-armed-self-defense-and-the-klan' => '/pdfs/external-imports/fa-c8-mabel-williams-on-armed-self-defense-and-the-klan.mp4',
            'fa-c8-mabel-williams-on-the-beginnings-of-radio-free-dixie' => '/pdfs/external-imports/fa-c8-mabel-williams-on-the-beginnings-of-radio-free-dixie.mp4',
            'fa-c8-mabel-williams-recounts-the-story-of-her-familys-flight-from-monroe-to-cuba' => '/pdfs/external-imports/fa-c8-mabel-williams-recounts-the-story-of-her-familys-flight-from-monroe-to-cuba.mp4',
            'fa-c8-self-respect-self-defense-self-determination-full-program' => '/pdfs/external-imports/fa-c8-self-respect-self-defense-self-determination-full-program.mp4',
            'fa-c84-richard-aoki-interviews-with-kpfa-reporter-wayie' => '/pdfs/external-imports/fa-c84-richard-aoki-interviews-with-kpfa-reporter-wayie.pdf',
            'fa-c84-the-life-and-times-of-richard-aoki-in-his-own-words' => '/pdfs/external-imports/fa-c84-the-life-and-times-of-richard-aoki-in-his-own-words.pdf',
            'fa1013-david-gilbert-lifetime-of-struggle' => '/pdfs/external-imports/fa1013-david-gilbert-lifetime-of-struggle.mp4',
            'fa1013-marilyn-buck-a-tribute' => '/pdfs/external-imports/fa1013-marilyn-buck-a-tribute.mp4',
            'fa1057-akinyele-umoja-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-akinyele-umoja-cointelpro-101-extra-footage.mp4',
            'fa1057-cointelpro-101-trailer' => '/pdfs/external-imports/fa1057-cointelpro-101-trailer.mp4',
            'fa1057-francisco-kiko-martinez-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-francisco-kiko-martinez-cointelpro-101-extra-footage.mp4',
            'fa1057-jose-lopez-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-jose-lopez-cointelpro-101-extra-footage.mp4',
            'fa1057-kathleen-cleaver-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-kathleen-cleaver-cointelpro-101-extra-footage.mp4',
            'fa1057-laura-whitehorn-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-laura-whitehorn-cointelpro-101-extra-footage.mp4',
            'fa1057-muhammad-ahmad-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-muhammad-ahmad-cointelpro-101-extra-footage.mp4',
            'fa1057-priscilla-falcon-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-priscilla-falcon-cointelpro-101-extra-footage.mp4',
            'fa1057-ricardo-romero-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-ricardo-romero-cointelpro-101-extra-footage.mp4',
            'fa1057-roxanne-dunbar-ortiz-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-roxanne-dunbar-ortiz-cointelpro-101-extra-footage.mp4',
            'fa1057-ward-churchill-cointelpro-101-extra-footage' => '/pdfs/external-imports/fa1057-ward-churchill-cointelpro-101-extra-footage.mp4',
            'fag-c1-the-continuing-crime-of-black-imprisonment' => '/pdfs/external-imports/fag-c1-the-continuing-crime-of-black-imprisonment.html',
            'fag-c159-the-case-against-the-death-penalty' => '/pdfs/external-imports/fag-c159-the-case-against-the-death-penalty.htm',
            'fag-c226-legacy-of-torture-trailer' => '/pdfs/external-imports/fag-c226-legacy-of-torture-trailer.mp4',
            'fag-c237-amiri-baraka-introduces-wild-poppies' => '/pdfs/external-imports/fag-c237-amiri-baraka-introduces-wild-poppies.mp3',
            'fag-c237-aya-de-leon-reads-grito-de-vieques' => '/pdfs/external-imports/fag-c237-aya-de-leon-reads-grito-de-vieques.mp3',
            'fag-c237-carolyn-baxter-reads-coca-cola-2' => '/pdfs/external-imports/fag-c237-carolyn-baxter-reads-coca-cola-2.mp3',
            'fag-c237-chrystos-reads-authenticity' => '/pdfs/external-imports/fag-c237-chrystos-reads-authenticity.mp3',
            'fag-c237-david-meltzer-reads-revelation' => '/pdfs/external-imports/fag-c237-david-meltzer-reads-revelation.mp3',
            'fag-c237-dennis-brutus-reads-one-hour-yard-poem' => '/pdfs/external-imports/fag-c237-dennis-brutus-reads-one-hour-yard-poem.mp3',
            'fag-c237-devorah-major-reads-political-poem' => '/pdfs/external-imports/fag-c237-devorah-major-reads-political-poem.mp3',
            'fag-c237-devorah-major-reads-prison-chant' => '/pdfs/external-imports/fag-c237-devorah-major-reads-prison-chant.mp3',
            'fag-c237-elana-levy-reads-i-saw-your-picture-today' => '/pdfs/external-imports/fag-c237-elana-levy-reads-i-saw-your-picture-today.mp3',
            'fag-c237-fanny-howe-reads-acrobatic' => '/pdfs/external-imports/fag-c237-fanny-howe-reads-acrobatic.mp3',
            'fag-c237-genny-lim-reads-rescue-the-word' => '/pdfs/external-imports/fag-c237-genny-lim-reads-rescue-the-word.mp3',
            'fag-c237-jean-stewart-reads-bird-watchers' => '/pdfs/external-imports/fag-c237-jean-stewart-reads-bird-watchers.mp3',
            'fag-c237-kiilu-nyasha-reads-in-memory-of-kuwasi-balagoon' => '/pdfs/external-imports/fag-c237-kiilu-nyasha-reads-in-memory-of-kuwasi-balagoon.mp3',
            'fag-c237-kwame-ture-reads-in-honor-to-marilyn-buck' => '/pdfs/external-imports/fag-c237-kwame-ture-reads-in-honor-to-marilyn-buck.mp3',
            'fag-c237-maria-poblet-reads-movement-poem' => '/pdfs/external-imports/fag-c237-maria-poblet-reads-movement-poem.mp3',
            'fag-c237-maria-poblet-reads-thirteen-springs' => '/pdfs/external-imports/fag-c237-maria-poblet-reads-thirteen-springs.mp3',
            'fag-c237-mariann-wizard-reads-imperatives' => '/pdfs/external-imports/fag-c237-mariann-wizard-reads-imperatives.mp3',
            'fag-c237-marilyn-buck-introduces-wild-poppies' => '/pdfs/external-imports/fag-c237-marilyn-buck-introduces-wild-poppies.mp4',
            'fag-c237-marilyn-buck-reads-concrete-cocoon' => '/pdfs/external-imports/fag-c237-marilyn-buck-reads-concrete-cocoon.mp3',
            'fag-c237-marilyn-buck-reads-dream-fragments' => '/pdfs/external-imports/fag-c237-marilyn-buck-reads-dream-fragments.mp3',
            'fag-c237-marilyn-buck-reads-reading-poetry' => '/pdfs/external-imports/fag-c237-marilyn-buck-reads-reading-poetry.mp3',
            'fag-c237-marilyn-buck-reads-wild-poppies' => '/pdfs/external-imports/fag-c237-marilyn-buck-reads-wild-poppies.mp3',
            'fag-c237-merle-woo-reads-pennsylvania-death-march' => '/pdfs/external-imports/fag-c237-merle-woo-reads-pennsylvania-death-march.mp3',
            'fag-c237-nellie-wong-reads-the-owl' => '/pdfs/external-imports/fag-c237-nellie-wong-reads-the-owl.mp3',
            'fag-c237-piri-thomas-reads-for-vieques-in-solidarity' => '/pdfs/external-imports/fag-c237-piri-thomas-reads-for-vieques-in-solidarity.mp3',
            'fag-c237-presente-performs-after-the-wave' => '/pdfs/external-imports/fag-c237-presente-performs-after-the-wave.mp3',
            'fag-c237-presente-performs-blues-for-shaka' => '/pdfs/external-imports/fag-c237-presente-performs-blues-for-shaka.mp3',
            'fag-c237-samsara-reads-jasper-tx' => '/pdfs/external-imports/fag-c237-samsara-reads-jasper-tx.mp3',
            'fag-c237-sara-menefee-reads-moon-bereft' => '/pdfs/external-imports/fag-c237-sara-menefee-reads-moon-bereft.mp3',
            'fag-c237-sonia-sanchez-reads-prayer' => '/pdfs/external-imports/fag-c237-sonia-sanchez-reads-prayer.mp3',
            'fag-c237-staajabu-reads-black-august' => '/pdfs/external-imports/fag-c237-staajabu-reads-black-august.mp3',
            'fag-c237-staajabu-reads-the-visit' => '/pdfs/external-imports/fag-c237-staajabu-reads-the-visit.mp3',
            'fag-c237-uchechi-kalu-reads-1950s-girl-thinking-about-love' => '/pdfs/external-imports/fag-c237-uchechi-kalu-reads-1950s-girl-thinking-about-love.mp3',
            'fag-c237-uchechi-kalu-reads-blindfolded-men' => '/pdfs/external-imports/fag-c237-uchechi-kalu-reads-blindfolded-men.mp3',
            'fag-c237-uchechi-kalu-reads-they-came-for-me' => '/pdfs/external-imports/fag-c237-uchechi-kalu-reads-they-came-for-me.mp3',
            'fag-c237-vini-bhansali-reads-a-15-year-old-palestinian-woman-in-prison' => '/pdfs/external-imports/fag-c237-vini-bhansali-reads-a-15-year-old-palestinian-woman-in-prison.mp3',
            'fbi-bpp-north-carolina' => '/pdfs/external-imports/fbi-bpp-north-carolina.pdf',
            'fbi-cesar-chavez-consolidated' => '/pdfs/external-imports/fbi-cesar-chavez-consolidated.pdf',
            'fbi-cointelpro-black-extremists-part-01' => '/pdfs/external-imports/fbi-cointelpro-black-extremists-part-01.pdf',
            'fbi-cointelpro-black-extremists-part-08' => '/pdfs/external-imports/fbi-cointelpro-black-extremists-part-08.pdf',
            'fbi-cointelpro-black-extremists-part-10' => '/pdfs/external-imports/fbi-cointelpro-black-extremists-part-10.pdf',
            'fbi-cointelpro-new-left-hq-part-01' => '/pdfs/external-imports/fbi-cointelpro-new-left-hq-part-01.pdf',
            'fbi-cointelpro-puerto-rico-consolidated' => '/pdfs/external-imports/fbi-cointelpro-puerto-rico-consolidated.pdf',
            'fbi-cointelpro-swp-consolidated' => '/pdfs/external-imports/fbi-cointelpro-swp-consolidated.pdf',
            'fbi-cointelpro-white-hate-groups-part-01' => '/pdfs/external-imports/fbi-cointelpro-white-hate-groups-part-01.pdf',
            'fbi-fred-hampton-part-01' => '/pdfs/external-imports/fbi-fred-hampton-part-01.pdf',
            'fbi-malcolm-x-nyc-field-office' => '/pdfs/external-imports/fbi-malcolm-x-nyc-field-office.pdf',
            'fbi-malcolm-x-part-01' => '/pdfs/external-imports/fbi-malcolm-x-part-01.pdf',
            'fbi-sclc-cominfil-1964' => '/pdfs/external-imports/fbi-sclc-cominfil-1964.pdf',
            'fbi-stokely-carmichael-consolidated' => '/pdfs/external-imports/fbi-stokely-carmichael-consolidated.pdf',
            'fbi-stokely-carmichael-part-01' => '/pdfs/external-imports/fbi-stokely-carmichael-part-01.pdf',
            'fbi-stokely-carmichael-part-05' => '/pdfs/external-imports/fbi-stokely-carmichael-part-05.pdf',
            'fbi-weather-underground-part-6' => '/pdfs/external-imports/fbi-weather-underground-part-6.pdf',
            'tfsr-jay-ward-interview-zine-tfsr' => '/pdfs/external-imports/tfsr-jay-ward-interview-zine-tfsr.pdf',
            'tfsr-joan-braune-interview-zine-tfsr' => '/pdfs/external-imports/tfsr-joan-braune-interview-zine-tfsr.pdf',
        ];

        $unfetchable = [
            'black-and-pink-black-and-pink-lgbtq-prisoner-resource-list',
            'churchill-vanderwall-agents-of-repression',
            'fa-c23-interview-with-josefina-rodriguez',
            'fa-c325-counterinsurgency-in-the-courtroom-the-resistance-conspiracy-case',
            'fa-c325-international-day-to-stop-violence-against-women-solidarity-statement',
            'fag-c13-the-object-is-to-win',
            'fag-c237-akwasi-evans-reads-space',
            'fag-c261-stonewall-means-fight-back-fight-for-lesbian-and-gay-liberation',
            'fag-c261-support-the-chilean-resistance',
            'fbi-black-panther-party-part-01',
            'fbi-black-panther-party-part-06',
            'fbi-cesar-chavez-part-1',
            'fbi-cesar-chavez-part-2',
            'fbi-cointelpro-puerto-rican-groups-part-01',
            'fbi-cointelpro-swp-part-01',
            'fbi-cointelpro-swp-part-05',
            'fbi-fred-hampton-part-2',
            'fbi-george-jackson-file',
            'fbi-jean-seberg-file',
            'fbi-john-trudell-file',
            'fbi-kathy-boudin-part-01',
            'fbi-malcolm-x-part-20',
            'fbi-malcolm-x-part-33',
            'fbi-martin-luther-king-jr-part-1',
            'fbi-martin-luther-king-jr-part-2',
            'fbi-move-file',
            'fbi-russell-means-file',
            'fbi-viola-liuzzo-part-1',
            'fbi-weather-underground-part-1',
            'goldman-berkman-fragment-prison-1919',
            'loc-alien-anarchists-exclusion-1919',
            'peltier-flawed-justice-leonard-peltier-defense-committee',
            'sf8-37-year-old-case-drop-the-charges',
            'sf8-africa-today-on-grand-jury-repression',
            'sf8-attorneys-respond-to-reopening-sun-reporter',
            'sf8-bookmark-and-flyer-2008-08-27',
            'sf8-francisco-torres-on-the-dismissal',
            'sf8-free-the-san-francisco-8-la-prison-times',
            'sf8-free-the-sf8-flyer-2008-03-05',
            'sf8-legacy-of-torture-sf-bayview-2007',
            'sf8-signed-photograph',
        ];

        $updated = 0;
        foreach ($mapping as $slug => $file) {
            $rec = ArchiveRecord::where('slug', $slug)->first();
            if ($rec) {
                $rec->update(['file' => $file]);
                $updated++;
            } else {
                $this->warn("Not found: {$slug}");
            }
        }
        $this->info("Repointed {$updated} records to local files.");

        $nulled = 0;
        foreach ($unfetchable as $slug) {
            $rec = ArchiveRecord::where('slug', $slug)->first();
            if ($rec && $rec->file && str_starts_with((string)$rec->file, 'http')) {
                $rec->update(['file' => null]);
                $nulled++;
            }
        }
        $this->info("Nulled file field on {$nulled} unfetchable records.");

        return self::SUCCESS;
    }
}
