# Placeholder Image Curation

This document is a shopping list of images you need to download, save under
`public/images/site/`, and commit. Once each file is in place the corresponding
Blade template will pick it up automatically — every spot already has a
`file_exists()` guard so dropping the file in is the only step required.

If you skip an image, the page silently falls back to its existing gradient
placeholder. No broken-image icons.

## Where images go

```
public/images/site/about-history.jpg
public/images/site/about-team.jpg
public/images/site/about-write-letter.jpg
public/images/site/getinvolved-featured.jpg
public/images/site/store-hero.jpg
public/images/site/store-feature.jpg
```

Recommended size: **1600×1100px JPG, ~75% quality**, under 400 KB each. They'll
be served at responsive widths so going larger is wasted bandwidth.

## License rules

Only use images from these sources:

1. **Library of Congress** ([loc.gov/free-to-use](https://www.loc.gov/free-to-use/)) — public domain, no attribution required (but credit it anyway).
2. **National Archives Catalog** ([catalog.archives.gov](https://catalog.archives.gov/)) — most images are public domain; check rights statement on each item.
3. **Wikimedia Commons** ([commons.wikimedia.org](https://commons.wikimedia.org/)) — only files marked CC0, public domain, or CC-BY (CC-BY-SA is fine for our use). **Capture the photographer's name and license URL** for the attribution file below.
4. **Unsplash** ([unsplash.com](https://unsplash.com/)) — Unsplash License (free for any use, attribution appreciated).
5. **Pexels** ([pexels.com](https://www.pexels.com/)) — Pexels License (same as above).

**Do NOT use:**
- Any image from a news site (AP, Reuters, Getty, NYT, etc.) — copyrighted.
- Any image from social media unless the original poster has explicitly licensed it permissively.
- Any image of a specific living person without verifying license — even on Wikimedia Commons, portrait photos may have subject-rights complications.

## Per-spot recommendations

### 1. `about-history.jpg` — Our History block on `/about`
Tone: archival, weighty, civil-rights era. Black and white preferred. Crowd or single iconic figure.

**Search starting points:**
- LoC Civil Rights collection: <https://www.loc.gov/free-to-use/civil-rights-movement/>
- LoC search: <https://www.loc.gov/photos/?q=civil+rights+protest&fa=online-format:image>
- Specific candidate (public domain): "Civil Rights March on Washington, D.C." (1963), LoC ID `LC-DIG-ppmsca-04293` — <https://www.loc.gov/pictures/item/2003654393/>
- National Archives: search "civil rights protest" filtered to public domain — <https://catalog.archives.gov/>

Alt text in template: *"History of political imprisonment in the United States"*

### 2. `about-team.jpg` — Our Team block on `/about`
Tone: hands-on, organizing, multi-racial group of people working together. Doesn't need to be the actual NPPC team — a generic community-meeting / round-table organizing photo is fine.

**Search starting points:**
- Unsplash: <https://unsplash.com/s/photos/community-meeting>
- Unsplash: <https://unsplash.com/s/photos/grassroots-organizing>
- Pexels: <https://www.pexels.com/search/community%20organizing/>

Alt text: *"The NPPC team"*

### 3. `about-write-letter.jpg` — "Write a Letter" CTA card on `/about`
Tone: warm, hands-on. A hand writing a letter; an envelope and stamps; ink pen on paper. Frames the prisoner-letter program emotionally.

**Search starting points:**
- Unsplash: <https://unsplash.com/s/photos/handwritten-letter>
- Pexels: <https://www.pexels.com/search/writing%20a%20letter/>
- Pexels: <https://www.pexels.com/search/letter%20envelope%20stamp/>

The Blade overlays a 70%-black gradient on this image, so darker / contrastier shots work best (white type sits on top).

Alt text: *"Write a letter to a political prisoner"*

### 4. `getinvolved-featured.jpg` — Featured Case panel fallback on `/get-involved`
Used **only** when there's no in-DB prisoner with a photo to feature. Tone: silhouette / hands raised at protest / barbed wire — anonymous so it doesn't claim to depict a specific person.

**Search starting points:**
- Unsplash: <https://unsplash.com/s/photos/protest-silhouette>
- Unsplash: <https://unsplash.com/s/photos/barbed-wire-prison>
- Pexels: <https://www.pexels.com/search/protest%20fist/>

Alt text: *"Get involved"*

### 5. `store-hero.jpg` — `/store` top hero (when no featured product is set)
Tone: NPPC merch flat-lay or a confident protest-poster aesthetic. Stack of t-shirts, books, a folded poster, stickers. Or a bold "ABOLITION" / "FREE THEM ALL" type mock-up. Will sit beside black background; high-contrast subjects work best.

**Search starting points:**
- Unsplash: <https://unsplash.com/s/photos/protest-poster>
- Unsplash: <https://unsplash.com/s/photos/activist-merch>
- Pexels: <https://www.pexels.com/search/protest%20sign/>

Alt text: *"Shop to support political prisoners"*

### 6. `store-feature.jpg` — "Goods That Do Good" feature panel on `/store`
Tone: warm, product-y. Hands holding a t-shirt, a stack of zines, a button collection. Pairs visually with the hero so don't repeat the same composition.

**Search starting points:**
- Unsplash: <https://unsplash.com/s/photos/screen-print-shirt>
- Unsplash: <https://unsplash.com/s/photos/zine-stack>
- Pexels: <https://www.pexels.com/search/handmade%20goods/>

Alt text: *"Goods that do good"*

## Workflow

1. Open each search link above in your browser.
2. Pick an image that fits the tone notes.
3. Download the largest-resolution version offered.
4. (Optional) Resize / compress to ~1600px wide, JPG 75% quality, using whatever tool you prefer (ImageMagick, Photoshop, Squoosh).
5. Save it to `public/images/site/<filename>.jpg` matching the names above.
6. Commit:

   ```bash
   git add public/images/site/
   git commit -m "Add curated section images"
   git push origin claude/search-archive-cases-XTiV7
   ```

7. Pull on the server (`git pull origin claude/search-archive-cases-XTiV7`). The Blades pick the new files up immediately — no Vue rebuild, no cache flush.

## Attribution file

If you use any Unsplash / Pexels / Wikimedia photo, append a row to
`public/images/site/CREDITS.md` so we have a paper trail:

```markdown
| File                       | Source        | Photographer / Author | License            | URL                                    |
| -------------------------- | ------------- | --------------------- | ------------------ | -------------------------------------- |
| about-history.jpg          | Library of Congress | (n/a)           | Public Domain      | https://www.loc.gov/pictures/item/...  |
| about-team.jpg             | Unsplash      | First Last            | Unsplash License   | https://unsplash.com/photos/abc123     |
```

LoC and National Archives public-domain images don't legally need attribution
but adding the catalog ID makes future maintenance easier — if the file gets
lost or replaced you can re-find the original.
