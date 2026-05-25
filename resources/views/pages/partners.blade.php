@extends('app')

@section('head')
<style>
    .pp { overflow: hidden; }

    /* Hero */
    .pp-hero {
        position: relative;
        min-height: 560px;
        display: flex;
        align-items: flex-end;
        background: linear-gradient(135deg, #0a0a1a 0%, #1a1040 50%, #5660fe 100%);
        overflow: hidden;
    }
    .pp-hero-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(0deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.2) 50%, transparent 100%);
    }
    .pp-hero-back {
        position: absolute;
        top: 24px;
        left: 24px;
        color: rgba(255,255,255,0.7);
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        z-index: 2;
        transition: color 0.2s;
    }
    .pp-hero-back:hover { color: #fff; }
    .pp-hero-content {
        position: relative;
        z-index: 2;
        max-width: 700px;
        padding: 0 48px 64px;
    }
    .pp-hero-title {
        font-size: 4rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.05;
        margin-bottom: 24px;
        text-transform: uppercase;
    }
    .pp-hero-sub {
        font-size: 1.1rem;
        color: rgba(255,255,255,0.8);
        line-height: 1.7;
        margin-bottom: 32px;
        max-width: 550px;
    }
    .pp-hero-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: #5660fe;
        color: #fff;
        text-decoration: none;
        transition: background 0.2s;
    }
    .pp-hero-btn:hover { background: #4850e6; }

    /* Intro section */
    .pp-intro {
        max-width: 1200px;
        margin: 0 auto;
        padding: 100px 48px;
        display: flex;
        gap: 64px;
        align-items: center;
    }
    .pp-intro-text { flex: 1; }
    .pp-intro-label {
        font-size: 14px;
        font-weight: 700;
        color: #5660fe;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin-bottom: 16px;
    }
    .pp-intro-title {
        font-size: 2.8rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.1;
        text-transform: uppercase;
        margin-bottom: 24px;
    }
    .pp-intro-body {
        font-size: 16px;
        color: rgba(255,255,255,0.65);
        line-height: 1.8;
    }
    .pp-intro-body p { margin-bottom: 1.25em; }
    .pp-intro-image {
        flex: 0 0 420px;
        height: 420px;
        border-radius: 50%;
        overflow: hidden;
        border: 6px solid #5660fe;
        box-shadow: 0 20px 60px rgba(86,96,254,0.2);
    }
    .pp-intro-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .pp-intro-image-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #1a1040 0%, #5660fe 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Partners list */
    .pp-list {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 48px 100px;
    }
    .pp-list-title {
        font-size: 2.5rem;
        font-weight: 900;
        color: #fff;
        text-transform: uppercase;
        margin-bottom: 48px;
    }
    .pp-category-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: rgba(255,255,255,0.4);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        margin: 48px 0 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .pp-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 24px;
    }
    .pp-card {
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: 8px;
        padding: 32px;
        transition: border-color 0.3s, background 0.3s;
        text-decoration: none;
        display: flex;
        flex-direction: column;
    }
    .pp-card:hover {
        border-color: rgba(86,96,254,0.3);
        background: rgba(255,255,255,0.02);
    }
    .pp-card-logo {
        width: 64px;
        height: 64px;
        object-fit: contain;
        margin-bottom: 20px;
    }
    .pp-card-initials {
        width: 64px;
        height: 64px;
        background: rgba(86,96,254,0.15);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        font-weight: 900;
        color: #5660fe;
        margin-bottom: 20px;
    }
    .pp-card-name {
        font-size: 1.15rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 8px;
    }
    .pp-card-desc {
        font-size: 14px;
        color: rgba(255,255,255,0.5);
        line-height: 1.6;
        flex: 1;
    }
    .pp-card-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 16px;
        font-size: 13px;
        font-weight: 700;
        color: #5660fe;
        text-decoration: none;
    }
    .pp-card-link:hover { color: #7880ff; }

    /* Become a partner CTA */
    .pp-cta {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 48px 100px;
        display: flex;
        gap: 64px;
        align-items: center;
    }
    .pp-cta-image {
        flex: 0 0 380px;
        height: 380px;
        border-radius: 50%;
        overflow: hidden;
        border: 6px solid rgba(255,255,255,0.1);
    }
    .pp-cta-image-placeholder {
        width: 100%;
        height: 100%;
        background: linear-gradient(135deg, #111 0%, #1a1040 100%);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pp-cta-text { flex: 1; }
    .pp-cta-title {
        font-size: 2.5rem;
        font-weight: 900;
        color: #fff;
        text-transform: uppercase;
        margin-bottom: 20px;
    }
    .pp-cta-body {
        font-size: 16px;
        color: rgba(255,255,255,0.6);
        line-height: 1.7;
        margin-bottom: 32px;
    }

    /* Contact bar */
    .pp-contact {
        max-width: 1200px;
        margin: 0 auto 80px;
        padding: 32px 48px;
        background: #5660fe;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }
    .pp-contact-text {
        font-size: 16px;
        font-weight: 700;
        color: #fff;
    }
    .pp-contact-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        background: rgba(255,255,255,0.15);
        border: 1px solid rgba(255,255,255,0.3);
        color: #fff;
        text-decoration: none;
        transition: background 0.2s;
        flex-shrink: 0;
    }
    .pp-contact-btn:hover { background: rgba(255,255,255,0.25); }

    @media (max-width: 768px) {
        .pp-hero { min-height: 400px; }
        .pp-hero-title { font-size: 2.5rem; }
        .pp-hero-content { padding: 0 24px 48px; }
        .pp-intro { flex-direction: column; padding: 60px 24px; }
        .pp-intro-image { flex: auto; width: 280px; height: 280px; }
        .pp-intro-title { font-size: 2rem; }
        .pp-list { padding: 0 24px 60px; }
        .pp-list-title { font-size: 1.8rem; }
        .pp-grid { grid-template-columns: 1fr; }
        .pp-cta { flex-direction: column; padding: 0 24px 60px; }
        .pp-cta-image { flex: auto; width: 250px; height: 250px; }
        .pp-cta-title { font-size: 2rem; }
        .pp-hero-content, .pp-intro, .pp-list, .pp-cta, .pp-contact { padding-left: 16px !important; padding-right: 16px !important; }
    }
    @media (max-width: 420px) {
        .pp-hero-title { font-size: 1.8rem; }
        .pp-intro-image { width: 220px; height: 220px; }
        .pp-intro-title { font-size: 1.4rem; }
        .pp-list-title { font-size: 1.3rem; }
        .pp-cta-title { font-size: 1.4rem; }
        .pp-cta-image { width: 200px; height: 200px; }
        .pp-contact { flex-direction: column; text-align: center; margin: 0 24px 60px; padding: 24px; }
    }
</style>
@endsection

@section('body')
<div class="pp">

    {{-- Hero --}}
    <div class="pp-hero">
        <div class="pp-hero-overlay"></div>
        <a href="/about" class="pp-hero-back">&larr; About</a>
        <div class="pp-hero-content">
            <h1 class="pp-hero-title">Partners in the Movement</h1>
            <p class="pp-hero-sub">Building on the principle of coalition and solidarity, NPPC partners with organizations committed to justice, civil liberties, and human rights to advance our shared mission.</p>
            <a href="/contact" class="pp-hero-btn">Partner With Us &rarr;</a>
        </div>
    </div>

    {{-- Intro --}}
    <div class="pp-intro">
        <div class="pp-intro-text">
            <div class="pp-intro-label">Our Coalition</div>
            <h2 class="pp-intro-title">A Uniting Force</h2>
            <div class="pp-intro-body">
                <p>We remain a uniting force in the movement, bringing together leading voices and organizations to collaborate on initiatives that bring us closer to a more just future.</p>
                <p>Our partners share our belief that political imprisonment is a fundamental threat to democracy. Through our campaigns, events, and research, partners aid us in our mission to document, advocate, and support those who have been unjustly detained.</p>
            </div>
        </div>
        <div class="pp-intro-image">
            <div class="pp-intro-image-placeholder">
                <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="rgba(255,255,255,0.15)" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
            </div>
        </div>
    </div>

    {{-- Partners List --}}
    <div class="pp-list">
        <h2 class="pp-list-title">Our Partners</h2>

        @php
            $categorized = $partners->groupBy(fn ($p) => $p->category ?: 'Partners');
        @endphp

        @foreach($categorized as $category => $group)
            @if($categorized->count() > 1)
                <h3 class="pp-category-title">{{ $category }}</h3>
            @endif
            <div class="pp-grid">
                @foreach($group as $partner)
                    <div class="pp-card">
                        @if($partner->logo)
                            <img src="{{ Storage::url($partner->logo) }}" alt="{{ $partner->name }}" class="pp-card-logo">
                        @else
                            <div class="pp-card-initials">{{ strtoupper(substr($partner->name, 0, 2)) }}</div>
                        @endif
                        <div class="pp-card-name">{{ $partner->name }}</div>
                        @if($partner->description)
                            <div class="pp-card-desc">{{ $partner->description }}</div>
                        @endif
                        @if($partner->url)
                            <a href="{{ $partner->url }}" target="_blank" class="pp-card-link">Visit website &rarr;</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>

    {{-- Become a Partner CTA --}}
    <div class="pp-cta">
        <div class="pp-cta-image">
            <img src="/images/become-a-partner.jpg" alt="Running Down The Walls — Philadelphia ABC, 2020" style="width:100%;height:100%;object-fit:cover;">
        </div>
        <div class="pp-cta-text">
            <h2 class="pp-cta-title">Become a Partner</h2>
            <p class="pp-cta-body">NPPC aligns with organizations that demonstrate a commitment to justice, civil liberties, and human rights. Through our partnerships, we amplify the voices of political prisoners and build a stronger movement for change.</p>
            <p class="pp-cta-body">Does this sound like your organization?</p>
            <a href="/contact" class="pp-hero-btn">Partner With Us &rarr;</a>
        </div>
    </div>

    {{-- Contact bar --}}
    <div class="pp-contact">
        <div class="pp-contact-text">Interested in partnering with NPPC? Please email info@nationalpoliticalprisonercoalition.org.</div>
        <a href="mailto:info@nationalpoliticalprisonercoalition.org" class="pp-contact-btn">Email Us &rarr;</a>
    </div>

</div>
@endsection
