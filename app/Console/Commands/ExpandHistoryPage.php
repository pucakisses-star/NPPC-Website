<?php

namespace App\Console\Commands;

use App\Models\HistoryEra;
use App\Models\HistoryTopic;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Expands the /history scrollytelling page: fixes the corrupted "AAAAAA"
 * Sedition Act title, gives Coxey's Army its missing photograph, and adds
 * eleven new topics (Whiskey Rebellion through Stop Cop City) with public
 * domain / CC images from database/data/photos/history/ (see CREDITS.md
 * there). After inserting, each era's topics are renumbered chronologically
 * by the first year in their date label. Idempotent and safe to re-run.
 */
class ExpandHistoryPage extends Command {
    protected $signature = 'history:expand';
    protected $description = 'Add new topics and images to the history page';

    /** [era title fragment, topic title, date_label, caption_era, caption_label, bg_class, image file, summary] */
    private const TOPICS = [
        [
            'New Republic', 'The Whiskey Rebellion', '1791 – 1794', '1794',
            'Washington reviews the army raised against the whiskey rebels — painting attributed to Frederick Kemmelmeyer',
            'vbg-1700', 'whiskey-rebellion.jpg',
            'Before the Sedition Act, there was the excise. When farmers in western Pennsylvania resisted the new federal whiskey tax — with petitions, liberty poles, and finally arms — President Washington marched nearly 13,000 militiamen across the Alleghenies to put them down. Some twenty suspected rebels were seized in nighttime raids and marched three hundred miles to Philadelphia to stand trial for treason; two, John Mitchell and Philip Weigel, were convicted and sentenced to hang before Washington pardoned them. It was the first time the new republic imprisoned its own citizens for resisting federal power, and it set the template every later crackdown would follow: overwhelming force, exemplary prosecutions, and mercy only after the point had been made.',
        ],
        [
            'Abolition', 'John Brown & Harpers Ferry', '1859', '1859',
            'John Brown in 1859, the year of the raid — portrait by Martin M. Lawrence',
            'vbg-abolition', 'john-brown.jpg',
            'After the raid on the federal armory at Harpers Ferry failed, Virginia tried John Brown for treason, murder, and inciting insurrection while he lay wounded on a cot in the courtroom. He was hanged on December 2, 1859; six of his men followed him to the gallows. Brown used his six weeks in the Charles Town jail to write the letters that made him the most consequential political prisoner of the century — "I, John Brown, am now quite certain that the crimes of this guilty land will never be purged away but with blood." Eighteen months later the country was at war, and Union soldiers marched south singing his name.',
        ],
        [
            'Abolition', 'The Dakota War Trials', '1862', '1862',
            'The mass execution at Mankato, December 26, 1862 — contemporary lithograph',
            'vbg-civilwar', 'dakota-war-trials.jpg',
            'In the six weeks after the U.S.–Dakota War of 1862, a five-man military commission tried 392 Dakota men — some in hearings that lasted less than five minutes, none with counsel — and sentenced 303 of them to death. President Lincoln personally reviewed the transcripts and commuted most of the sentences, but on December 26, 1862, thirty-eight Dakota men were hanged together at Mankato, Minnesota: the largest mass execution in American history. Sixteen hundred Dakota women, children, and elders spent that winter imprisoned in a stockade below Fort Snelling, and the survivors were exiled from the state by act of Congress. The census counts the Mankato thirty-eight among the first political prisoners of the modern record.',
        ],
        [
            'McCarthyism', 'The Smith Act Trials', '1949 – 1957', '1949',
            'Convicted Communist Party leaders outside the Foley Square courthouse, 1949',
            'vbg-mccarthy', 'smith-act-trials.jpg',
            'The 1940 Smith Act made it a crime to advocate — or to belong to a party that advocated — the overthrow of the government, and after the war the Justice Department used it to decapitate the Communist Party. Eleven national leaders were convicted at Foley Square in 1949 after a nine-month trial; their defense lawyers were jailed for contempt alongside them. The Supreme Court blessed the convictions in Dennis v. United States (1951), and more than 140 party officials were indicted nationwide before Yates v. United States (1957) gutted the doctrine — too late for the men and women who had already served their years for a conviction built on books and speeches.',
        ],
        [
            'COINTELPRO', 'Attica & the Prison Rebellion Years', '1971', '1971',
            'The yard at Attica after the state retook the prison, September 1971',
            'vbg-cointelpro', 'attica.jpg',
            'On September 9, 1971, nearly 1,300 prisoners seized the yard at Attica and issued demands that read like a bill of rights: medical care, religious freedom, an end to censorship, a minimum wage. Four days later New York State took the prison back with helicopters and shotguns, killing thirty-nine men — hostages and prisoners alike — in fifteen minutes, and torturing survivors in the days that followed. Sixty-two prisoners were indicted for the uprising; no trooper was ever prosecuted for the retaking. Attica turned the prisoners\' rights movement into a national force and put the phrase "political prisoner" back into the American vocabulary, where the census has kept it.',
        ],
        [
            'COINTELPRO', 'AIM & Wounded Knee', '1973 – 1976', '1973',
            'American Indian Movement security at Wounded Knee, 1973',
            'vbg-cointelpro', 'wounded-knee.jpg',
            'For seventy-one days in 1973, American Indian Movement activists and Oglala Lakota traditionalists occupied the hamlet of Wounded Knee on the Pine Ridge Reservation, under fire from federal marshals, the FBI, and armored personnel carriers. The siege produced more than 500 arrests and a wave of federal prosecutions; the leadership case against Dennis Banks and Russell Means collapsed in 1974 when a federal judge dismissed all charges for prosecutorial misconduct, calling the government\'s conduct a "pollution of justice." The violence on Pine Ridge did not end with the occupation — and out of the 1975 firefight that followed came the case of Leonard Peltier, the census\'s longest-running entry.',
        ],
        [
            'Terror', 'The Whistleblowers', '2010 – Present', '2013',
            'Chelsea Manning after her release — photograph by Tim Travers Hawkins (CC0)',
            'vbg-terror', 'whistleblowers.jpg',
            'Since 2010 the Espionage Act — written for German saboteurs in 1917 — has become the government\'s standard weapon against its own truth-tellers. Chelsea Manning was sentenced to thirty-five years for giving the public the Iraq and Afghanistan war logs, then jailed again for refusing to testify to a grand jury; Reality Winner received sixty-three months for a single page about election interference; Daniel Hale got forty-five months for proving that most drone-strike victims were not the intended targets. More Espionage Act cases have been brought against journalistic sources since 2009 than in the statute\'s entire prior history — and none of the defendants was permitted to tell the jury why they did it.',
        ],
        [
            'Terror', 'Standing Rock', '2016 – 2017', '2016',
            '"We can\'t drink oil" — NoDAPL solidarity march, November 2016 (photo: Pax Ahimsa Gethen, CC BY-SA 4.0)',
            'vbg-2000', 'standing-rock.jpg',
            'The camps that rose against the Dakota Access Pipeline drew thousands of water protectors to Standing Rock — and one of the largest protest prosecutions in modern American history: more than 800 people arrested, many charged with felony rioting for standing on their own treaty land. Private security firm TigerSwan ran military-style counterinsurgency operations against the movement, briefing police that the water protectors were an "insurgency." Red Fawn Fallis served fifty-seven months in federal prison in a case built on an informant who was also her boyfriend. Standing Rock wrote the modern playbook — conspiracy charges, surveillance contractors, felony counts for misdemeanor conduct — that every pipeline and forest fight since has faced.',
        ],
        [
            'Terror', 'Stop Cop City', '2021 – Present', '2023',
            'Banner and memorial to Tortuguita, Atlanta, January 2023 (photo: Tatsoi, CC BY-SA 4.0)',
            'vbg-green', 'stop-cop-city.jpg',
            'The movement to stop a police training center in Atlanta\'s Weelaunee Forest became the test case for treating protest itself as terrorism. Beginning in December 2022, forest defenders were charged under Georgia\'s domestic-terrorism statute — many for offenses as slight as trespassing — and in January 2023 state troopers shot and killed Manuel "Tortuguita" Paez Terán in the forest. That September, sixty-one people were indicted together under the state RICO act, the broadest conspiracy prosecution of a protest movement in a generation, sweeping in legal observers and a bail fund. The forest cases entered the census as a cohort in 2024, and most defendants are still waiting for trial.',
        ],
        [
            'Labor', 'The Scottsboro Nine', '1931 – 1950', '1931',
            'The Scottsboro defendants with attorney Samuel Leibowitz under National Guard watch, 1932',
            'vbg-1900', 'scottsboro-nine.jpg',
            'Nine Black teenagers pulled off a freight train in Alabama in 1931 were convicted of a fabricated rape within two weeks, eight of them sentenced to death by all-white juries. Their case became a worldwide cause: the Communist Party\'s legal arm fought it to the Supreme Court twice, winning the right to counsel in Powell v. Alabama and the right to jury pools that included Black citizens in Norris v. Alabama. One accuser recanted entirely; Alabama kept retrying and reconvicting anyway, and the last of the nine did not leave prison until 1950. The state issued posthumous pardons in 2013 — eighty-two years after the arrests.',
        ],
        [
            'Labor', 'The Bonus Army', '1932', '1932',
            'Bonus Army veterans encamped on the Capitol lawn, 1932',
            'vbg-1900', 'bonus-army.jpg',
            'In the summer of 1932, more than 40,000 First World War veterans and their families camped in Washington to demand early payment of their service bonus. On July 28, on orders that ran from President Hoover through General Douglas MacArthur, the Army moved on its own veterans with cavalry, tanks, and tear gas, burning the Anacostia camp to the ground; two veterans were shot dead by police and hundreds were injured and arrested. No protest before television did more to show Americans what the state was willing to do to peaceful petitioners — and the image of the burning camps helped end a presidency.',
        ],
    ];

    public function handle(): int {
        // 1. Repair the corrupted Sedition Act title.
        $junk = HistoryTopic::where('title', 'AAAAAA')->first();
        if ($junk) {
            $junk->title = 'The Sedition Act';
            $junk->save();
            $this->info('Fixed corrupted topic title -> The Sedition Act');
        }

        // 2. Give the Anti-Rent War its missing image (the topic renders a
        //    blank panel without one) — a period anti-rent meeting broadside.
        $antiRent = HistoryTopic::where('title', 'The Anti-Rent War')->first();
        if ($antiRent && ! $antiRent->image) {
            $antiRent->image = $this->installImage('anti-rent-war.jpg');
            $antiRent->caption_era = $antiRent->caption_era ?: '1840s';
            $antiRent->caption_label = '"Attention! Anti-Renters! Awake! Arouse!" — broadside for an anti-rent meeting, Rensselaer County, NY';
            $antiRent->save();
            $this->info('Added image to The Anti-Rent War');
        }

        // 3. Give Coxey's Army its missing photograph.
        $coxey = HistoryTopic::where('title', "Coxey's Army")->first();
        if ($coxey && ! $coxey->image) {
            $coxey->image = $this->installImage('coxeys-army.jpg');
            $coxey->caption_era = $coxey->caption_era ?: '1894';
            $coxey->caption_label = 'Coxey\'s Army on the march to Washington, 1894 — Ray Stannard Baker, Library of Congress';
            $coxey->save();
            $this->info("Added photograph to Coxey's Army");
        }

        // 4. Add the new topics.
        $added = 0;
        foreach (self::TOPICS as [$eraFragment, $title, $date, $capEra, $capLabel, $bg, $image, $summary]) {
            $era = HistoryEra::where('title', 'like', '%'.$eraFragment.'%')->first();
            if (! $era) {
                $this->warn("No era matching '{$eraFragment}' — skipped {$title}.");
                continue;
            }
            $topic = HistoryTopic::firstOrCreate(
                ['history_era_id' => $era->id, 'title' => $title],
                [
                    'date_label' => $date,
                    'summary' => $summary,
                    'image' => $this->installImage($image),
                    'bg_class' => $bg,
                    'caption_era' => $capEra,
                    'caption_label' => $capLabel,
                    'sort_order' => 999,
                ]
            );
            if ($topic->wasRecentlyCreated) {
                $added++;
                $this->info("Added: {$title} ({$era->title})");
            } else {
                $this->line("Exists: {$title}");
            }
        }

        // 5. Renumber every era's topics chronologically by the first year in
        //    their date label (stable, so same-year topics keep their order).
        foreach (HistoryEra::with('topics')->get() as $era) {
            $sorted = $era->topics->sortBy(function (HistoryTopic $t) {
                preg_match('/\d{4}/', $t->date_label, $m);

                return [(int) ($m[0] ?? 9999), $t->sort_order];
            })->values();
            foreach ($sorted as $i => $topic) {
                $order = ($i + 1) * 10;
                if ($topic->sort_order !== $order) {
                    $topic->sort_order = $order;
                    $topic->save();
                }
            }
        }

        $this->info("Done. {$added} topic(s) added; eras renumbered chronologically.");

        return self::SUCCESS;
    }

    private function installImage(string $file): string {
        $disk = Storage::disk('public');
        $dest = 'history/'.$file;
        $src = database_path('data/photos/history/'.$file);
        if (! $disk->exists($dest) || md5($disk->get($dest)) !== md5_file($src)) {
            $disk->makeDirectory('history');
            $disk->put($dest, file_get_contents($src));
        }

        return $dest;
    }
}
