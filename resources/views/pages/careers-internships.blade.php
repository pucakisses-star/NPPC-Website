@extends('app')

@section('head')
<style>
    /* Container & Layout */
    .ci-page { max-width: 1200px; margin: 0 auto; padding: 0 24px; }
    .ci-divider { height: 1px; background: rgba(var(--fg-rgb),0.1); margin: 64px 0; }

    /* Hero */
    .ci-hero { padding: 80px 0 0; }
    .ci-hero-label { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; color: var(--accent-2); margin-bottom: 16px; }
    .ci-hero-title { font-size: 5rem; font-weight: 900; color: var(--fg); line-height: 1.02; margin-bottom: 28px; }
    .ci-hero-sub { font-size: 1.35rem; font-weight: 600; color: rgba(var(--fg-rgb),0.65); line-height: 1.65; max-width: 680px; }

    /* Why Join */
    .ci-why { display: flex; gap: 64px; align-items: flex-start; }
    .ci-why-left { flex: 0 0 260px; }
    .ci-why-label { font-size: 15px; font-weight: 800; color: var(--fg); }
    .ci-why-right { flex: 1; }
    .ci-why-text { font-size: 2.2rem; font-weight: 900; color: var(--fg); line-height: 1.25; margin-bottom: 28px; }
    .ci-why-body { font-size: 17px; color: rgba(var(--fg-rgb),0.6); line-height: 1.75; }
    .ci-pillars { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-top: 40px; }
    .ci-pillar { border: 1px solid rgba(var(--fg-rgb),0.08); border-radius: 8px; padding: 28px; }
    .ci-pillar-icon { width: 44px; height: 44px; background: rgba(86,96,254,0.12); border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
    .ci-pillar-icon svg { width: 22px; height: 22px; }
    .ci-pillar-name { font-size: 17px; font-weight: 800; color: var(--fg); margin-bottom: 8px; }
    .ci-pillar-desc { font-size: 14px; color: rgba(var(--fg-rgb),0.5); line-height: 1.65; }

    /* Positions */
    .ci-positions-header { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 40px; }
    .ci-positions-title { font-size: 3rem; font-weight: 900; color: var(--fg); line-height: 1.1; }
    .ci-positions-count { font-size: 14px; color: rgba(var(--fg-rgb),0.45); font-weight: 600; }
    .ci-listing { border: 1px solid rgba(var(--fg-rgb),0.08); border-radius: 8px; padding: 32px; margin-bottom: 16px; cursor: pointer; transition: background 0.2s, border-color 0.2s; }
    .ci-listing:hover { background: rgba(var(--fg-rgb),0.03); border-color: rgba(var(--fg-rgb),0.15); }
    .ci-listing-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
    .ci-listing-title { font-size: 20px; font-weight: 800; color: var(--fg); margin-bottom: 6px; }
    .ci-listing-meta { display: flex; gap: 20px; flex-wrap: wrap; }
    .ci-listing-tag { font-size: 13px; color: rgba(var(--fg-rgb),0.5); font-weight: 600; display: flex; align-items: center; gap: 6px; }
    .ci-listing-tag svg { width: 14px; height: 14px; opacity: 0.5; }
    .ci-listing-arrow { flex-shrink: 0; width: 40px; height: 40px; border: 1px solid rgba(var(--fg-rgb),0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
    .ci-listing:hover .ci-listing-arrow { border-color: var(--accent); background: var(--accent); }
    .ci-listing-arrow svg { width: 16px; height: 16px; }
    .ci-listing-detail { max-height: 0; overflow: hidden; transition: max-height 0.35s ease, padding-top 0.35s ease; }
    .ci-listing.open .ci-listing-detail { max-height: 800px; padding-top: 24px; }
    .ci-listing-desc { font-size: 15px; color: rgba(var(--fg-rgb),0.6); line-height: 1.75; margin-bottom: 20px; }
    .ci-listing-reqs-title { font-size: 14px; font-weight: 800; color: var(--fg); text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 12px; }
    .ci-listing-reqs { list-style: none; padding: 0; margin: 0 0 24px; }
    .ci-listing-reqs li { font-size: 14px; color: rgba(var(--fg-rgb),0.55); padding: 6px 0; padding-left: 18px; position: relative; line-height: 1.55; }
    .ci-listing-reqs li::before { content: '\2192'; position: absolute; left: 0; color: var(--accent); }
    .ci-listing-apply { display: inline-block; border: 1px solid rgba(var(--fg-rgb),0.3); color: var(--fg); padding: 12px 28px; font-size: 14px; font-weight: 700; text-decoration: none; transition: all 0.2s; }
    .ci-listing-apply:hover { border-color: var(--accent); color: var(--accent); }

    /* Internship Program */
    .ci-intern { display: flex; gap: 48px; align-items: stretch; }
    .ci-intern-left { flex: 1; }
    .ci-intern-title { font-size: 3rem; font-weight: 900; color: var(--fg); line-height: 1.1; margin-bottom: 20px; }
    .ci-intern-text { font-size: 17px; color: rgba(var(--fg-rgb),0.6); line-height: 1.75; margin-bottom: 20px; }
    .ci-intern-features { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 32px; }
    .ci-intern-feat { display: flex; gap: 12px; align-items: flex-start; }
    .ci-intern-feat-dot { flex-shrink: 0; width: 8px; height: 8px; background: var(--accent); border-radius: 50%; margin-top: 6px; }
    .ci-intern-feat-label { font-size: 15px; color: rgba(var(--fg-rgb),0.7); font-weight: 600; line-height: 1.45; }
    .ci-intern-right { flex: 0 0 420px; background: linear-gradient(135deg, #0c0c1e 0%, #1a1040 50%, #2a1860 100%); border-radius: 12px; padding: 40px; display: flex; flex-direction: column; justify-content: space-between; }
    .ci-intern-card-title { font-size: 22px; font-weight: 900; color: var(--on-dark); margin-bottom: 12px; }
    .ci-intern-card-text { font-size: 15px; color: rgba(255,255,255,0.6); line-height: 1.7; }
    .ci-intern-card-detail { margin-top: 32px; }
    .ci-intern-card-row { display: flex; justify-content: space-between; padding: 14px 0; border-bottom: 1px solid rgba(255,255,255,0.08); }
    .ci-intern-card-row:last-child { border-bottom: none; }
    .ci-intern-card-key { font-size: 14px; color: rgba(255,255,255,0.45); font-weight: 600; }
    .ci-intern-card-val { font-size: 14px; color: var(--on-dark); font-weight: 700; }

    /* Benefits */
    .ci-benefits-title { font-size: 3rem; font-weight: 900; color: var(--fg); line-height: 1.1; margin-bottom: 16px; }
    .ci-benefits-sub { font-size: 17px; color: rgba(var(--fg-rgb),0.55); margin-bottom: 48px; max-width: 600px; }
    .ci-benefits-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .ci-benefit { border: 1px solid rgba(var(--fg-rgb),0.08); border-radius: 8px; padding: 32px; transition: border-color 0.2s; }
    .ci-benefit:hover { border-color: rgba(86,96,254,0.3); }
    .ci-benefit-icon { width: 48px; height: 48px; background: rgba(86,96,254,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px; }
    .ci-benefit-icon svg { width: 24px; height: 24px; }
    .ci-benefit-name { font-size: 17px; font-weight: 800; color: var(--fg); margin-bottom: 8px; }
    .ci-benefit-desc { font-size: 14px; color: rgba(var(--fg-rgb),0.5); line-height: 1.65; }

    /* Process */
    .ci-process-title { font-size: 3rem; font-weight: 900; color: var(--fg); line-height: 1.1; margin-bottom: 48px; }
    .ci-steps { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; }
    .ci-step { position: relative; padding: 0 24px; }
    .ci-step-num { font-size: 48px; font-weight: 900; color: rgba(86,96,254,0.2); margin-bottom: 16px; line-height: 1; }
    .ci-step-name { font-size: 17px; font-weight: 800; color: var(--fg); margin-bottom: 8px; }
    .ci-step-desc { font-size: 14px; color: rgba(var(--fg-rgb),0.5); line-height: 1.6; }
    .ci-step::before { content: ''; position: absolute; top: 28px; left: 0; right: 0; height: 1px; background: rgba(var(--fg-rgb),0.08); }
    .ci-step:first-child::before { left: 50%; }
    .ci-step:last-child::before { right: 50%; }

    /* CTA */
    .ci-cta { text-align: center; padding: 80px 0; }
    .ci-cta-title { font-size: 3rem; font-weight: 900; color: var(--fg); margin-bottom: 16px; }
    .ci-cta-text { font-size: 17px; color: rgba(var(--fg-rgb),0.55); margin-bottom: 36px; max-width: 520px; margin-left: auto; margin-right: auto; line-height: 1.7; }
    .ci-cta-btn { display: inline-block; background: var(--accent); color: var(--on-accent); padding: 16px 40px; font-size: 15px; font-weight: 700; text-decoration: none; text-transform: uppercase; letter-spacing: 0.06em; transition: background 0.2s; }
    .ci-cta-btn:hover { background: var(--accent-hover); }
    .ci-cta-or { font-size: 14px; color: rgba(var(--fg-rgb),0.35); margin-top: 20px; }
    .ci-cta-or a { color: var(--accent-2); text-decoration: underline; }

    /* Responsive */
    @@media (max-width: 900px) {
        .ci-hero-title { font-size: 3.2rem; }
        .ci-why { flex-direction: column; gap: 24px; }
        .ci-why-left { flex: auto; }
        .ci-pillars { grid-template-columns: 1fr; }
        .ci-intern { flex-direction: column; }
        .ci-intern-right { flex: auto; }
        .ci-intern-features { grid-template-columns: 1fr; }
        .ci-benefits-grid { grid-template-columns: 1fr; }
        .ci-steps { grid-template-columns: 1fr 1fr; gap: 32px; }
        .ci-step::before { display: none; }
    }
    @@media (max-width: 640px) {
        .ci-hero-title { font-size: 2.5rem; }
        .ci-positions-header { flex-direction: column; align-items: flex-start; gap: 8px; }
        .ci-listing-top { flex-direction: column; }
        .ci-steps { grid-template-columns: 1fr; }
    }
</style>
@endsection

@section('body')
<div class="ci-page">

    {{-- ==================== HERO ==================== --}}
    <div class="ci-hero">
        <div class="ci-hero-label">Careers & Internships</div>
        <h1 class="ci-hero-title">Join the Fight<br>for Justice</h1>
        <p class="ci-hero-sub">We're building a team of passionate advocates, researchers, and organizers committed to ending political imprisonment in the United States. If you believe in the power of solidarity, we want to hear from you.</p>
    </div>

    <div class="ci-divider"></div>

    {{-- ==================== WHY JOIN ==================== --}}
    <div class="ci-why">
        <div class="ci-why-left">
            <div class="ci-why-label">Why Join NPPC</div>
        </div>
        <div class="ci-why-right">
            <div class="ci-why-text">We're a small, driven team doing work that matters. Every role here has a direct impact on the lives of political prisoners and their families.</div>
            <div class="ci-why-body">At NPPC, you won't get lost in bureaucracy. You'll collaborate closely with advocates, legal experts, and community organizers on campaigns that shape public awareness and influence policy. We value initiative, compassion, and a deep commitment to human rights.</div>

            <div class="ci-pillars">
                <div class="ci-pillar">
                    <div class="ci-pillar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    </div>
                    <div class="ci-pillar-name">Meaningful Work</div>
                    <div class="ci-pillar-desc">Every project contributes to documenting injustice, supporting prisoners, and advocating for systemic change.</div>
                </div>
                <div class="ci-pillar">
                    <div class="ci-pillar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                    </div>
                    <div class="ci-pillar-name">Collaborative Culture</div>
                    <div class="ci-pillar-desc">We work as a tight-knit team where every voice is heard, ideas are welcomed, and decisions are made collectively.</div>
                </div>
                <div class="ci-pillar">
                    <div class="ci-pillar-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                    <div class="ci-pillar-name">Growth & Learning</div>
                    <div class="ci-pillar-desc">Develop expertise in advocacy, research, communications, and nonprofit leadership in a supportive environment.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="ci-divider"></div>

    {{-- ==================== OPEN POSITIONS ==================== --}}
    <div>
        <div class="ci-positions-header">
            <h2 class="ci-positions-title">Open Positions</h2>
            <div class="ci-positions-count">3 openings</div>
        </div>

        {{-- Listing 1 --}}
        <div class="ci-listing" onclick="this.classList.toggle('open')">
            <div class="ci-listing-top">
                <div>
                    <div class="ci-listing-title">Research & Policy Analyst</div>
                    <div class="ci-listing-meta">
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Remote / Washington, D.C.
                        </span>
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            Full-time
                        </span>
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                            $55,000 &ndash; $70,000
                        </span>
                    </div>
                </div>
                <div class="ci-listing-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                </div>
            </div>
            <div class="ci-listing-detail">
                <p class="ci-listing-desc">We're looking for a meticulous researcher to investigate political imprisonment cases, analyze legal proceedings, draft policy briefs, and contribute to our case database. You'll work alongside legal advisors and advocacy staff to produce evidence-based reports that support campaigns for prisoner release and policy reform.</p>
                <div class="ci-listing-reqs-title">Requirements</div>
                <ul class="ci-listing-reqs">
                    <li>Bachelor's degree in political science, law, public policy, or related field</li>
                    <li>2+ years experience in legal research, policy analysis, or investigative journalism</li>
                    <li>Strong analytical writing and data synthesis skills</li>
                    <li>Familiarity with the U.S. criminal justice system and political prisoner history</li>
                    <li>Ability to work independently and manage multiple research projects</li>
                </ul>
                <a href="/contact" class="ci-listing-apply">Apply Now &rarr;</a>
            </div>
        </div>

        {{-- Listing 2 --}}
        <div class="ci-listing" onclick="this.classList.toggle('open')">
            <div class="ci-listing-top">
                <div>
                    <div class="ci-listing-title">Communications & Outreach Coordinator</div>
                    <div class="ci-listing-meta">
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Remote
                        </span>
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            Full-time
                        </span>
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                            $48,000 &ndash; $62,000
                        </span>
                    </div>
                </div>
                <div class="ci-listing-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                </div>
            </div>
            <div class="ci-listing-detail">
                <p class="ci-listing-desc">Own our public-facing voice across digital channels, media relations, and community engagement. You'll craft compelling stories about political prisoners, manage social media strategy, coordinate press outreach, and develop campaigns that grow our supporter base and amplify our mission.</p>
                <div class="ci-listing-reqs-title">Requirements</div>
                <ul class="ci-listing-reqs">
                    <li>2+ years experience in nonprofit communications, journalism, or digital marketing</li>
                    <li>Excellent writing, editing, and storytelling skills</li>
                    <li>Experience managing social media platforms and email marketing tools</li>
                    <li>Comfort working with media and preparing spokespeople for interviews</li>
                    <li>Passion for social justice and human rights</li>
                </ul>
                <a href="/contact" class="ci-listing-apply">Apply Now &rarr;</a>
            </div>
        </div>

        {{-- Listing 3 --}}
        <div class="ci-listing" onclick="this.classList.toggle('open')">
            <div class="ci-listing-top">
                <div>
                    <div class="ci-listing-title">Development & Fundraising Associate</div>
                    <div class="ci-listing-meta">
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg>
                            Washington, D.C.
                        </span>
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            Part-time
                        </span>
                        <span class="ci-listing-tag">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                            $22 &ndash; $30 / hr
                        </span>
                    </div>
                </div>
                <div class="ci-listing-arrow">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                </div>
            </div>
            <div class="ci-listing-detail">
                <p class="ci-listing-desc">Support our fundraising efforts by researching grant opportunities, drafting proposals, cultivating donor relationships, and helping plan fundraising events. This role is ideal for someone building a career in nonprofit development who wants hands-on experience across the full fundraising cycle.</p>
                <div class="ci-listing-reqs-title">Requirements</div>
                <ul class="ci-listing-reqs">
                    <li>1+ years experience in fundraising, grant writing, or donor relations</li>
                    <li>Strong organizational skills and attention to detail</li>
                    <li>Proficiency with CRM tools and donor management systems</li>
                    <li>Excellent interpersonal and relationship-building skills</li>
                    <li>Commitment to NPPC's mission and values</li>
                </ul>
                <a href="/contact" class="ci-listing-apply">Apply Now &rarr;</a>
            </div>
        </div>
    </div>

    <div class="ci-divider"></div>

    {{-- ==================== INTERNSHIP PROGRAM ==================== --}}
    <div class="ci-intern">
        <div class="ci-intern-left">
            <h2 class="ci-intern-title">Internship Program</h2>
            <p class="ci-intern-text">Our internship program offers undergraduate and graduate students a chance to gain real-world experience in human rights advocacy, legal research, and nonprofit operations. Interns are embedded in active projects from day one and contribute meaningfully to our mission.</p>
            <p class="ci-intern-text">Past interns have gone on to careers at organizations including the ACLU, Human Rights Watch, Amnesty International, and the Department of Justice.</p>

            <div class="ci-intern-features">
                <div class="ci-intern-feat">
                    <div class="ci-intern-feat-dot"></div>
                    <div class="ci-intern-feat-label">Mentorship from senior staff</div>
                </div>
                <div class="ci-intern-feat">
                    <div class="ci-intern-feat-dot"></div>
                    <div class="ci-intern-feat-label">Hands-on case research</div>
                </div>
                <div class="ci-intern-feat">
                    <div class="ci-intern-feat-dot"></div>
                    <div class="ci-intern-feat-label">Professional development workshops</div>
                </div>
                <div class="ci-intern-feat">
                    <div class="ci-intern-feat-dot"></div>
                    <div class="ci-intern-feat-label">Networking with advocacy leaders</div>
                </div>
                <div class="ci-intern-feat">
                    <div class="ci-intern-feat-dot"></div>
                    <div class="ci-intern-feat-label">Capstone presentation project</div>
                </div>
                <div class="ci-intern-feat">
                    <div class="ci-intern-feat-dot"></div>
                    <div class="ci-intern-feat-label">Flexible remote schedule</div>
                </div>
            </div>
        </div>

        <div class="ci-intern-right">
            <div>
                <div class="ci-intern-card-title">Program Details</div>
                <div class="ci-intern-card-text">Applications are accepted on a rolling basis. Internships are available during the fall, spring, and summer terms.</div>
            </div>
            <div class="ci-intern-card-detail">
                <div class="ci-intern-card-row">
                    <span class="ci-intern-card-key">Duration</span>
                    <span class="ci-intern-card-val">10 &ndash; 16 weeks</span>
                </div>
                <div class="ci-intern-card-row">
                    <span class="ci-intern-card-key">Hours</span>
                    <span class="ci-intern-card-val">15 &ndash; 25 hrs/week</span>
                </div>
                <div class="ci-intern-card-row">
                    <span class="ci-intern-card-key">Compensation</span>
                    <span class="ci-intern-card-val">Stipend available</span>
                </div>
                <div class="ci-intern-card-row">
                    <span class="ci-intern-card-key">Location</span>
                    <span class="ci-intern-card-val">Remote / Hybrid</span>
                </div>
                <div class="ci-intern-card-row">
                    <span class="ci-intern-card-key">Eligibility</span>
                    <span class="ci-intern-card-val">Undergrad & Graduate</span>
                </div>
            </div>
        </div>
    </div>

    <div class="ci-divider"></div>

    {{-- ==================== BENEFITS ==================== --}}
    <div>
        <h2 class="ci-benefits-title">What We Offer</h2>
        <p class="ci-benefits-sub">We invest in the people who invest their time and energy in our mission.</p>

        <div class="ci-benefits-grid">
            <div class="ci-benefit">
                <div class="ci-benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
                </div>
                <div class="ci-benefit-name">Health & Wellness</div>
                <div class="ci-benefit-desc">Comprehensive medical, dental, and vision coverage for full-time employees and their dependents.</div>
            </div>
            <div class="ci-benefit">
                <div class="ci-benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg>
                </div>
                <div class="ci-benefit-name">Remote Flexibility</div>
                <div class="ci-benefit-desc">Work from anywhere with flexible hours. We believe in autonomy and trust our team to manage their time.</div>
            </div>
            <div class="ci-benefit">
                <div class="ci-benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
                </div>
                <div class="ci-benefit-name">Learning Budget</div>
                <div class="ci-benefit-desc">Annual professional development stipend for conferences, courses, certifications, and books.</div>
            </div>
            <div class="ci-benefit">
                <div class="ci-benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                </div>
                <div class="ci-benefit-name">Generous PTO</div>
                <div class="ci-benefit-desc">20 days paid vacation, federal holidays, and a winter recess between Christmas and New Year's.</div>
            </div>
            <div class="ci-benefit">
                <div class="ci-benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <div class="ci-benefit-name">Retirement Plan</div>
                <div class="ci-benefit-desc">403(b) retirement plan with employer matching contributions after one year of employment.</div>
            </div>
            <div class="ci-benefit">
                <div class="ci-benefit-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#5660fe" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>
                </div>
                <div class="ci-benefit-name">Student Loan Assistance</div>
                <div class="ci-benefit-desc">PSLF-eligible employer. We also offer a monthly student loan repayment assistance benefit.</div>
            </div>
        </div>
    </div>

    <div class="ci-divider"></div>

    {{-- ==================== APPLICATION PROCESS ==================== --}}
    <div>
        <h2 class="ci-process-title">How to Apply</h2>
        <div class="ci-steps">
            <div class="ci-step">
                <div class="ci-step-num">01</div>
                <div class="ci-step-name">Submit Application</div>
                <div class="ci-step-desc">Send your resume, cover letter, and a brief writing sample to our team via the contact form or email.</div>
            </div>
            <div class="ci-step">
                <div class="ci-step-num">02</div>
                <div class="ci-step-name">Initial Screen</div>
                <div class="ci-step-desc">Our team reviews applications on a rolling basis. Qualified candidates will be contacted within two weeks.</div>
            </div>
            <div class="ci-step">
                <div class="ci-step-num">03</div>
                <div class="ci-step-name">Interview</div>
                <div class="ci-step-desc">Meet with team members over a video call to discuss your experience, interests, and how you can contribute.</div>
            </div>
            <div class="ci-step">
                <div class="ci-step-num">04</div>
                <div class="ci-step-name">Welcome Aboard</div>
                <div class="ci-step-desc">Receive an offer and join the team. Onboarding includes orientation, mentorship pairing, and project assignment.</div>
            </div>
        </div>
    </div>

    <div class="ci-divider"></div>

    {{-- ==================== CTA ==================== --}}
    <div class="ci-cta">
        <h2 class="ci-cta-title">Ready to Make a Difference?</h2>
        <p class="ci-cta-text">Whether you're looking for a career in advocacy or a meaningful internship experience, we'd love to hear from you.</p>
        <a href="/contact" class="ci-cta-btn">Get in Touch</a>
        <div class="ci-cta-or">Or email us directly at <a href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a></div>
    </div>

</div>
@endsection
