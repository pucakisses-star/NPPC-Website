@php use App\Models\HistoryEra; @endphp
@php
/** @var HistoryEra[] $eras */

// Approximate (lat, lng) coords for major topic locations. Used to plot
// markers on the per-era Leaflet map slide. Topics not in this map are
// silently skipped on the map (the map slide still renders for the era,
// but only with the topics it can locate).
$topicCoords = [
    'The Sedition Act'                   => [39.9526, -75.1652, 'Philadelphia, PA'],
    'The Abolition Movement'             => [42.3601, -71.0589, 'Boston, MA'],
    'The Civil War'                      => [38.9072, -77.0369, 'Washington, DC'],
    'The Labor Movement'                 => [41.8867, -87.6437, 'Haymarket Square, Chicago, IL'],
    'Suffragism'                         => [42.9106, -76.7960, 'Seneca Falls, NY'],
    'The First Red Scare'                => [40.7128, -74.0060, 'New York, NY'],
    'World War I'                        => [38.9072, -77.0369, 'Washington, DC'],
    'World War II'                       => [38.0306, -121.8636, 'Manzanar / U.S. internment camps (CA)'],
    'McCarthyism'                        => [38.8899, -77.0091, 'U.S. Capitol, Washington, DC'],
    'The Civil Rights Movement'          => [32.4071, -87.0211, 'Selma, AL'],
    'The Vietnam War'                    => [41.1467, -81.3470, 'Kent State, Kent, OH'],
    'COINTELPRO'                         => [38.8951, -77.0364, 'FBI HQ, Washington, DC'],
    'Puerto Rican Independence Movement' => [18.4655, -66.1057, 'San Juan, Puerto Rico'],
    'The War on Terror'                  => [40.7128, -74.0060, 'New York, NY (Holy Land Foundation)'],
    'The Green Scare'                    => [44.0521, -123.0868, 'Eugene, OR'],
    'Anonymous'                          => [40.7128, -74.0060, 'New York, NY (LulzSec / Anonymous)'],
    'Occupy Wall Street'                 => [40.7090, -74.0113, 'Zuccotti Park, NYC'],
    'Black Lives Matter'                 => [44.9778, -93.2650, 'Minneapolis, MN'],
];
@endphp

@extends('app')

@section('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<style>
:root {
  --h-accent: #5660fe;
  --h-dim: rgba(255,255,255,0.5);
  --h-faint: rgba(255,255,255,0.08);
}

/* Progress Bar */
.progress-bar {
  position: fixed;
  top: 0; left: 0;
  height: 3px;
  background: var(--h-accent);
  z-index: 9999;
  width: 0%;
}

/* Era Nav */
.era-nav {
  position: sticky;
  top: 0; z-index: 100;
  background: rgba(0,0,0,0.94);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--h-faint);
  overflow-x: auto;
  white-space: nowrap;
  scrollbar-width: none;
}
.era-nav::-webkit-scrollbar { display: none; }
.era-nav-inner {
  display: flex;
  max-width: 1080px;
  margin: 0 auto;
  padding: 0 16px;
}
.era-nav a {
  display: inline-block;
  padding: 14px 20px;
  font-size: 0.85rem;
  font-weight: 700;
  color: var(--h-dim);
  border-bottom: 2px solid transparent;
  transition: all 0.2s;
  white-space: nowrap;
}
.era-nav a:hover { color: #fff; }
.era-nav a.active {
  color: #fff;
  border-bottom-color: var(--h-accent);
}

/* Hero */
.history-hero {
  min-height: 90vh;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 80px 16px;
  max-width: 800px;
  margin: 0 auto;
  text-align: center;
}
.history-hero h1 {
  font-size: 3.5rem;
  font-weight: 900;
  line-height: 1.06;
  margin-bottom: 1.5rem;
  color: #fff;
}
.history-hero p {
  font-size: 1.15rem;
  color: var(--h-dim);
  line-height: 1.7;
  max-width: 600px;
  margin: 0 auto;
}
.hero-scroll-hint {
  margin-top: 3rem;
  font-size: 0.8rem;
  color: rgba(255,255,255,0.25);
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

/* Sidecar Layout */
.sidecar {
  position: relative;
  display: flex;
  scroll-margin-top: 50px;
}
.sidecar-narrative {
  width: 45%;
  position: relative;
  z-index: 2;
}
.sidecar-visual {
  width: 55%;
  position: sticky;
  top: 50px;
  height: calc(100vh - 50px);
  overflow: hidden;
}
.visual-layer {
  position: absolute;
  inset: 0;
  opacity: 0;
  transition: opacity 0.6s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}
.visual-layer.active { opacity: 1; }
.visual-layer-bg {
  position: absolute;
  inset: 0;
}
.visual-layer-img {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  opacity: 0.4;
}
.visual-caption {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  padding: 60px 30px 30px;
  background: linear-gradient(0deg, rgba(0,0,0,0.8) 0%, transparent 100%);
  z-index: 2;
}
.visual-caption-era {
  font-size: 4rem;
  font-weight: 900;
  line-height: 1;
  opacity: 0.3;
  color: #fff;
}
.visual-caption-label {
  font-size: 0.95rem;
  color: rgba(255,255,255,0.6);
  margin-top: 4px;
}

/* Steps */
.step {
  min-height: 100vh;
  display: flex;
  align-items: center;
  padding: 60px 40px 60px 16px;
}
.step-content {
  opacity: 0.2;
  transform: translateY(10px);
  transition: opacity 0.5s ease, transform 0.5s ease;
  max-width: 420px;
}
.step.active .step-content {
  opacity: 1;
  transform: translateY(0);
}
.step-era-cover .step-content { max-width: 440px; }
.step-era-tag {
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.15em;
  text-transform: uppercase;
  color: var(--h-accent);
  margin-bottom: 0.8rem;
  display: block;
}
.step-era-title {
  font-size: 2.2rem;
  font-weight: 900;
  line-height: 1.1;
  margin-bottom: 1rem;
  color: #fff;
}
.step-era-desc {
  font-size: 1.05rem;
  color: rgba(255,255,255,0.65);
  line-height: 1.7;
}
.step-topic-date {
  font-size: 0.75rem;
  font-weight: 700;
  color: var(--h-accent);
  letter-spacing: 0.08em;
  text-transform: uppercase;
  margin-bottom: 0.5rem;
}
.step-topic-title {
  font-size: 1.6rem;
  font-weight: 900;
  line-height: 1.15;
  margin-bottom: 0.9rem;
  color: #fff;
}
.step-topic-summary {
  font-size: 0.95rem;
  color: rgba(255,255,255,0.7);
  line-height: 1.75;
}
.step-divider {
  width: 40px;
  height: 1px;
  background: rgba(255,255,255,0.2);
  margin-bottom: 1.2rem;
}

/* CTA */
.history-cta {
  text-align: center;
  padding: 6rem 16px;
  max-width: 600px;
  margin: 0 auto;
}
.history-cta h2 {
  font-size: 2rem;
  font-weight: 900;
  margin-bottom: 1rem;
  color: #fff;
}
.history-cta p {
  font-size: 1rem;
  color: var(--h-dim);
  margin-bottom: 2rem;
  line-height: 1.7;
}
.history-cta-btn {
  display: inline-block;
  background: var(--h-accent);
  color: #fff;
  padding: 14px 36px;
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  transition: background 0.2s;
}
.history-cta-btn:hover { background: #6e76ff; }

/* Visual BG tints */
.vbg-1700 { background: linear-gradient(160deg, #1a1810 0%, #2a2418 50%, #1a1508 100%); }
.vbg-sedition { background: linear-gradient(140deg, #1a1510 0%, #2a1e15 40%, #3a2a18 100%); }
.vbg-1800 { background: linear-gradient(160deg, #14101a 0%, #20152a 50%, #14101a 100%); }
.vbg-abolition { background: linear-gradient(140deg, #12101e 0%, #201828 50%, #2a1e35 100%); }
.vbg-civilwar { background: linear-gradient(140deg, #1a1012 0%, #2a181e 50%, #351e25 100%); }
.vbg-1900 { background: linear-gradient(160deg, #0a1014 0%, #0f1a22 50%, #0a1014 100%); }
.vbg-labor { background: linear-gradient(140deg, #101a12 0%, #18281a 50%, #0f2010 100%); }
.vbg-suffrage { background: linear-gradient(140deg, #1a121a 0%, #281828 50%, #201020 100%); }
.vbg-redscare { background: linear-gradient(140deg, #1a1414 0%, #2a1e1e 50%, #201515 100%); }
.vbg-ww1 { background: linear-gradient(140deg, #14141a 0%, #1e1e28 50%, #151520 100%); }
.vbg-mid1900 { background: linear-gradient(160deg, #0e0a14 0%, #16102a 50%, #0e0a14 100%); }
.vbg-ww2 { background: linear-gradient(140deg, #12140e 0%, #1a200f 50%, #141810 100%); }
.vbg-mccarthy { background: linear-gradient(140deg, #14100e 0%, #20180f 50%, #181210 100%); }
.vbg-civilrights { background: linear-gradient(140deg, #0e1214 0%, #0f1a20 50%, #0a1418 100%); }
.vbg-vietnam { background: linear-gradient(140deg, #141210 0%, #201a15 50%, #181410 100%); }
.vbg-late1900 { background: linear-gradient(160deg, #0a0e14 0%, #101822 50%, #0a0e14 100%); }
.vbg-cointelpro { background: linear-gradient(140deg, #100e14 0%, #18142a 50%, #100e18 100%); }
.vbg-puertorico { background: linear-gradient(140deg, #0e1410 0%, #142018 50%, #0e1810 100%); }
.vbg-2000 { background: linear-gradient(160deg, #0a0a16 0%, #0f0f28 50%, #0a0a16 100%); }
.vbg-terror { background: linear-gradient(140deg, #10101a 0%, #181828 50%, #121220 100%); }
.vbg-green { background: linear-gradient(140deg, #0e140e 0%, #142014 50%, #101810 100%); }
.vbg-anon { background: linear-gradient(140deg, #0e1018 0%, #141828 50%, #101420 100%); }
.vbg-occupy { background: linear-gradient(140deg, #14120e 0%, #201a10 50%, #181410 100%); }
.vbg-blm { background: linear-gradient(140deg, #10101a 0%, #181828 50%, #121220 100%); }

/* ─── Section title slides (full-bleed era dividers) ─── */
.era-divider {
  position: relative;
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  text-align: center;
  overflow: hidden;
  isolation: isolate;
}
.era-divider-bg {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  filter: grayscale(40%) brightness(0.45);
  z-index: 1;
  transform: scale(1.05);
  transition: transform 1.2s ease;
}
.era-divider.in-view .era-divider-bg {
  transform: scale(1);
}
.era-divider-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0,0,0,0.55) 0%, rgba(0,0,0,0.25) 50%, rgba(0,0,0,0.85) 100%);
  z-index: 2;
}
.era-divider-content {
  position: relative;
  z-index: 3;
  padding: 0 24px;
  max-width: 760px;
  opacity: 0;
  transform: translateY(20px);
  transition: opacity 0.8s ease 0.2s, transform 0.8s ease 0.2s;
}
.era-divider.in-view .era-divider-content {
  opacity: 1;
  transform: translateY(0);
}
.era-divider-tag {
  font-size: 0.8rem;
  font-weight: 700;
  letter-spacing: 0.18em;
  text-transform: uppercase;
  color: var(--h-accent);
  margin-bottom: 1rem;
  display: block;
}
.era-divider-title {
  font-size: 4rem;
  font-weight: 900;
  line-height: 1;
  color: #fff;
  margin-bottom: 1.5rem;
}
.era-divider-desc {
  font-size: 1.1rem;
  color: rgba(255,255,255,0.8);
  line-height: 1.7;
}

/* ─── Progress dots column inside each sidecar ─── */
.sidecar { position: relative; }
.sidecar-dots {
  position: absolute;
  left: calc(45% - 8px);
  top: 0;
  bottom: 0;
  width: 16px;
  display: none;
  pointer-events: none;
  z-index: 3;
}
@media (min-width: 901px) {
  .sidecar-dots { display: block; }
}
.sidecar-dots-inner {
  position: sticky;
  top: 50%;
  transform: translateY(-50%);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}
.sidecar-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: rgba(255,255,255,0.18);
  transition: background 0.3s ease, transform 0.3s ease;
}
.sidecar-dot.active {
  background: var(--h-accent);
  transform: scale(1.6);
}

/* ─── Map slide (Leaflet) ─── */
.visual-layer-map {
  position: absolute;
  inset: 0;
  z-index: 1;
}
.visual-layer-map .leaflet-container {
  width: 100%;
  height: 100%;
  background: #0a0a14;
}
.leaflet-tile-pane { filter: invert(1) hue-rotate(180deg) brightness(0.95) saturate(0.6); }
.leaflet-control-attribution {
  background: rgba(0,0,0,0.6) !important;
  color: rgba(255,255,255,0.6) !important;
}
.leaflet-control-attribution a {
  color: rgba(255,255,255,0.7) !important;
}

/* ─── Immersive variant for the era cover step ─── */
.step-era-cover .step-content {
  background: rgba(0,0,0,0.45);
  backdrop-filter: blur(2px);
  padding: 24px 28px;
  border-left: 3px solid var(--h-accent);
}

@@media (max-width: 900px) {
  .sidecar { flex-direction: column; }
  .sidecar-narrative { width: 100%; }
  .sidecar-visual { width: 100%; position: relative; top: auto; height: 50vh; }
  .step { min-height: auto; padding: 40px 16px; }
  .history-hero h1 { font-size: 2.2rem; }
  .step-era-title { font-size: 1.6rem; }
  .step-topic-title { font-size: 1.3rem; }
  .visual-caption-era { font-size: 2.5rem; }
  .era-divider { height: 70vh; }
  .era-divider-title { font-size: 2.5rem; }
}
</style>
@endsection

@section('body')
<div class="bg-black">
    <div class="progress-bar" id="progressBar"></div>

    <!-- Era Nav -->
    <nav class="era-nav" id="eraNav">
        <div class="era-nav-inner">
            @foreach($eras as $i => $era)
                <a href="#sc-{{ $era->slug }}" @if($i === 0) class="active" @endif>{{ $era->nav_label }}</a>
            @endforeach
        </div>
    </nav>

    <!-- Hero -->
    <section class="history-hero">
        <h1>The History of Political Prisoners in the United States</h1>
        <p>From the Sedition Act of 1798 to the Black Lives Matter movement, the United States has a long and often hidden history of imprisoning people for their political beliefs, activism, and resistance.</p>
        <div class="hero-scroll-hint">Scroll to explore &darr;</div>
    </section>

    <!-- Eras -->
    @foreach($eras as $era)
    @php
        // Pick a hero image for the era divider: first topic image with one,
        // else fall back to a neutral gradient via the era bg class.
        $eraHeroImage = null;
        foreach ($era->topics as $t) {
            if ($t->image) { $eraHeroImage = \Storage::url($t->image); break; }
        }

        // Build the marker list for this era's map slide.
        $eraMarkers = [];
        foreach ($era->topics as $t) {
            if (isset($topicCoords[$t->title])) {
                [$lat, $lng, $label] = $topicCoords[$t->title];
                $eraMarkers[] = [
                    'lat'   => $lat,
                    'lng'   => $lng,
                    'label' => $label,
                    'title' => $t->title,
                    'date'  => $t->date_label,
                ];
            }
        }
    @endphp

    <!-- Section title slide (full-bleed era divider) -->
    <section class="era-divider {{ $era->bg_class }}">
        @if($eraHeroImage)
            <div class="era-divider-bg" style="background-image: url('{{ $eraHeroImage }}');"></div>
        @else
            <div class="era-divider-bg {{ $era->bg_class }}"></div>
        @endif
        <div class="era-divider-overlay"></div>
        <div class="era-divider-content">
            <span class="era-divider-tag">{{ $era->tag_line }}</span>
            <h2 class="era-divider-title">{{ $era->heading }}</h2>
            <p class="era-divider-desc">{{ $era->description }}</p>
        </div>
    </section>

    <section class="sidecar" id="sc-{{ $era->slug }}" data-era="{{ $era->nav_label }}">

        <!-- Progress dots column (one dot per scroll-stop in this era) -->
        <div class="sidecar-dots" aria-hidden="true">
            <div class="sidecar-dots-inner">
                <span class="sidecar-dot @if($loop->first) active @endif" data-step-index="0"></span>
                @foreach($era->topics as $i => $topic)
                    <span class="sidecar-dot" data-step-index="{{ $i + 1 }}"></span>
                @endforeach
                @if(count($eraMarkers) > 0)
                    <span class="sidecar-dot" data-step-index="{{ $era->topics->count() + 1 }}"></span>
                @endif
            </div>
        </div>

        <div class="sidecar-narrative">
            <!-- Era cover step -->
            <div class="step step-era-cover @if($loop->first) active @endif" data-visual="v-{{ $era->slug }}-cover" data-step-index="0">
                <div class="step-content">
                    <span class="step-era-tag">{{ $era->tag_line }}</span>
                    <h2 class="step-era-title">{{ $era->heading }}</h2>
                    <p class="step-era-desc">{{ $era->description }}</p>
                </div>
            </div>

            <!-- Topic steps -->
            @foreach($era->topics as $i => $topic)
            <div class="step" data-visual="v-topic-{{ $topic->id }}" data-step-index="{{ $i + 1 }}">
                <div class="step-content">
                    <div class="step-divider"></div>
                    <div class="step-topic-date">{{ $topic->date_label }}</div>
                    <h3 class="step-topic-title">{{ $topic->title }}</h3>
                    <p class="step-topic-summary">{{ $topic->summary }}</p>
                </div>
            </div>
            @endforeach

            @if(count($eraMarkers) > 0)
            <!-- Map slide step -->
            <div class="step" data-visual="v-{{ $era->slug }}-map" data-step-index="{{ $era->topics->count() + 1 }}">
                <div class="step-content">
                    <div class="step-divider"></div>
                    <div class="step-topic-date">Where it happened</div>
                    <h3 class="step-topic-title">A geography of repression</h3>
                    <p class="step-topic-summary">
                        The events of the {{ $era->nav_label }} era spread across the country —
                        from {{ $eraMarkers[0]['label'] ?? '' }}@if(count($eraMarkers) > 1) to {{ $eraMarkers[count($eraMarkers)-1]['label'] }}@endif.
                        Each marker is a story this database documents.
                    </p>
                </div>
            </div>
            @endif
        </div>

        <div class="sidecar-visual">
            <!-- Era cover visual -->
            <div class="visual-layer @if($loop->first) active @endif" id="v-{{ $era->slug }}-cover">
                <div class="visual-layer-bg {{ $era->bg_class }}"></div>
                <div class="visual-caption">
                    <span class="visual-caption-era">{{ $era->caption_era }}</span>
                    <span class="visual-caption-label">{{ $era->caption_label }}</span>
                </div>
            </div>

            <!-- Topic visuals -->
            @foreach($era->topics as $topic)
            <div class="visual-layer" id="v-topic-{{ $topic->id }}">
                <div class="visual-layer-bg {{ $topic->bg_class }}"></div>
                @if($topic->image)
                    <div class="visual-layer-img" style="background-image: url('{{ Storage::url($topic->image) }}')"></div>
                @endif
                <div class="visual-caption">
                    <span class="visual-caption-era">{{ $topic->caption_era }}</span>
                    <span class="visual-caption-label">{{ $topic->caption_label }}</span>
                </div>
            </div>
            @endforeach

            @if(count($eraMarkers) > 0)
            <!-- Map visual -->
            <div class="visual-layer" id="v-{{ $era->slug }}-map">
                <div class="visual-layer-map" data-map-markers='@json($eraMarkers)' data-map-id="map-{{ $era->slug }}"></div>
                <div class="visual-caption">
                    <span class="visual-caption-era">{{ $era->caption_era }}</span>
                    <span class="visual-caption-label">Geography of repression</span>
                </div>
            </div>
            @endif
        </div>
    </section>
    @endforeach

    <!-- CTA -->
    <section class="history-cta">
        <h2>The Fight Continues</h2>
        <p>Today, political prisoners remain incarcerated across the United States. Learn about their cases and how you can take action.</p>
        <a href="/database" class="history-cta-btn">View Prisoner Profiles</a>
    </section>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
// Lazy Leaflet map initializer — only spin up a map when its layer first
// becomes active (saves the four-or-five maps on the page from all
// instantiating on first paint).
const initMap = (mapEl) => {
    if (mapEl.dataset.initialized === 'true') return;
    mapEl.dataset.initialized = 'true';

    let markers;
    try { markers = JSON.parse(mapEl.dataset.mapMarkers || '[]'); }
    catch (e) { markers = []; }
    if (!markers.length) return;

    const map = L.map(mapEl, {
        zoomControl: false,
        attributionControl: true,
        scrollWheelZoom: false,
        dragging: true,
    }).setView([39.5, -98.35], 4);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 8,
    }).addTo(map);

    const bounds = [];
    const dotIcon = L.divIcon({
        className: '',
        html: '<div style="width:14px;height:14px;border-radius:50%;background:#5660fe;border:2px solid #fff;box-shadow:0 0 12px rgba(86,96,254,0.7);"></div>',
        iconSize: [14, 14],
        iconAnchor: [7, 7],
    });
    markers.forEach(m => {
        L.marker([m.lat, m.lng], { icon: dotIcon })
            .bindPopup(
                `<div style="font-family: 'Roboto', sans-serif;">
                    <div style="font-weight:800; color:#0a0a0a; margin-bottom:4px;">${m.title}</div>
                    <div style="font-size:12px; color:#5660fe; margin-bottom:4px;">${m.date || ''}</div>
                    <div style="font-size:13px; color:rgba(0,0,0,0.7);">${m.label}</div>
                </div>`,
                { maxWidth: 280 }
            )
            .addTo(map);
        bounds.push([m.lat, m.lng]);
    });

    if (bounds.length === 1) {
        map.setView(bounds[0], 5);
    } else {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 6 });
    }
};

// Scrollytelling Engine
document.querySelectorAll('.sidecar').forEach(sidecar => {
    const steps = sidecar.querySelectorAll('.step');
    const layers = sidecar.querySelectorAll('.visual-layer');
    const dots = sidecar.querySelectorAll('.sidecar-dot');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const targetId = entry.target.dataset.visual;
                const stepIdx = entry.target.dataset.stepIndex;
                steps.forEach(s => s.classList.remove('active'));
                entry.target.classList.add('active');

                // Update progress dots for this sidecar
                dots.forEach(d => {
                    d.classList.toggle('active', d.dataset.stepIndex === stepIdx);
                });

                layers.forEach(l => {
                    if (l.id === targetId) {
                        if (!l.classList.contains('active')) l.classList.add('active');
                        // Lazy-init Leaflet maps when their layer becomes active
                        const mapEl = l.querySelector('.visual-layer-map');
                        if (mapEl) initMap(mapEl);
                    } else {
                        l.classList.remove('active');
                    }
                });
            }
        });
    }, { root: null, rootMargin: '-30% 0px -30% 0px', threshold: 0 });

    steps.forEach(step => observer.observe(step));
});

// Era divider entrance animations (fade + zoom on scroll-into-view)
const dividerObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('in-view');
        } else if (entry.intersectionRatio === 0) {
            entry.target.classList.remove('in-view');
        }
    });
}, { threshold: [0, 0.2, 0.5, 0.8] });

document.querySelectorAll('.era-divider').forEach(d => dividerObserver.observe(d));

// Era Nav Tracking
const sidecars = document.querySelectorAll('.sidecar');
const eraLinks = document.querySelectorAll('.era-nav a');

const eraObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const id = entry.target.id;
            eraLinks.forEach(link => {
                link.classList.toggle('active', link.getAttribute('href') === '#' + id);
            });
        }
    });
}, { threshold: 0.1, rootMargin: '-50px 0px -50% 0px' });

sidecars.forEach(s => eraObserver.observe(s));

// Smooth scroll for nav
eraLinks.forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        document.querySelector(link.getAttribute('href'))?.scrollIntoView({ behavior: 'smooth' });
    });
});

// Progress Bar
window.addEventListener('scroll', () => {
    const h = document.documentElement.scrollHeight - window.innerHeight;
    document.getElementById('progressBar').style.width = (window.scrollY / h * 100) + '%';
});
</script>
@endsection
