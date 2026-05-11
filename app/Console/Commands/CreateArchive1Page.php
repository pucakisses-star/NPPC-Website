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

    /**
     * @return array<int, array{title: string, desc: string, term: string, glyph: string}>
     */
    private function collections(): array {
        return [
            ['title' => 'Black Liberation', 'desc' => 'The Black Liberation Movement and its imprisoned veterans, including the Black Panther Party, Black Liberation Army, Republic of New Afrika, and contemporary heirs to that tradition.', 'term' => 'Black Liberation', 'glyph' => 'BL'],
            ['title' => 'Indigenous Resistance', 'desc' => 'American Indian Movement, treaty-rights defenders, water and land protectors from Wounded Knee to Standing Rock to Stop Cop City.', 'term' => 'AIM', 'glyph' => 'IR'],
            ['title' => 'Puerto Rican Independence', 'desc' => 'FALN, Los Macheteros, the Nationalist Party of Puerto Rico, and the long campaign against U.S. colonial rule on the island.', 'term' => 'Puerto Rican', 'glyph' => 'PR'],
            ['title' => 'Anti-Imperialism', 'desc' => 'United Freedom Front, May 19th Communist Organization, and the white anti-imperialist solidarity tradition.', 'term' => 'Anti-imperialism', 'glyph' => 'AI'],
            ['title' => 'Anarchist Movement', 'desc' => 'Anarchist Black Cross, anti-fascist defendants, NATO 3, Cleveland 4, and contemporary anarchist prisoners and grand-jury resisters.', 'term' => 'Anarchist', 'glyph' => 'AN'],
            ['title' => 'Earth & Animal Liberation', 'desc' => 'Earth Liberation Front, Animal Liberation Front, Green Scare prosecutions, and the ecodefense tradition.', 'term' => 'ELF', 'glyph' => 'EL'],
            ['title' => 'Plowshares & Peace Activism', 'desc' => 'The Berrigan tradition of disarmament actions, conscientious objectors, and nuclear-resistance activists.', 'term' => 'Plowshares', 'glyph' => 'PS'],
            ['title' => 'Whistleblowers & Hackers', 'desc' => 'Chelsea Manning, Reality Winner, Jeremy Hammond, Barrett Brown, and other digital-era political prisoners.', 'term' => 'Whistleblower', 'glyph' => 'WH'],
            ['title' => 'Anti-Police Resistance', 'desc' => 'Cases stemming from confrontations with policing, from BLA-era cop cases to contemporary anti-police defendants.', 'term' => 'Anti-police', 'glyph' => 'AP'],
            ['title' => 'Palestine Solidarity', 'desc' => 'Holy Land Foundation Five, Rasmea Odeh, Sami Al-Arian, and others targeted for Palestine advocacy.', 'term' => 'Palestine', 'glyph' => 'PA'],
            ['title' => 'GI & War Resisters', 'desc' => 'Soldiers who refused unjust wars, deserters, and conscientious objectors from Vietnam onward.', 'term' => 'War resister', 'glyph' => 'GI'],
            ['title' => 'MOVE Organization', 'desc' => 'The MOVE 9, the Africa family, and the legacy of the 1985 Philadelphia police bombing.', 'term' => 'MOVE', 'glyph' => 'MV'],
            ['title' => 'Government Repression', 'desc' => 'COINTELPRO, grand juries, preemptive prosecutions, and the apparatus of political imprisonment.', 'term' => 'COINTELPRO', 'glyph' => 'GR'],
            ['title' => 'Historical Cases', 'desc' => 'Sacco and Vanzetti, the Rosenbergs, the Haymarket martyrs, and political prisoners from the pre-1970 era.', 'term' => 'Historical', 'glyph' => 'HC'],
        ];
    }

    /**
     * @return array<int, array{term: string, weight: int}>
     */
    private function commonTerms(): array {
        // weight is a font-size hint (1..5)
        return [
            ['term' => 'Political Prisoners', 'weight' => 5],
            ['term' => 'Black Liberation', 'weight' => 5],
            ['term' => 'National Liberation', 'weight' => 4],
            ['term' => 'Anti-Imperialism', 'weight' => 4],
            ['term' => 'Resistance', 'weight' => 4],
            ['term' => 'Prison', 'weight' => 4],
            ['term' => 'COINTELPRO', 'weight' => 3],
            ['term' => 'Civil Rights', 'weight' => 3],
            ['term' => 'Latin America', 'weight' => 3],
            ['term' => 'Palestine', 'weight' => 3],
            ['term' => 'Puerto Rico', 'weight' => 3],
            ['term' => 'Women', 'weight' => 3],
            ['term' => 'Anti-Racism', 'weight' => 2],
            ['term' => 'Black Panther Party', 'weight' => 2],
            ['term' => 'Poetry', 'weight' => 2],
            ['term' => 'Human Rights', 'weight' => 2],
            ['term' => 'Anti-War', 'weight' => 2],
            ['term' => 'Africa', 'weight' => 2],
            ['term' => 'African American', 'weight' => 2],
            ['term' => 'Cuba', 'weight' => 1],
            ['term' => 'Mexico', 'weight' => 1],
            ['term' => 'Nicaragua', 'weight' => 1],
            ['term' => 'Viet Nam', 'weight' => 1],
            ['term' => 'Middle East', 'weight' => 1],
            ['term' => 'Chicano', 'weight' => 1],
            ['term' => 'Israel', 'weight' => 1],
            ['term' => 'Chile', 'weight' => 1],
            ['term' => 'Music', 'weight' => 1],
        ];
    }

    /**
     * @return array<int, array{href: string, title: string, blurb: string}>
     */
    private function featured(): array {
        return [
            ['href' => '/prisoner/mumia-abu-jamal', 'title' => 'Mumia Abu-Jamal', 'blurb' => 'Award-winning journalist and former Black Panther; on death row from 1982 until 2011 and serving life since.'],
            ['href' => '/prisoner/leonard-peltier', 'title' => 'Leonard Peltier', 'blurb' => 'American Indian Movement member imprisoned since 1977 in a case the FBI itself has been forced to acknowledge as flawed.'],
            ['href' => '/prisoner/ojore-lutalo', 'title' => 'Ojore Lutalo', 'blurb' => 'New Afrikan anarchist; served 28 years in New Jersey, much of it in solitary; now an acclaimed collage artist.'],
            ['href' => '/prisoner/oscar-lopez-rivera', 'title' => 'Oscar López Rivera', 'blurb' => 'Puerto Rican independentista who served 36 years before President Obama commuted his sentence in 2017.'],
            ['href' => '/archive', 'title' => 'Movement Press Archive', 'blurb' => 'Digitized issues of 4StruggleMag and other prisoner-support periodicals, going back to the 1970s.'],
        ];
    }

    private function renderBody(): string {
        $collections = '';
        foreach ($this->collections() as $c) {
            $href = '/search?q='.rawurlencode($c['term']);
            $collections .= sprintf(
                '<a href="%s" class="a1-coll-card">'.
                    '<div class="a1-coll-thumb">%s</div>'.
                    '<div class="a1-coll-text">'.
                        '<h3>%s</h3>'.
                        '<p>%s</p>'.
                    '</div>'.
                '</a>',
                htmlspecialchars($href, ENT_QUOTES),
                htmlspecialchars($c['glyph'], ENT_QUOTES),
                htmlspecialchars($c['title'], ENT_QUOTES),
                htmlspecialchars($c['desc'], ENT_QUOTES)
            );
        }

        $chips = '';
        foreach ($this->commonTerms() as $t) {
            $href = '/search?q='.rawurlencode($t['term']);
            $chips .= sprintf(
                '<a href="%s" class="a1-tag a1-tag-w%d">%s</a> ',
                htmlspecialchars($href, ENT_QUOTES),
                $t['weight'],
                htmlspecialchars($t['term'], ENT_QUOTES)
            );
        }

        $featured = '';
        foreach ($this->featured() as $f) {
            $featured .= sprintf(
                '<li><a href="%s"><span class="a1-feat-title">%s</span><span class="a1-feat-blurb">%s</span></a></li>',
                htmlspecialchars($f['href'], ENT_QUOTES),
                htmlspecialchars($f['title'], ENT_QUOTES),
                htmlspecialchars($f['blurb'], ENT_QUOTES)
            );
        }

        $coll_count = count($this->collections());

        return <<<HTML
<style>
  .a1-wrap { --a1-accent: #5660fe; --a1-line: rgba(255,255,255,0.15); }
  .a1-welcome { background: rgba(255, 235, 165, 0.08); border: 1px solid rgba(255, 235, 165, 0.25); border-left: 4px solid #f5d061; padding: 20px 24px; border-radius: 4px; margin: 0 0 40px; font-size: 15px; line-height: 1.65; }
  .a1-welcome b { font-weight: 800; }
  .a1-section-head { font-size: 28px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; color: var(--a1-accent); margin: 0 0 16px; font-family: 'Verlag', 'Helvetica Neue', sans-serif; }
  .a1-search-row { display: flex; gap: 12px; align-items: center; margin-bottom: 48px; }
  .a1-search-pill { flex: 1; display: flex; align-items: center; gap: 12px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); border-radius: 999px; padding: 12px 22px; }
  .a1-search-pill svg { flex-shrink: 0; opacity: 0.6; }
  .a1-search-pill input { flex: 1; background: transparent; border: none; outline: none; color: #fff; font-size: 16px; }
  .a1-search-pill input::placeholder { color: rgba(255,255,255,0.4); }
  .a1-search-help { width: 28px; height: 28px; border-radius: 50%; border: 1px solid rgba(255,255,255,0.3); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,0.6); font-size: 13px; font-weight: 700; }
  .a1-search-btn { background: transparent; color: var(--a1-accent); border: 1px solid var(--a1-accent); border-radius: 999px; padding: 12px 28px; font-size: 13px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.12em; cursor: pointer; }
  .a1-search-btn:hover { background: var(--a1-accent); color: #fff; }
  .a1-grid { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 56px; align-items: start; }
  @media (max-width: 900px) { .a1-grid { grid-template-columns: 1fr; gap: 40px; } }
  .a1-coll-card { display: flex; gap: 20px; align-items: stretch; padding: 18px; border: 1px solid var(--a1-line); border-radius: 6px; margin-bottom: 16px; text-decoration: none; color: inherit; transition: border-color 0.15s ease, background 0.15s ease; }
  .a1-coll-card:hover { border-color: var(--a1-accent); background: rgba(86, 96, 254, 0.05); }
  .a1-coll-thumb { flex: 0 0 120px; min-height: 120px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(194,65,12,0.18), rgba(86,96,254,0.18)); border: 1px solid var(--a1-line); border-radius: 4px; font-family: 'Verlag', 'Helvetica Neue', sans-serif; font-weight: 800; font-size: 36px; letter-spacing: 0.04em; color: rgba(255,255,255,0.85); }
  .a1-coll-text h3 { font-size: 22px; font-weight: 700; margin: 0 0 8px; line-height: 1.25; }
  .a1-coll-text p { margin: 0; font-size: 14px; line-height: 1.55; opacity: 0.78; }
  .a1-tags { line-height: 2.4; padding: 8px 4px 16px; }
  .a1-tag { display: inline-block; color: var(--a1-accent); text-decoration: none; margin: 0 6px 4px 0; font-weight: 600; transition: opacity 0.15s ease; }
  .a1-tag:hover { opacity: 0.7; text-decoration: underline; }
  .a1-tag-w1 { font-size: 13px; opacity: 0.7; }
  .a1-tag-w2 { font-size: 15px; opacity: 0.8; }
  .a1-tag-w3 { font-size: 18px; }
  .a1-tag-w4 { font-size: 22px; font-weight: 700; }
  .a1-tag-w5 { font-size: 26px; font-weight: 800; }
  .a1-feat-list { list-style: none; padding: 0; margin: 0; }
  .a1-feat-list li { border-bottom: 1px solid var(--a1-line); }
  .a1-feat-list li:last-child { border-bottom: none; }
  .a1-feat-list a { display: block; padding: 14px 4px; color: inherit; text-decoration: none; transition: background 0.15s ease; }
  .a1-feat-list a:hover { background: rgba(86, 96, 254, 0.05); }
  .a1-feat-title { display: block; font-size: 16px; font-weight: 700; margin-bottom: 4px; color: var(--a1-accent); }
  .a1-feat-blurb { display: block; font-size: 13px; opacity: 0.75; line-height: 1.5; }
  .a1-divider { border: none; border-top: 1px solid var(--a1-line); margin: 40px 0; }
</style>

<div class="a1-wrap">
  <div class="a1-welcome">
    <b>Welcome to the NPPC Archive.</b> Archive1 is a searchable index of the National Political Prisoner Coalition&rsquo;s records: prisoner profiles, case files, movement periodicals, court documents, and oral histories. It collects, in one place, the materials that document the history of U.S. political imprisonment from the late nineteenth century to the present.
  </div>

  <h2 class="a1-section-head">Search the Archives</h2>
  <form action="/search" method="GET" role="search" class="a1-search-row">
    <label class="a1-search-pill">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/></svg>
      <input type="text" name="q" placeholder="Search archives&hellip;" required>
    </label>
    <a href="/faq" class="a1-search-help" title="Search help" aria-label="Search help">?</a>
    <button type="submit" class="a1-search-btn">Search</button>
  </form>

  <div class="a1-grid">
    <div class="a1-collections">
      <h2 class="a1-section-head">Browse by Collection</h2>
      {$collections}
    </div>

    <aside class="a1-sidebar">
      <h2 class="a1-section-head">Common Search Terms</h2>
      <div class="a1-tags">{$chips}</div>

      <hr class="a1-divider">

      <h2 class="a1-section-head">Featured Content</h2>
      <ul class="a1-feat-list">{$featured}</ul>
    </aside>
  </div>
</div>

<!-- {$coll_count} collections -->
HTML;
    }
}
