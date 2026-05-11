<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Downloads PDFs from Boston ABC's zine-distro page that aren't already
 * in public/pdfs/abc-chapters/, saving them with the
 * bostonanarchistblackcross.__<name>.pdf naming convention the existing
 * ImportAbcChapters command expects. Most are hosted on archive.org, a
 * few on the Boston ABC wordpress upload area.
 *
 * After downloading, runs archive:import-abc-chapters to register them
 * as ArchiveRecord rows.
 */
final class FetchBostonZineDistro extends Command {
    protected $signature = 'archive:fetch-boston-zine-distro {--skip-import : skip the import step}';
    protected $description = 'Download Boston ABC zine-distro PDFs and import them into the archive';

    /**
     * @var array<int,string>
     */
    private array $urls = [
        'https://archive.org/download/GrandJuryInvestigationsFbiHarassmentAndYourRights/grand_jury_investigations.pdf',
        'https://bostonanarchistblackcross.wordpress.com/wp-content/uploads/2018/10/crimethinc-surviving-a-grand-jury.pdf',
        'https://ia600106.us.archive.org/21/items/TalkingToTheMediaAGuideForAnarchists/talking-to-media-IMPOSED.pdf',
        'https://ia600800.us.archive.org/1/items/12ThingsToDoInsteadOfCallingTheCops/12things-print.pdf',
        'https://ia600906.us.archive.org/20/items/AnonymitySecurity/anonymity_security.pdf',
        'https://ia600906.us.archive.org/6/items/BountyHuntersChildPredators/bounty_hunters_child_predators.pdf',
        'https://ia800101.us.archive.org/28/items/HowToSurviveAFelonyTrialKeepingYourHeadUpThroughTheWorstOfIt/felony-print.pdf',
        'https://ia800107.us.archive.org/23/items/SurvivingAGrandJury/surviving-a-grand-jury.pdf',
        'https://ia800200.us.archive.org/32/items/SevenYearsAgainstPrison/seven_years_against_prison.pdf',
        'https://ia800307.us.archive.org/24/items/SecurityCounter-surveillance/security-countersurveillance.pdf',
        'https://ia800506.us.archive.org/25/items/AfterWeHaveBurntEverything/after_we_have_burnt-IMPOSED.pdf',
        'https://ia800508.us.archive.org/19/items/WhatIsPrisonerSupport/what_is_prisoner_support.pdf',
        'https://ia800700.us.archive.org/13/items/CaughtInTheWebOfDeception_271/caught_in_the_web.pdf',
        'https://ia800700.us.archive.org/19/items/SecurityCultureAHandbookForActivists/security_culture_handbook.pdf',
        'https://ia800702.us.archive.org/27/items/AlfPrisonerSupportTheBasics/alf_prisoner_support.pdf',
        'https://ia800704.us.archive.org/1/items/ProfilesOfProvocateurs_197/profiles_of_provocateurs.pdf',
        'https://ia800904.us.archive.org/21/items/EarthWarriorsAreOkZine_591/ewok_zine.pdf',
        'https://ia800907.us.archive.org/7/items/UntitledOrWhatToDoWhenEveryoneGetsArrested/untitled_or-print.pdf',
        'https://ia801300.us.archive.org/29/items/HowToStartAPrisonBooksCollective/prison_books_collective_manual.pdf',
        'https://ia801901.us.archive.org/35/items/WhateverYouDoDontTalkToThePolice/dont-talk-to-police-IMPOSED.pdf',
        'https://ia801902.us.archive.org/15/items/AWorldWithoutPolice/a-world-without-police-IMPOSED.pdf',
        'https://ia801902.us.archive.org/35/items/TheCriminalLegalSystemForRadicals/criminal-legal-system-radicals.pdf',
        'https://ia801903.us.archive.org/23/items/CollectiveActionBehindBars/collective-action-behind-bars.pdf',
        'https://ia801903.us.archive.org/35/items/Counter-infoAhow-toGuide/counter-info-howto-imposed.pdf',
        'https://ia801909.us.archive.org/32/items/AnActivistsGuideToInformationSecurity/activist-info-sec-IMPOSED.pdf',
        'https://ia802205.us.archive.org/34/items/MediaAHowToGuideForActivists/media_how_to_guide.pdf',
        'https://ia802205.us.archive.org/7/items/DigitalSecurityForActivists_439/digital_security_for_activists.pdf',
        'https://ia802304.us.archive.org/10/items/KnowYourRightsWhatYouNeedToKnow/know_your_rights_zine.pdf',
        'https://ia802604.us.archive.org/24/items/ResistTheGrandJuries/resist_the_grand_juries.pdf',
        'https://ia802604.us.archive.org/33/items/StopHuntingSheep/stop_hunting_sheep.pdf',
        'https://ia802604.us.archive.org/4/items/APracticalGuideToPrisonerSupport_89/a_practical_guide_to_prisoner_support.pdf',
        'https://ia802605.us.archive.org/13/items/StayCalm/stay_calm.pdf',
        'https://ia802605.us.archive.org/17/items/WhyMisogynistsMakeGreatInformants/misogynists_great_informants.pdf',
        'https://ia802606.us.archive.org/5/items/GrandJuriesToolsOfPoliticalRepression/grand_juries_tools_of_repression.pdf',
        'https://ia802607.us.archive.org/17/items/Copwatch101/copwatch_101.pdf',
        'https://ia802707.us.archive.org/15/items/3PositionsAgainstPrison/3_position_against_prison.pdf',
        'https://ia802800.us.archive.org/2/items/ResistGrandJuriesTheFrontLineIsEverywhere/resist-grand-juries-frontlines-IMPOSED.pdf',
        'https://ia802801.us.archive.org/16/items/AnarchistTacticsAtStandingRock/anarchist-tactics-at-standing-rock-IMPOSED.pdf',
        'https://ia802901.us.archive.org/15/items/Infoshop.orgGuideToFederalGrandJuryInvestigations/infoshop_guide_to_grand_jury_investigations.pdf',
        'https://ia802903.us.archive.org/5/items/TechToolsForActivism/tech_tools_for_activism.pdf',
        'https://ia802909.us.archive.org/20/items/WritingToPrisonersFrequentlyAskedQuestions/writing_to_prisoners_faq.pdf',
        'https://ia802909.us.archive.org/28/items/LondonCallingACellphoneAndInternetSecurityPrimer/london_calling.pdf',
        'https://ia902205.us.archive.org/18/items/SecurityCultureAnInteractiveSockPuppetFarce/security_culture_puppet_show.pdf',
        'https://ia902707.us.archive.org/20/items/AnarchyAlcohol/anarchy_and_alcohol_imposed.pdf',
    ];

    public function handle(): int {
        $dir = public_path('pdfs/abc-chapters');
        if (! is_dir($dir)) {
            if (! mkdir($dir, 0775, true) && ! is_dir($dir)) {
                $this->error("Could not create {$dir}");

                return self::FAILURE;
            }
        }

        $ok = 0;
        $skip = 0;
        $fail = 0;

        foreach ($this->urls as $i => $url) {
            $base = basename(parse_url($url, PHP_URL_PATH));
            $out = $dir.'/bostonanarchistblackcross.__'.$base;

            if (is_file($out) && filesize($out) > 0) {
                $skip++;

                continue;
            }

            try {
                $resp = Http::timeout(90)
                    ->withHeaders(['User-Agent' => 'NPPC-Archive/1.0'])
                    ->withOptions(['sink' => $out])
                    ->get($url);

                if (! $resp->successful() || filesize($out) < 1024) {
                    @unlink($out);
                    $this->warn(sprintf('FAIL %3d/%d  %s', $i + 1, count($this->urls), $url));
                    $fail++;

                    continue;
                }

                $this->info(sprintf('ok   %3d/%d  %s (%d KB)', $i + 1, count($this->urls), $base, intval(filesize($out) / 1024)));
                $ok++;
                usleep(400_000);
            } catch (\Throwable $e) {
                @unlink($out);
                $this->warn(sprintf('FAIL %3d/%d  %s — %s', $i + 1, count($this->urls), $url, $e->getMessage()));
                $fail++;
            }
        }

        $this->info("\nDownloaded={$ok} Skipped={$skip} Failed={$fail}");

        if ($ok > 0 && ! $this->option('skip-import')) {
            $this->info("\nRunning archive:import-abc-chapters…");
            $this->call('archive:import-abc-chapters');
        }

        return self::SUCCESS;
    }
}
