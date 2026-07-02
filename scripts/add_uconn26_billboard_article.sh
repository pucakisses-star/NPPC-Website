#!/usr/bin/env bash
# Run on production server (104.238.162.40):
#   cd /var/www/NPPC-Website && bash scripts/add_uconn26_billboard_article.sh
# Adds a September 13, 2024 press release announcing the UConn 26 billboard campaign.
set +e

cat > /tmp/add_uconn26_article.php << 'PHPEOF'
<?php

// Find or create category
$category = \App\Models\Category::firstOrCreate(
    ['slug' => 'press-releases'],
    ['title' => 'Press Releases']
);

// Find or create author
$author = \App\Models\Author::firstOrCreate(
    ['name' => 'NPPC'],
    ['name' => 'NPPC']
);

$title = 'NPPC Launches Billboard Campaign Demanding Charges Be Dropped Against the UConn 26';
$slug  = 'nppc-launches-billboard-campaign-uconn-26';

if (\App\Models\Article::where('slug', $slug)->exists()) {
    echo 'Article already exists — skipping.' . PHP_EOL;
    exit;
}

$intro = 'The National Political Prisoner Coalition has launched a billboard campaign in the greater Storrs, Connecticut area demanding that prosecutors drop all charges against the UConn 26 — students, faculty, and community members arrested during a peaceful pro-Palestinian encampment protest on the UConn campus in May 2024.';

$body = <<<'HTML'
<p><strong>FOR IMMEDIATE RELEASE</strong><br>
September 13, 2024<br>
Contact: media@nppc.org</p>

<p><strong>NPPC Launches Billboard Campaign Demanding Charges Be Dropped Against the UConn 26</strong></p>

<p><em>Billboards near UConn campus and Hartford call for dismissal of charges against 26 students, faculty, and activists arrested at May 2024 pro-Palestinian encampment</em></p>

<p><strong>Storrs, CT</strong> — The National Political Prisoner Coalition (NPPC) today announced the launch of a billboard campaign in the greater Storrs, Connecticut area demanding that local prosecutors drop all charges against the UConn 26 — 26 students, faculty, and community members arrested during a peaceful pro-Palestinian encampment protest on the University of Connecticut campus in May 2024.</p>

<p>The billboards, appearing on major routes near the UConn campus and in the Hartford metropolitan area, display the message <em>"DROP THE CHARGES: Free the UConn 26"</em> and are designed to sustain public pressure on both the University administration and the State's Attorney's office as defendants' cases move through Connecticut courts.</p>

<p>"These 26 people were exercising their constitutionally protected rights to free speech and peaceful assembly," said an NPPC spokesperson. "The University called in police to suppress a student encampment that posed no threat to anyone. The charges against them are politically motivated and must be dropped."</p>

<p>The UConn 26 were arrested on May 2, 2024, when UConn administration ordered police to clear a campus encampment established in solidarity with Palestinians in Gaza and to demand the University divest from defense contractors. Protesters were charged with breach of peace and trespassing — misdemeanor charges that nonetheless carry the risk of criminal records and serious immigration consequences for some defendants.</p>

<p>The billboard campaign is part of a broader NPPC effort to document and bring visibility to the hundreds of students and community members across the country who have faced criminal charges for their participation in the 2024 wave of campus solidarity encampments. The NPPC notes that many of the UConn 26 are first-time defendants with no prior involvement with the criminal legal system.</p>

<p>"Universities should be spaces for free inquiry and dissent," the NPPC statement continued. "Instead, UConn chose to criminalize its own students for speaking out against a genocide. We will not let these cases disappear quietly into the courts."</p>

<p>The NPPC calls on supporters to:</p>
<ul>
<li>Contact the Connecticut State's Attorney's office to demand all charges be dropped</li>
<li>Attend court dates to show solidarity with the defendants</li>
<li>Donate to the UConn 26 legal defense fund</li>
<li>Share the campaign on social media using <strong>#DropTheCharges</strong> and <strong>#FreeTheUConn26</strong></li>
</ul>

<p>Legal support for the UConn 26 is being coordinated by the National Lawyers Guild Connecticut Chapter.</p>

<p>###</p>

<p><em>The National Political Prisoner Coalition (NPPC) advocates for political prisoners in the United States and documents cases of politically motivated prosecution. For more information, visit nppc.org or email media@nppc.org.</em></p>
HTML;

$article = \App\Models\Article::create([
    'title'        => $title,
    'slug'         => $slug,
    'intro'        => $intro,
    'body'         => $body,
    'image'        => '',
    'category_id'  => $category->id,
    'author_id'    => $author->id,
    'published_at' => '2024-09-13',
]);

echo 'Created article: ' . $article->title . PHP_EOL;
echo 'Slug: ' . $article->slug . PHP_EOL;
echo 'URL: /news/' . $article->slug . PHP_EOL;
PHPEOF

echo "Adding UConn 26 billboard press release..."
php artisan tinker --execute="require '/tmp/add_uconn26_article.php';"
echo "Done."
