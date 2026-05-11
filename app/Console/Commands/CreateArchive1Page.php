<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

final class CreateArchive1Page extends Command {
    protected $signature = 'archive:create-archive1';
    protected $description = 'Create or refresh the /archive1 page (search-style archive landing modeled on search.freedomarchives.org)';

    public function handle(): int {
        $body = $this->renderBody();

        $page = Page::updateOrCreate(
            ['slug' => 'archive1'],
            [
                'title' => 'Archive1',
                'body' => $body,
            ]
        );

        $this->info("Page saved (id={$page->id}, slug={$page->slug}). View at /archive1");

        return self::SUCCESS;
    }

    private function renderBody(): string {
        $collections = [
            ['Black Liberation', 'Black Panther Party, Black Liberation Army, Republic of New Afrika, and the broader struggle for Black self-determination.', 'Black Liberation'],
            ['Indigenous Resistance', 'American Indian Movement, treaty-rights defenders, water and land protectors from Wounded Knee to Standing Rock.', 'AIM'],
            ['Puerto Rican Independence', 'FALN, Macheteros, Nationalist Party of Puerto Rico, and the long campaign against U.S. colonial rule.', 'Puerto Rican'],
            ['Anti-Imperialism', 'United Freedom Front, May 19th Communist Organization, and the white anti-imperialist solidarity tradition.', 'Anti-imperialism'],
            ['Anarchist Movement', 'Anarchist Black Cross, anti-fascist defendants, NATO 3, Cleveland 4, and contemporary anarchist prisoners.', 'Anarchist'],
            ['Earth & Animal Liberation', 'Earth Liberation Front, Animal Liberation Front, Green Scare prosecutions, and ecodefense.', 'ELF'],
            ['Plowshares & Peace Activism', 'Berrigan tradition disarmament actions, conscientious objectors, and nuclear-resistance activists.', 'Plowshares'],
            ['Whistleblowers & Hackers', 'Chelsea Manning, Reality Winner, Jeremy Hammond, Barrett Brown, and other digital-era political prisoners.', 'Whistleblower'],
            ['Anti-Police Resistance', 'Cases stemming from confrontations with policing, including BLA-era cop cases and contemporary anti-police defendants.', 'Anti-police'],
            ['Palestine Solidarity', 'Holy Land Foundation Five, Rasmea Odeh, Sami Al-Arian, and others targeted for Palestine advocacy.', 'Palestine'],
            ['GI & War Resisters', 'Soldiers who refused unjust wars, deserters, and conscientious objectors from Vietnam onward.', 'War resister'],
            ['MOVE Organization', 'The MOVE 9, the Africa family, and the legacy of the 1985 Philadelphia police bombing.', 'MOVE'],
            ['Government Repression', 'COINTELPRO, grand-jury resisters, preemptive-prosecution cases, and the apparatus of political imprisonment.', 'COINTELPRO'],
            ['Historical Cases', 'Sacco and Vanzetti, the Rosenbergs, the Haymarket martyrs, and political prisoners from the pre-1970 era.', 'Historical'],
        ];

        $commonTerms = [
            'COINTELPRO', 'Black Liberation Army', 'MOVE 9', 'AIM', 'Black Panther Party',
            'Earth Liberation Front', 'FALN', 'Plowshares', 'Anarchist Black Cross',
            'Grand Jury Resistance', 'Stop Cop City', 'Standing Rock',
        ];

        $featured = [
            ['/prisoner/mumia-abu-jamal', 'Mumia Abu-Jamal', 'Award-winning journalist, former Black Panther, on death row 1982–2011 and serving life since.'],
            ['/prisoner/leonard-peltier', 'Leonard Peltier', 'American Indian Movement member imprisoned since 1977 for a case the FBI itself has been forced to acknowledge as flawed.'],
            ['/prisoner/ojore-lutalo', 'Ojore Lutalo', 'New Afrikan anarchist; served 28 years in New Jersey, much of it in solitary; now an acclaimed collage artist.'],
            ['/prisoner/oscar-lopez-rivera', 'Oscar López Rivera', 'Puerto Rican independentista who served 36 years before President Obama commuted his sentence in 2017.'],
            ['/archive', 'Movement Press Archive', 'Browse digitized issues of 4StruggleMag and other prisoner-support periodicals.'],
        ];

        $html = <<<'HTML'
<p style="font-size: 18px; line-height: 1.6; opacity: 0.85; max-width: 760px;">
  Archive1 is a searchable index of the National Political Prisoner Coalition's records: prisoner profiles, case files, movement periodicals, court documents, and oral histories. It collects, in one place, the materials that document the history of U.S. political imprisonment from the late nineteenth century to the present.
</p>

<!-- ============ SEARCH ============ -->
<section style="margin-top: 48px; margin-bottom: 56px; padding: 32px; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; background: rgba(255,255,255,0.02);">
  <h2 style="font-size: 28px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 8px;">Search the Archives</h2>
  <p style="font-size: 14px; opacity: 0.7; margin-bottom: 20px;">Search across prisoners, articles, cases, and movement publications.</p>
  <form action="/search" method="GET" role="search" style="display: flex; gap: 12px; align-items: center; max-width: 720px;">
    <input type="text" name="q" placeholder="Search prisoners, cases, articles, publications&hellip;" required style="flex: 1; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 6px; padding: 14px 18px; color: #fff; font-size: 16px; outline: none;" />
    <button type="submit" style="background: #5660fe; color: #fff; border: none; border-radius: 6px; padding: 14px 28px; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; cursor: pointer;">Search</button>
  </form>
</section>

<!-- ============ COLLECTIONS ============ -->
<section style="margin-bottom: 56px;">
  <header style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 12px; margin-bottom: 24px;">
    <h2 style="font-size: 24px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;">Browse by Collection</h2>
    <span style="font-size: 13px; opacity: 0.5;">__COLLECTION_COUNT__ collections</span>
  </header>
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px;">
__COLLECTION_CARDS__
  </div>
</section>

<!-- ============ COMMON TERMS ============ -->
<section style="margin-bottom: 56px;">
  <header style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 12px; margin-bottom: 20px;">
    <h2 style="font-size: 24px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;">Common Search Terms</h2>
  </header>
  <div style="display: flex; flex-wrap: wrap; gap: 10px;">
__COMMON_TERMS__
  </div>
</section>

<!-- ============ FEATURED ============ -->
<section style="margin-bottom: 56px;">
  <header style="display: flex; align-items: baseline; justify-content: space-between; border-bottom: 2px solid rgba(255,255,255,0.2); padding-bottom: 12px; margin-bottom: 24px;">
    <h2 style="font-size: 24px; font-weight: 800; letter-spacing: 0.04em; text-transform: uppercase;">Featured Content</h2>
  </header>
  <ul style="list-style: none; padding: 0; margin: 0;">
__FEATURED_ITEMS__
  </ul>
</section>

<style>
  .archive1-card { display: block; padding: 20px; border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; text-decoration: none; color: inherit; transition: border-color 0.15s ease, background 0.15s ease; }
  .archive1-card:hover { border-color: rgba(86,96,254,0.6); background: rgba(86,96,254,0.05); }
  .archive1-chip { display: inline-block; padding: 8px 14px; border: 1px solid rgba(255,255,255,0.2); border-radius: 999px; text-decoration: none; color: inherit; font-size: 13px; transition: border-color 0.15s ease, background 0.15s ease; }
  .archive1-chip:hover { border-color: rgba(86,96,254,0.6); background: rgba(86,96,254,0.08); }
</style>
HTML;

        $cards = '';
        foreach ($collections as [$name, $desc, $term]) {
            $href = '/search?q='.rawurlencode($term);
            $cards .= sprintf(
                "    <a href=\"%s\" class=\"archive1-card\">\n      <h3 style=\"font-size: 18px; font-weight: 800; letter-spacing: 0.02em; margin-bottom: 8px;\">%s</h3>\n      <p style=\"font-size: 14px; line-height: 1.55; opacity: 0.75; margin: 0;\">%s</p>\n    </a>\n",
                htmlspecialchars($href, ENT_QUOTES),
                htmlspecialchars($name, ENT_QUOTES),
                htmlspecialchars($desc, ENT_QUOTES)
            );
        }

        $chips = '';
        foreach ($commonTerms as $term) {
            $href = '/search?q='.rawurlencode($term);
            $chips .= sprintf(
                "    <a href=\"%s\" class=\"archive1-chip\">%s</a>\n",
                htmlspecialchars($href, ENT_QUOTES),
                htmlspecialchars($term, ENT_QUOTES)
            );
        }

        $items = '';
        foreach ($featured as [$href, $title, $desc]) {
            $items .= sprintf(
                "    <li style=\"padding: 16px 0; border-bottom: 1px solid rgba(255,255,255,0.1);\"><a href=\"%s\" style=\"color: inherit; text-decoration: none; display: block;\"><div style=\"font-size: 18px; font-weight: 700; margin-bottom: 4px;\">%s</div><div style=\"font-size: 14px; opacity: 0.7; line-height: 1.55;\">%s</div></a></li>\n",
                htmlspecialchars($href, ENT_QUOTES),
                htmlspecialchars($title, ENT_QUOTES),
                htmlspecialchars($desc, ENT_QUOTES)
            );
        }

        return strtr($html, [
            '__COLLECTION_COUNT__' => (string) count($collections),
            '__COLLECTION_CARDS__' => rtrim($cards, "\n"),
            '__COMMON_TERMS__' => rtrim($chips, "\n"),
            '__FEATURED_ITEMS__' => rtrim($items, "\n"),
        ]);
    }
}
