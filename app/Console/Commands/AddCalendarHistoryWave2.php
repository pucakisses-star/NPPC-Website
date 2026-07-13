<?php

namespace App\Console\Commands;

use App\Models\CalendarEntry;
use App\Models\Prisoner;
use Illuminate\Console\Command;

/**
 * Second curated wave of this-day-in-history calendar entries (24 events),
 * weighted toward the sparsest months (March had only six entries). All
 * dates are well-documented history; entries ship without images to avoid
 * the keyword-match failures fixed in calendar:fix-images — entries whose
 * subject exists in the prisoner database are linked so the calendar's
 * prisoner-photo fallback illustrates them. Idempotent on month/day/year
 * + title.
 */
final class AddCalendarHistoryWave2 extends Command {
    protected $signature = 'calendar:add-history-wave2';
    protected $description = 'Add 24 curated historical calendar entries (second wave)';

    public function handle(): int {
        $events = [
            [1, 21, 1977, 'Carter pardons Vietnam draft resisters', "On his first full day in office, President Jimmy Carter issued Proclamation 4483, an unconditional pardon for hundreds of thousands of men who had evaded the Vietnam-era draft. It remains one of the broadest acts of clemency for political offenses in American history — and it followed a decade in which draft resisters filled federal prisons.", null],
            [1, 30, 1956, "Martin Luther King Jr.'s home bombed in Montgomery", "During the Montgomery bus boycott, a bomb exploded on the porch of Dr. King's parsonage while his wife Coretta and infant daughter were inside. No one was hurt. King, who days earlier had been jailed on a pretextual speeding charge, calmed the armed crowd that gathered — while Alabama continued prosecuting the boycott's organizers rather than its bombers.", null],
            [2, 1, 1960, 'Greensboro sit-ins begin', "Four Black college students sat down at a whites-only Woolworth's lunch counter in Greensboro, North Carolina, and refused to leave. The sit-in movement they ignited spread to dozens of cities within weeks and put thousands of students in southern jails — many choosing \"jail, no bail\" to fill the cells rather than fund the system prosecuting them.", null],
            [2, 6, 1919, 'Seattle General Strike begins', "Sixty-five thousand workers shut down Seattle for five days in the first citywide general strike in U.S. history. The strike was peaceful and self-organized — strikers ran milk deliveries and hospital services — but it triggered a national red-scare panic, mass arrests of radicals, and the raids and deportations that followed that year.", null],
            [2, 15, 2003, 'Global antiwar protest; mass arrests in New York', "On the largest single day of protest in world history, millions marched against the coming invasion of Iraq. In New York, where a court had banned marching outside the UN, police penned, charged, and arrested hundreds of demonstrators — a preview of the mass-arrest tactics that would define the decade's protest policing.", null],
            [2, 28, 1972, "Angela Davis's trial opens in San Jose", "Charged with murder, kidnapping, and conspiracy over the Marin County courthouse raid — guns registered in her name were used — Davis had spent 16 months in jail, much of it in solitary, while an international \"Free Angela\" campaign grew. Her trial before an all-white jury opened in San Jose on this day.", 'Angela Davis'],
            [3, 3, 1913, 'Suffrage parade attacked in Washington', "The day before Woodrow Wilson's inauguration, thousands of suffragists marched down Pennsylvania Avenue and were mobbed — grabbed, spat on, and beaten while police stood by or joined in. Over a hundred marchers were hospitalized. The outrage revived the movement that would later fill the Occoquan Workhouse with jailed pickets.", null],
            [3, 5, 1770, 'Boston Massacre; Crispus Attucks killed', "British soldiers fired into a Boston crowd, killing five — the first of them Crispus Attucks, a sailor of African and Native descent who became the first martyr of the American Revolution. The bodies had barely cooled before the propaganda war over who counts as a criminal and who counts as a patriot began.", null],
            [3, 6, 1970, 'Greenwich Village townhouse explosion', "A bomb being assembled by Weather Underground members detonated prematurely in a Manhattan townhouse, killing Diana Oughton, Ted Gold, and Terry Robbins. The blast sent the organization fully underground and became the FBI's standing justification for a decade of extraordinary surveillance and burglaries directed at the entire left.", null],
            [3, 7, 1965, "Bloody Sunday on the Edmund Pettus Bridge", "Alabama troopers and a sheriff's posse attacked some 600 voting-rights marchers with clubs and tear gas as they crossed the Edmund Pettus Bridge in Selma. John Lewis's skull was fractured. The televised violence — inflicted by law enforcement on peaceful marchers — forced the Voting Rights Act onto the national agenda.", null],
            [3, 9, 1970, 'Ralph Featherstone and Che Payne killed by car bomb', "SNCC organizers Ralph Featherstone and William \"Che\" Payne died when a bomb exploded in their car near Bel Air, Maryland, where H. Rap Brown was to stand trial days later. Brown went underground and onto the FBI's Ten Most Wanted list; no one was ever charged for the killings.", 'Imam Jamil Al-Amin'],
            [3, 10, 1919, 'Supreme Court upholds Debs conviction', "In Debs v. United States, the Supreme Court unanimously upheld Eugene Debs's ten-year Espionage Act sentence for an anti-war speech in Canton, Ohio. Justice Holmes wrote the opinion. The ruling confirmed that, in wartime America, a speech could be a federal crime — and sent the country's most famous socialist to prison.", 'Eugene V. Debs'],
            [3, 20, 2000, 'Imam Jamil Al-Amin arrested in White Hall, Alabama', "Four days after the shootout outside his Atlanta store that killed Fulton County Deputy Ricky Kinchen, Imam Jamil Al-Amin — the former H. Rap Brown — was captured in White Hall, Alabama. He maintained his innocence through his 2002 conviction and for the rest of his life; another man's repeated confession was never heard by a jury.", 'Imam Jamil Al-Amin'],
            [3, 29, 1951, 'Julius and Ethel Rosenberg convicted', "A federal jury convicted the Rosenbergs of conspiracy to commit espionage at the height of the McCarthy era. The case — built on the coached testimony of Ethel's own brother — became the defining political prosecution of the Cold War.", null],
            [4, 5, 1951, 'Rosenbergs sentenced to death', "Judge Irving Kaufman sentenced Julius and Ethel Rosenberg to die, blaming them from the bench for the Korean War itself. Worldwide clemency appeals — from the Pope to Einstein — would fail over the next two years.", null],
            [5, 4, 1886, 'Haymarket bombing and police riot in Chicago', "A bomb thrown at police breaking up a peaceful eight-hour-day rally in Chicago's Haymarket Square killed seven officers; police gunfire killed at least four workers. Eight anarchists were convicted for their politics rather than the bombing — four were hanged, one died in his cell — in the case that gave the world May Day.", null],
            [6, 3, 2017, 'NSA contractor Reality Winner arrested', "FBI agents arrested Reality Winner at her Augusta, Georgia home for mailing a classified report on Russian election interference to The Intercept. Denied bail, she pleaded guilty and received 63 months — at the time the longest federal sentence ever imposed for leaking to the press.", 'Reality Winner'],
            [6, 4, 1972, 'Angela Davis acquitted on all charges', "After thirteen hours of deliberation, an all-white jury acquitted Angela Davis of every count. The verdict — after 16 months of pretrial jail and a global defense campaign — remains one of the movement's clearest courtroom victories.", 'Angela Davis'],
            [6, 13, 1971, 'The Pentagon Papers are published', "The New York Times began publishing the Defense Department's secret history of the Vietnam War, leaked by Daniel Ellsberg. Nixon's response — an injunction against the press, an Espionage Act prosecution, and a burglary of Ellsberg's psychiatrist's office — collapsed in a mistrial that exposed the government's lawlessness.", null],
            [6, 19, 1953, 'Julius and Ethel Rosenberg executed at Sing Sing', "The Rosenbergs were electrocuted at sundown, hours after the Supreme Court vacated a last-minute stay. Ethel was executed on evidence her own prosecutors privately doubted; decades later, her brother recanted the testimony that killed her.", null],
            [8, 21, 1971, 'George Jackson killed at San Quentin', "Guards shot Black Panther Field Marshal George Jackson in the prison yard during what officials called an escape attempt, three days before his Soledad Brothers trial. His death — still contested in nearly every detail — sparked the Attica uprising two weeks later and is commemorated every Black August.", 'George Jackson'],
            [8, 23, 1927, 'Sacco and Vanzetti executed', "Nicola Sacco and Bartolomeo Vanzetti died in Massachusetts's electric chair for a robbery-murder the evidence never tied them to, after seven years of worldwide protest. Fifty years later the state's governor proclaimed their trial unfair; the anarchists' names remain shorthand for execution as political punishment.", null],
            [8, 29, 1970, 'Chicano Moratorium attacked; Rubén Salazar killed', "Sheriff's deputies attacked the 25,000-strong Chicano Moratorium march against the Vietnam War in East Los Angeles, arresting hundreds. Los Angeles Times columnist Rubén Salazar — the movement's most prominent journalistic voice — was killed by a tear-gas projectile fired into the Silver Dollar Café.", null],
            [9, 5, 1917, 'Federal agents raid IWW halls nationwide', "Justice Department agents simultaneously raided 48 IWW halls and offices across the country, seizing five tons of records. The raids produced the mass Espionage Act indictment of the union's entire leadership — and effectively criminalized the country's most militant labor organization.", null],
            [9, 24, 1969, 'The Chicago Eight go on trial', "Eight movement leaders went on trial for \"conspiracy to incite a riot\" at the 1968 Democratic Convention — under the federal anti-riot law nicknamed for one of history's own defendants, the Rap Brown Act. The trial became political theater: Bobby Seale was bound and gagged in the courtroom before his case was severed.", null],
            [9, 30, 1919, 'Elaine massacre begins in Arkansas', "White mobs and federal troops killed more than a hundred Black sharecroppers — some estimates run far higher — after union organizing in Phillips County, Arkansas. The only people prosecuted were Black: twelve were sentenced to death by mob-dominated juries, a travesty the Supreme Court finally condemned in Moore v. Dempsey.", null],
            [10, 29, 1969, 'Bobby Seale bound and gagged in a Chicago courtroom', "After Black Panther chairman Bobby Seale repeatedly insisted on his right to represent himself, Judge Julius Hoffman ordered him chained to his chair and gagged in front of the jury — for days. The image became the era's starkest portrait of American political justice.", null],
            [12, 21, 1919, "The 'Soviet Ark' deports Emma Goldman and 248 others", "The USAT Buford sailed from New York for Soviet Russia carrying 249 deported radicals, Emma Goldman and Alexander Berkman among them — most guilty of nothing but membership and speech. J. Edgar Hoover, who built the deportation case, watched the ship leave.", null],
        ];

        $added = 0; $skipped = 0;
        foreach ($events as [$month, $day, $year, $title, $description, $prisonerName]) {
            $exists = CalendarEntry::where('month', $month)->where('day', $day)
                ->where('year', $year)->where('title', $title)->exists();
            if ($exists) {
                $this->line("Already present: {$month}/{$day} {$title}");
                $skipped++;
                continue;
            }
            $prisonerId = null;
            if ($prisonerName) {
                $prisonerId = Prisoner::withoutGlobalScopes()
                    ->where('name', 'like', '%'.$prisonerName.'%')
                    ->value('id');
                if (! $prisonerId) {
                    $this->warn("Prisoner not found for link: {$prisonerName} (entry added unlinked)");
                }
            }
            CalendarEntry::create([
                'month' => $month, 'day' => $day, 'year' => $year,
                'title' => $title, 'description' => $description,
                'published' => true, 'prisoner_id' => $prisonerId,
            ]);
            $this->info("Added: {$month}/{$day}/{$year} {$title}");
            $added++;
        }

        // Correction: the "FALN clemency campaign opens with Carter remarks"
        // entry (July 11, 1985) describes an event that doesn't exist in the
        // record — no 1985 Carter FALN statement is documented anywhere.
        // Replace it with the documented event it garbles: Carter's Sept. 6,
        // 1979 commutation of the Puerto Rican Nationalists.
        $faln = CalendarEntry::where('month', 7)->where('day', 11)
            ->where('title', 'like', '%FALN clemency campaign%')->first();
        if ($faln) {
            $faln->update([
                'month' => 9, 'day' => 6, 'year' => 1979,
                'title' => 'Carter commutes the sentences of the Puerto Rican Nationalists',
                'description' => "President Jimmy Carter commuted the sentences of Oscar Collazo, Lolita Lebrón, Rafael Cancel Miranda, and Irving Flores — the Puerto Rican Nationalists imprisoned since the 1950 attack on Blair House and the 1954 shooting in the U.S. House of Representatives. Released days later after more than a quarter century inside, they flew home to San Juan to a heroes' welcome.\n\nTwenty years later, Carter would again back clemency for Puerto Rican independence prisoners, urging President Clinton to release the FALN prisoners freed in 1999.",
            ]);
            $this->info('Corrected: bogus July 11, 1985 FALN/Carter entry replaced with the Sept 6, 1979 commutation.');
        }

        // Correction: the existing Reality Winner sentencing entry sits on
        // Dec 22, 2017 — she was sentenced August 23, 2018.
        $winner = CalendarEntry::where('month', 12)->where('day', 22)
            ->where('title', 'like', '%Reality Winner sentenced%')->first();
        if ($winner) {
            $winner->update(['month' => 8, 'day' => 23, 'year' => 2018]);
            $this->info('Corrected: Reality Winner sentencing moved to Aug 23, 2018.');
        }

        $this->info("\nDone. Added={$added} Skipped={$skipped}");

        return self::SUCCESS;
    }
}
