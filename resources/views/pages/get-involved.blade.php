@extends('app')

@section('head')
<style>
    /* Container & Layout */
    .gi-page { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .gi-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 72px 0; }

    /* Hero */
    .gi-hero { padding: 80px 0 0; max-width: 800px; }
    .gi-hero-label { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: #5660fe; margin-bottom: 16px; }
    .gi-hero-title { font-size: 5rem; font-weight: 900; color: #fff; line-height: 1.02; margin-bottom: 28px; }
    .gi-hero-sub { font-size: 1.35rem; font-weight: 600; color: rgba(255,255,255,0.6); line-height: 1.65; }

    /* Action Cards */
    .gi-actions { display: grid; grid-template-columns: repeat(2, 1fr); gap: 24px; }
    .gi-card { position: relative; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 40px; display: flex; flex-direction: column; justify-content: space-between; min-height: 340px; text-decoration: none; transition: border-color 0.25s, background 0.25s; overflow: hidden; }
    .gi-card:hover { border-color: rgba(86,96,254,0.35); background: rgba(255,255,255,0.02); }
    .gi-card-icon { width: 56px; height: 56px; background: rgba(86,96,254,0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 28px; }
    .gi-card-icon svg { width: 28px; height: 28px; }
    .gi-card-title { font-size: 1.75rem; font-weight: 900; color: #fff; margin-bottom: 12px; line-height: 1.15; }
    .gi-card-desc { font-size: 15px; color: rgba(255,255,255,0.55); line-height: 1.7; margin-bottom: 28px; }
    .gi-card-link { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 700; color: #5660fe; text-decoration: none; transition: gap 0.2s; }
    .gi-card:hover .gi-card-link { gap: 12px; }
    .gi-card-link svg { width: 16px; height: 16px; }

    /* Featured / Donate */
    .gi-featured { display: flex; gap: 0; border-radius: 12px; overflow: hidden; min-height: 420px; }
    .gi-featured-left { flex: 1; background: linear-gradient(135deg, #1a1040 0%, #2a1860 40%, #5660fe 100%); padding: 56px; display: flex; flex-direction: column; justify-content: center; }
    .gi-featured-label { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: rgba(255,255,255,0.5); margin-bottom: 16px; }
    .gi-featured-title { font-size: 2.8rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 20px; }
    .gi-featured-text { font-size: 17px; color: rgba(255,255,255,0.7); line-height: 1.7; margin-bottom: 36px; max-width: 460px; }
    .gi-featured-btn { display: inline-block; background: #fff; color: #111; padding: 16px 36px; font-size: 15px; font-weight: 700; text-decoration: none; text-transform: uppercase; letter-spacing: 0.04em; transition: background 0.2s; }
    .gi-featured-btn:hover { background: rgba(255,255,255,0.9); }
    .gi-featured-right { flex: 0 0 420px; position: relative; background: #0c0c1e; }
    .gi-featured-img { width: 100%; height: 100%; object-fit: cover; opacity: 0.7; }
    .gi-featured-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: linear-gradient(180deg, #0c0c1e 0%, #1a1040 100%); }

    /* Impact Stats */
    .gi-impact { text-align: center; padding: 20px 0; }
    .gi-impact-title { font-size: 2.5rem; font-weight: 900; color: #fff; margin-bottom: 12px; }
    .gi-impact-sub { font-size: 17px; color: rgba(255,255,255,0.5); margin-bottom: 56px; max-width: 560px; margin-left: auto; margin-right: auto; line-height: 1.65; }
    .gi-impact-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 32px; }
    .gi-impact-stat { }
    .gi-impact-num { font-size: 3.5rem; font-weight: 900; color: #5660fe; line-height: 1; margin-bottom: 10px; }
    .gi-impact-label { font-size: 14px; color: rgba(255,255,255,0.5); line-height: 1.5; }

    /* Ways to Help */
    .gi-ways-title { font-size: 2.5rem; font-weight: 900; color: #fff; margin-bottom: 48px; }
    .gi-ways { display: grid; grid-template-columns: repeat(3, 1fr); gap: 0; border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; overflow: hidden; }
    .gi-way { padding: 40px 32px; border-right: 1px solid rgba(255,255,255,0.08); position: relative; }
    .gi-way:last-child { border-right: none; }
    .gi-way-num { font-size: 42px; font-weight: 900; color: rgba(86,96,254,0.15); margin-bottom: 20px; line-height: 1; }
    .gi-way-name { font-size: 18px; font-weight: 800; color: #fff; margin-bottom: 10px; }
    .gi-way-desc { font-size: 14px; color: rgba(255,255,255,0.5); line-height: 1.7; }

    /* Testimonial */
    .gi-testimonial { text-align: center; padding: 20px 0; max-width: 800px; margin: 0 auto; }
    .gi-testimonial-quote { font-size: 1.75rem; font-weight: 700; color: rgba(255,255,255,0.85); line-height: 1.5; font-style: italic; margin-bottom: 28px; }
    .gi-testimonial-quote::before { content: '\201C'; color: #5660fe; }
    .gi-testimonial-quote::after { content: '\201D'; color: #5660fe; }
    .gi-testimonial-author { font-size: 15px; color: rgba(255,255,255,0.45); }
    .gi-testimonial-author strong { color: rgba(255,255,255,0.7); font-weight: 700; }

    /* Newsletter */
    .gi-newsletter { border: 1px solid rgba(255,255,255,0.08); border-radius: 12px; padding: 56px; display: flex; gap: 48px; align-items: center; }
    .gi-newsletter-left { flex: 1; }
    .gi-newsletter-title { font-size: 2rem; font-weight: 900; color: #fff; margin-bottom: 12px; }
    .gi-newsletter-text { font-size: 15px; color: rgba(255,255,255,0.5); line-height: 1.7; }
    .gi-newsletter-form { flex: 0 0 380px; display: flex; gap: 8px; }
    .gi-newsletter-input { flex: 1; background: transparent; border: 1px solid rgba(255,255,255,0.25); color: #fff; padding: 14px 16px; font-size: 15px; outline: none; transition: border-color 0.2s; }
    .gi-newsletter-input:focus { border-color: #5660fe; }
    .gi-newsletter-input::placeholder { color: rgba(255,255,255,0.3); }
    .gi-newsletter-btn { background: #5660fe; color: #fff; border: none; padding: 14px 28px; font-size: 14px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; cursor: pointer; white-space: nowrap; transition: background 0.2s; }
    .gi-newsletter-btn:hover { background: #4850e6; }

    /* CTA Bottom */
    .gi-bottom { padding: 0 0 80px; }
    .gi-bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .gi-bottom-card { position: relative; display: block; border-radius: 12px; overflow: hidden; min-height: 280px; text-decoration: none; border: 1px solid rgba(255,255,255,0.08); transition: border-color 0.25s; }
    .gi-bottom-card:hover { border-color: rgba(86,96,254,0.35); }
    .gi-bottom-card-inner { position: relative; padding: 40px; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; }
    .gi-bottom-card-title { font-size: 2rem; font-weight: 900; color: #fff; line-height: 1.1; margin-bottom: 12px; }
    .gi-bottom-card-desc { font-size: 15px; color: rgba(255,255,255,0.55); line-height: 1.65; max-width: 340px; margin-bottom: 20px; }
    .gi-bottom-card-arrow { width: 44px; height: 44px; background: #5660fe; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
    .gi-bottom-card:hover .gi-bottom-card-arrow { background: #4850e6; }

    /* Responsive */
    @@media (max-width: 900px) {
        .gi-hero-title { font-size: 3.2rem; }
        .gi-actions { grid-template-columns: 1fr; }
        .gi-featured { flex-direction: column; }
        .gi-featured-right { flex: auto; min-height: 240px; }
        .gi-featured-left { padding: 40px 32px; }
        .gi-impact-grid { grid-template-columns: repeat(2, 1fr); }
        .gi-ways { grid-template-columns: 1fr; }
        .gi-way { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .gi-way:last-child { border-bottom: none; }
        .gi-newsletter { flex-direction: column; }
        .gi-newsletter-form { flex: auto; width: 100%; }
        .gi-bottom-grid { grid-template-columns: 1fr; }
    }
    @@media (max-width: 640px) {
        .gi-hero-title { font-size: 2.5rem; }
        .gi-featured-title { font-size: 2rem; }
        .gi-impact-grid { grid-template-columns: 1fr 1fr; gap: 24px; }
        .gi-newsletter-form { flex-direction: column; }
    }
</style>
@endsection

@section('body')
<div class="gi-page">

    {{-- ==================== HERO ==================== --}}
    <div class="gi-hero">
        <div class="gi-hero-label">Get Involved</div>
        <h1 class="gi-hero-title">Your Voice Can Free a Prisoner</h1>
        <p class="gi-hero-sub">Political prisoners need more than thoughts and prayers. They need people willing to write letters, make calls, spread awareness, donate resources, and show up. Here's how you can help.</p>
    </div>
</div>

{{-- ==================== IMPACT STATS (full-width) ==================== --}}
@include('sections.impact-stats')

<div class="gi-page">
    <div class="gi-divider"></div>

    {{-- ==================== ACTION CARDS ==================== --}}
    <div class="gi-actions">

        {{-- Volunteer --}}
        <a href="/volunteer" class="gi-card">
            <div>
                <div class="gi-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                </div>
                <div class="gi-card-title">Volunteer With Us</div>
                <div class="gi-card-desc">Join our team of advocates, researchers, and organizers. We have volunteer opportunities for every skill set and schedule, from data entry and research to event planning and fundraising.</div>
            </div>
            <div class="gi-card-link">Apply to volunteer <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
        </a>

        {{-- Prisoner Outreach --}}
        <a href="/prisoner-outreach" class="gi-card">
            <div>
                <div class="gi-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/></svg>
                </div>
                <div class="gi-card-title">Write a Letter</div>
                <div class="gi-card-desc">One of the most powerful and direct things you can do. Letters break isolation, provide comfort, and remind political prisoners that the world has not forgotten them. We'll show you how.</div>
            </div>
            <div class="gi-card-link">Learn how to write <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
        </a>

        {{-- Careers --}}
        <a href="/careers-internships" class="gi-card">
            <div>
                <div class="gi-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                </div>
                <div class="gi-card-title">Careers & Internships</div>
                <div class="gi-card-desc">Make advocacy your profession. We're hiring passionate researchers, communicators, and organizers. We also offer a structured internship program for undergraduate and graduate students.</div>
            </div>
            <div class="gi-card-link">See open positions <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
        </a>

        {{-- Spread the Word --}}
        <a href="/store" class="gi-card">
            <div>
                <div class="gi-card-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M4 12v8a2 2 0 002 2h12a2 2 0 002-2v-8"/><path d="M16 6l-4-4-4 4"/><path d="M12 2v13"/></svg>
                </div>
                <div class="gi-card-title">Spread the Word</div>
                <div class="gi-card-desc">Share our articles, follow us on social media, and help raise awareness about political imprisonment in the United States. Visit our store for materials, merch, and educational resources.</div>
            </div>
            <div class="gi-card-link">Visit the store <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg></div>
        </a>

    </div>

    <div class="gi-divider"></div>

    {{-- ==================== DONATE FEATURE ==================== --}}
    <div class="gi-featured">
        <div class="gi-featured-left">
            <div class="gi-featured-label">Make a Donation</div>
            <h2 class="gi-featured-title">Fund the Fight<br>for Freedom</h2>
            <p class="gi-featured-text">Your financial support powers everything we do: legal research, prisoner outreach programs, public education campaigns, and the most comprehensive database of U.S. political prisoners ever built. Every dollar goes directly toward freeing those imprisoned for their beliefs.</p>
            <a href="/donate" class="gi-featured-btn">Donate Now &rarr;</a>
        </div>
        <div class="gi-featured-right">
            @php
                $featuredPrisoner = \App\Models\Prisoner::whereNotNull('photo')->where('photo','!=','')->inRandomOrder()->first();
            @endphp
            @if($featuredPrisoner && $featuredPrisoner->photo)
                <img class="gi-featured-img" src="{{ asset('storage/'.$featuredPrisoner->photo) }}" alt="{{ $featuredPrisoner->name }}">
            @elseif(file_exists(public_path('images/site/getinvolved-featured.jpg')))
                <img class="gi-featured-img" src="/images/site/getinvolved-featured.jpg" alt="Get involved">
            @else
                <div class="gi-featured-placeholder">
                    <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" fill="rgba(255,255,255,0.06)" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                </div>
            @endif
        </div>
    </div>

    <div class="gi-divider"></div>

    {{-- ==================== IMPACT STATS ==================== --}}
    <div class="gi-impact">
        <h2 class="gi-impact-title">Why It Matters</h2>
        <p class="gi-impact-sub">Every action you take contributes to a growing movement for justice. Here's the impact our community has made so far.</p>

        <div class="gi-impact-grid">
            <div class="gi-impact-stat">
                <div class="gi-impact-num">{{ \App\Models\Prisoner::count() }}+</div>
                <div class="gi-impact-label">Political prisoners documented in our database</div>
            </div>
            <div class="gi-impact-stat">
                <div class="gi-impact-num">{{ \App\Models\Prisoner::where('released', true)->count() }}</div>
                <div class="gi-impact-label">Prisoners released and documented in our records</div>
            </div>
            <div class="gi-impact-stat">
                <div class="gi-impact-num">{{ \App\Models\PetitionSignature::count() ?: '1,200' }}+</div>
                <div class="gi-impact-label">Petition signatures collected for active campaigns</div>
            </div>
            <div class="gi-impact-stat">
                <div class="gi-impact-num">50+</div>
                <div class="gi-impact-label">Volunteers and interns who have joined our mission</div>
            </div>
        </div>
    </div>

    <div class="gi-divider"></div>

    {{-- ==================== WAYS TO HELP ==================== --}}
    <div>
        <h2 class="gi-ways-title">Other Ways to Help</h2>
        <div class="gi-ways">
            <div class="gi-way">
                <div class="gi-way-num">01</div>
                <div class="gi-way-name">Attend an Event</div>
                <div class="gi-way-desc">Join us for rallies, panel discussions, film screenings, and community events. Check our <a href="/events" style="color:#5660fe; text-decoration:underline;">events calendar</a> for upcoming opportunities near you or online.</div>
            </div>
            <div class="gi-way">
                <div class="gi-way-num">02</div>
                <div class="gi-way-name">Sign a Petition</div>
                <div class="gi-way-desc">Add your name to active petitions demanding clemency, fair trials, and humane treatment for political prisoners. Your signature sends a message to those in power.</div>
            </div>
            <div class="gi-way">
                <div class="gi-way-num">03</div>
                <div class="gi-way-name">Educate Yourself & Others</div>
                <div class="gi-way-desc">Explore our <a href="/history" style="color:#5660fe; text-decoration:underline;">history section</a>, browse the <a href="/database" style="color:#5660fe; text-decoration:underline;">prisoner database</a>, listen to our <a href="/podcast" style="color:#5660fe; text-decoration:underline;">podcast</a>, and share what you learn with your community.</div>
            </div>
        </div>
    </div>

    <div class="gi-divider"></div>

    {{-- ==================== TESTIMONIAL ==================== --}}
    <div class="gi-testimonial">
        <div class="gi-testimonial-quote">The letters I receive remind me that I'm not alone in this struggle. Each one is proof that the world hasn't forgotten, and that people are fighting for us on the outside.</div>
        <div class="gi-testimonial-author">&mdash; <strong>A Political Prisoner</strong>, through correspondence with NPPC volunteers</div>
    </div>

    <div class="gi-divider"></div>

    {{-- ==================== NEWSLETTER ==================== --}}
    <div class="gi-newsletter">
        <div class="gi-newsletter-left">
            <h2 class="gi-newsletter-title">Stay Connected</h2>
            <p class="gi-newsletter-text">Get updates on new cases, campaign victories, upcoming events, and ways to take action delivered to your inbox.</p>
        </div>
        <form method="POST" action="/sign-up" class="gi-newsletter-form">
            @csrf
            <input type="email" name="email" class="gi-newsletter-input" placeholder="Enter your email" required>
            <button type="submit" class="gi-newsletter-btn">Subscribe</button>
        </form>
    </div>

    <div class="gi-divider"></div>

    {{-- ==================== BOTTOM CTA ==================== --}}
    <div class="gi-bottom">
        <div class="gi-bottom-grid">
            <a href="/contact" class="gi-bottom-card">
                <div class="gi-bottom-card-inner">
                    <div>
                        <div class="gi-bottom-card-title">Have Questions?</div>
                        <div class="gi-bottom-card-desc">Whether you want to partner with us, cover our work, or just learn more, we'd love to hear from you.</div>
                    </div>
                    <div class="gi-bottom-card-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
            <a href="/about" class="gi-bottom-card">
                <div class="gi-bottom-card-inner">
                    <div>
                        <div class="gi-bottom-card-title">Learn About NPPC</div>
                        <div class="gi-bottom-card-desc">Discover our history, meet our team, and understand the mission driving everything we do.</div>
                    </div>
                    <div class="gi-bottom-card-arrow">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </div>
                </div>
            </a>
        </div>
    </div>

</div>
@endsection
