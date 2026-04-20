@extends('app')

@section('head')
<style>
    .lm-serif { font-family: Georgia, 'Times New Roman', Times, serif; }

    /* Hero */
    .lm-hero { position: relative; min-height: 560px; overflow: hidden; display: flex; align-items: flex-end; }
    .lm-hero-photos { position: absolute; inset: 0; display: flex; clip-path: inset(0 100% 0 0); animation: revealSweep 3s ease-out forwards; }
    .lm-hero-photos > div { flex: 1; background-size: cover; background-position: center top; filter: grayscale(30%); }
    .lm-hero-overlay { position: absolute; inset: 0; background: linear-gradient(0deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 40%, rgba(0,0,0,0.25) 100%); }
    .lm-hero-content { position: relative; z-index: 2; max-width: 800px; margin: 0 auto; text-align: center; padding: 0 24px 64px; }
    .lm-hero-title { font-size: 3rem; font-weight: 700; color: #fff; line-height: 1.2; margin-bottom: 24px; }
    .lm-hero-title .lm-line { display: block; overflow: hidden; }
    .lm-hero-title .lm-line > span { display: inline-block; clip-path: inset(0 100% 0 0); animation: revealSweep 1.5s ease-out forwards; }
    .lm-hero-title .lm-line:nth-child(1) > span { animation-delay: 0.4s; }
    .lm-hero-title .lm-line:nth-child(2) > span { animation-delay: 1.1s; }
    .lm-hero-title .lm-line:nth-child(3) > span { animation-delay: 1.8s; }
    .lm-hero-sub { font-size: 1.1rem; color: rgba(255,255,255,0.8); line-height: 1.6; margin-bottom: 32px; opacity: 0; animation: fadeInUp 1s ease-out 2.8s forwards; }
    .lm-hero-btns { display: flex; justify-content: center; gap: 16px; flex-wrap: wrap; opacity: 0; animation: fadeInUp 1s ease-out 3.2s forwards; }
    .lm-hero-btn { display: inline-flex; align-items: center; gap: 8px; padding: 14px 28px; font-size: 14px; font-weight: 700; border: 1px solid rgba(255,255,255,0.4); color: #fff; text-decoration: none; background: rgba(0,0,0,0.3); backdrop-filter: blur(4px); transition: all 0.2s; }
    .lm-hero-btn:hover { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.6); }
    .lm-hero-btn svg { width: 18px; height: 18px; }

    @keyframes revealSweep {
        from { clip-path: inset(0 100% 0 0); }
        to { clip-path: inset(0 0 0 0); }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Editorial sections */
    .lm-section { max-width: 800px; margin: 0 auto; padding: 80px 24px; }
    .lm-section-title { font-size: 2.8rem; font-weight: 700; color: #fff; line-height: 1.2; margin-bottom: 32px; text-align: center; }
    .lm-section p { font-size: 17px; color: rgba(255,255,255,0.75); line-height: 1.85; margin-bottom: 1.5em; }
    .lm-section p:last-child { margin-bottom: 0; }

    /* Full-width image */
    .lm-fullimg { width: 100%; height: 480px; background-size: cover; background-position: center; }

    /* Stats bar */
    .lm-stats { max-width: 1000px; margin: 0 auto; padding: 64px 24px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; text-align: center; }
    .lm-stat-num { font-size: 3.5rem; font-weight: 900; color: #5660fe; line-height: 1; margin-bottom: 8px; }
    .lm-stat-label { font-size: 14px; color: rgba(255,255,255,0.5); line-height: 1.5; }
    .lm-divider { height: 1px; background: rgba(255,255,255,0.08); max-width: 1000px; margin: 0 auto; }

    /* Featured prisoners */
    .lm-cases-title { font-size: 2.8rem; font-weight: 700; color: #fff; line-height: 1.2; text-align: center; padding: 80px 24px 48px; }
    .lm-cases-grid { max-width: 1100px; margin: 0 auto; padding: 0 24px 80px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .lm-case-card { border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; overflow: hidden; text-decoration: none; transition: border-color 0.25s, transform 0.25s; display: flex; flex-direction: column; }
    .lm-case-card:hover { border-color: rgba(86,96,254,0.35); transform: translateY(-2px); }
    .lm-case-photo { height: 280px; background-size: cover; background-position: center top; }
    .lm-case-photo-placeholder { height: 280px; background: linear-gradient(135deg, #111 0%, #1a1a2e 100%); display: flex; align-items: center; justify-content: center; }
    .lm-case-info { padding: 24px; flex: 1; display: flex; flex-direction: column; }
    .lm-case-name { font-size: 1.25rem; font-weight: 800; color: #fff; margin-bottom: 8px; }
    .lm-case-meta { font-size: 13px; color: rgba(255,255,255,0.45); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px; }
    .lm-case-status { display: inline-block; padding: 3px 10px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; border-radius: 3px; margin-bottom: 12px; }
    .lm-case-status-custody { background: rgba(239,68,68,0.15); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
    .lm-case-status-released { background: rgba(34,197,94,0.15); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
    .lm-case-status-exile { background: rgba(234,179,8,0.15); color: #eab308; border: 1px solid rgba(234,179,8,0.3); }
    .lm-case-desc { font-size: 14px; color: rgba(255,255,255,0.6); line-height: 1.7; flex: 1; }

    /* CTA */
    .lm-cta { text-align: center; padding: 80px 24px; max-width: 700px; margin: 0 auto; }
    .lm-cta-title { font-size: 2.5rem; font-weight: 700; color: #fff; margin-bottom: 20px; }
    .lm-cta-text { font-size: 17px; color: rgba(255,255,255,0.6); line-height: 1.75; margin-bottom: 36px; }
    .lm-cta-btn { display: inline-block; background: #5660fe; color: #fff; padding: 16px 40px; font-size: 15px; font-weight: 700; text-decoration: none; text-transform: uppercase; letter-spacing: 0.06em; transition: background 0.2s; }
    .lm-cta-btn:hover { background: #4850e6; }

    @@media (max-width: 900px) {
        .lm-hero { min-height: 440px; }
        .lm-hero-title { font-size: 2.2rem; }
        .lm-hero-photos > div:nth-child(n+4) { display: none; }
        .lm-stats { grid-template-columns: repeat(2, 1fr); }
        .lm-cases-grid { grid-template-columns: 1fr; max-width: 480px; }
    }
    @@media (max-width: 640px) {
        .lm-hero-title { font-size: 1.8rem; }
        .lm-hero-photos > div:nth-child(n+3) { display: none; }
        .lm-section-title { font-size: 2rem; }
        .lm-cases-title { font-size: 2rem; }
        .lm-stats { grid-template-columns: 1fr 1fr; gap: 24px; }
        .lm-fullimg { height: 280px; }
    }
</style>
@endsection

@section('body')
@php
    $heroPrisoners = \App\Models\Prisoner::whereNotNull('photo')->where('photo','!=','')->inRandomOrder()->limit(5)->get();
    $featuredPrisoners = \App\Models\Prisoner::with('cases')
        ->whereNotNull('description')
        ->where('description','!=','')
        ->where(function ($q) {
            $q->where('in_custody', true)
              ->orWhere('in_exile', true)
              ->orWhere('currently_in_exile', true)
              ->orWhere('awaiting_trial', true);
        })
        ->orderByDesc('created_at')
        ->limit(6)
        ->get();
    $prisonerCount = \App\Models\Prisoner::count();
    $inCustodyCount = \App\Models\Prisoner::where('in_custody', true)->count();
    $releasedCount = \App\Models\Prisoner::where('released', true)->count();
    $stateCount = \App\Models\Prisoner::whereNotNull('state')->where('state','!=','')->distinct()->count('state');
@endphp

{{-- ==================== HERO ==================== --}}
<div class="lm-hero">
    <div class="lm-hero-photos">
        @foreach($heroPrisoners as $p)
            <div style="background-image: url('{{ asset('storage/'.$p->photo) }}');"></div>
        @endforeach
        @for($i = $heroPrisoners->count(); $i < 5; $i++)
            <div style="background: linear-gradient(135deg, #0a0a1a 0%, #1a1040 100%);"></div>
        @endfor
    </div>
    <div class="lm-hero-overlay"></div>
    <div class="lm-hero-content">
        <h1 class="lm-hero-title lm-serif">
            <span class="lm-line"><span>Documenting Political</span></span>
            <span class="lm-line"><span>Imprisonment in the</span></span>
            <span class="lm-line"><span>United States</span></span>
        </h1>
        <p class="lm-hero-sub">Identifying and supporting those who have been imprisoned for their political beliefs, activism, and dissent across the United States.</p>
        <div class="lm-hero-btns">
            <a href="/database" class="lm-hero-btn">
                Explore the Database
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="#cases" class="lm-hero-btn">
                Political Prisoners
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 14l-7 7-7-7"/></svg>
            </a>
        </div>
    </div>
</div>

{{-- ==================== INTRO ==================== --}}
<div class="lm-section">
    <p>The United States has a long and often unacknowledged history of imprisoning individuals for their political beliefs, activism, and associations. From the anti-war movements of the 1960s and 70s to the environmental and digital rights struggles of today, the government has repeatedly used the criminal justice system to silence dissent and punish those who challenge the status quo.</p>
    <p>The National Political Prisoner Coalition works to document these cases, provide support to those who remain imprisoned, and educate the public about the ongoing reality of political repression in America. Our comprehensive database tracks hundreds of cases spanning decades, revealing patterns of government overreach that persist to this day.</p>
</div>

{{-- ==================== STATS ==================== --}}
<div class="lm-divider"></div>
<div class="lm-stats">
    <div>
        <div class="lm-stat-num">{{ $prisonerCount }}+</div>
        <div class="lm-stat-label">Political prisoners documented in our database</div>
    </div>
    <div>
        <div class="lm-stat-num">{{ $inCustodyCount }}</div>
        <div class="lm-stat-label">Currently in custody or exile</div>
    </div>
    <div>
        <div class="lm-stat-num">{{ $releasedCount }}</div>
        <div class="lm-stat-label">Released after years of imprisonment</div>
    </div>
    <div>
        <div class="lm-stat-num">{{ $stateCount }}+</div>
        <div class="lm-stat-label">States with documented cases</div>
    </div>
</div>
<div class="lm-divider"></div>

{{-- ==================== AN ARRAY OF TARGETS ==================== --}}
<div class="lm-section">
    <h2 class="lm-section-title lm-serif">An Array of Targets and Tactics</h2>
    <p>The people who become political prisoners in America are as diverse as the causes they champion. They include civil rights organizers, anti-war activists, environmental defenders, journalists, whistleblowers, Indigenous sovereignty advocates, and immigrant rights leaders. What unites them is a willingness to challenge powerful institutions — and the price they have paid for doing so.</p>
    <p>In reprisal for their activism, they have been charged with offenses ranging from conspiracy and sedition to immigration violations and computer fraud. Once in custody, they frequently endure solitary confinement, denial of medical care, transfer to facilities far from family, and restrictions on communication. Many serve sentences vastly disproportionate to their actions, while others are held without charge or on pretextual grounds designed to silence their advocacy.</p>
    <p>Even after release, many face ongoing surveillance, travel restrictions, probation conditions that limit their speech, and the lasting stigma of a criminal record — all of which serve as deterrents to future activism.</p>
</div>

{{-- ==================== FULL-WIDTH IMAGE ==================== --}}
<div class="lm-fullimg" style="background-image: url('/images/stop-jailing-truth-tellers.webp'); background-position: center;"></div>

{{-- ==================== A NATIONAL PROBLEM ==================== --}}
<div class="lm-section">
    <h2 class="lm-section-title lm-serif">A National Problem</h2>
    <p>Political imprisonment is not a relic of American history — it is an ongoing reality. In recent years, we have seen journalists detained for covering immigration enforcement, activists imprisoned for protesting pipeline construction, whistleblowers sentenced for exposing government surveillance, and citizens jailed for social media posts critical of public officials.</p>
    <p>These cases are not isolated incidents. They reflect a systemic pattern in which the criminal justice system is wielded as a tool of political repression, targeting those who expose wrongdoing, organize communities, or simply exercise their constitutional rights to free speech and peaceful assembly.</p>
    <p>By documenting these cases and telling these stories, NPPC aims to hold the government accountable, support those who have been unjustly imprisoned, and build a movement for justice that cannot be silenced.</p>
</div>

{{-- ==================== A NEW INITIATIVE ==================== --}}
<div class="lm-divider"></div>
<div class="lm-section">
    <h2 class="lm-section-title lm-serif">Our Initiative</h2>
    <p>NPPC seeks to both highlight and combat political repression, in part by emphasizing its human toll. The experiences of the individuals profiled here illustrate the significant pressures and harms that activists, journalists, and advocates face in reprisal for their work.</p>
    <p>These individuals represent only a fraction of the many people who have been imprisoned for their beliefs and activism across the United States. Mapping the scale and scope of such repression, and the stories behind the numbers, is essential to holding the government accountable and securing justice for all those who have been unjustly detained.</p>
</div>

{{-- ==================== FEATURED CASES ==================== --}}
<div id="cases">
    <h2 class="lm-cases-title lm-serif">Emblematic Cases of Political Prisoners</h2>
    <div class="lm-cases-grid">
        @foreach($featuredPrisoners as $prisoner)
            <a href="/prisoner/{{ $prisoner->slug ?? $prisoner->id }}" class="lm-case-card">
                @if($prisoner->photo)
                    <div class="lm-case-photo" style="background-image: url('{{ asset('storage/'.$prisoner->photo) }}');"></div>
                @else
                    <div class="lm-case-photo-placeholder">
                        <img src="/images/no-image-available.png" alt="No image available" style="width:60%; height:auto; opacity:0.8;">
                    </div>
                @endif
                <div class="lm-case-info">
                    <div class="lm-case-name">{{ $prisoner->name }}</div>
                    @if($prisoner->state)
                        <div class="lm-case-meta">{{ $prisoner->state }}</div>
                    @endif
                    @if($prisoner->in_custody)
                        <span class="lm-case-status lm-case-status-custody">In Custody</span>
                    @elseif($prisoner->currently_in_exile || $prisoner->in_exile)
                        <span class="lm-case-status lm-case-status-exile">In Exile</span>
                    @elseif($prisoner->released)
                        <span class="lm-case-status lm-case-status-released">Released</span>
                    @endif
                    <div class="lm-case-desc">{{ \Illuminate\Support\Str::limit(strip_tags($prisoner->description), 160) }}</div>
                </div>
            </a>
        @endforeach
    </div>
</div>

{{-- ==================== CTA ==================== --}}
<div class="lm-divider"></div>
<div class="lm-cta">
    <h2 class="lm-cta-title lm-serif">Take Action</h2>
    <p class="lm-cta-text">Every voice matters in the fight against political imprisonment. Explore the database, write to a prisoner, sign a petition, or support our work with a donation.</p>
    <a href="/get-involved" class="lm-cta-btn">Get Involved</a>
</div>

@endsection
