@extends('app')

@section('head')
<style>
    .vol2-page { color: rgba(255,255,255,0.85); }

    /* ─── Hero ─── */
    .vol2-hero {
        position: relative;
        max-width: 1200px;
        margin: 0 auto;
        padding: 80px 24px 60px;
        display: grid;
        grid-template-columns: 1.1fr 0.9fr;
        gap: 60px;
        align-items: center;
    }
    .vol2-eyebrow {
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #5660fe;
        margin-bottom: 18px;
    }
    .vol2-eyebrow a { color: rgba(255,255,255,0.55); text-decoration: none; }
    .vol2-eyebrow a:hover { color: #fff; }
    .vol2-hero-title {
        font-size: 5rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.02;
        margin: 0 0 28px;
        letter-spacing: -0.02em;
    }
    .vol2-hero-lede {
        font-size: 22px;
        color: rgba(255,255,255,0.78);
        line-height: 1.55;
        margin: 0 0 32px;
        max-width: 540px;
    }
    .vol2-hero-cta {
        display: inline-block;
        background: #5660fe;
        color: #fff;
        padding: 18px 40px;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        text-decoration: none;
        transition: background 0.15s;
    }
    .vol2-hero-cta:hover { background: #4450ee; }

    .vol2-hero-image {
        position: relative;
        aspect-ratio: 4 / 5;
        border-radius: 4px;
        overflow: hidden;
        background: linear-gradient(135deg, #14101a 0%, #1a1040 50%, #5660fe 100%);
    }
    .vol2-hero-image img { width: 100%; height: 100%; object-fit: cover; }
    .vol2-hero-image::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(0deg, rgba(0,0,0,0.45) 0%, transparent 60%);
        pointer-events: none;
    }

    /* ─── Lead pull-quote section ─── */
    .vol2-lead {
        max-width: 880px;
        margin: 80px auto;
        padding: 0 24px;
    }
    .vol2-lead p {
        font-size: 28px;
        font-weight: 600;
        line-height: 1.45;
        color: #fff;
        letter-spacing: -0.01em;
        margin: 0 0 28px;
    }
    .vol2-lead p:last-child { font-weight: 400; font-size: 19px; color: rgba(255,255,255,0.7); line-height: 1.7; }
    .vol2-lead-rule {
        width: 60px;
        height: 4px;
        background: #5660fe;
        margin-bottom: 32px;
    }

    /* ─── Section heads ─── */
    .vol2-section { max-width: 1200px; margin: 0 auto; padding: 60px 24px; }
    .vol2-section-eyebrow {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #5660fe;
        margin-bottom: 12px;
    }
    .vol2-section-heading {
        font-size: 2.6rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.1;
        margin: 0 0 16px;
        letter-spacing: -0.02em;
    }
    .vol2-section-sub {
        font-size: 17px;
        color: rgba(255,255,255,0.6);
        line-height: 1.7;
        max-width: 720px;
        margin: 0 0 48px;
    }

    /* ─── Card grids ─── */
    .vol2-card-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.1);
    }
    .vol2-card {
        background: #0a0a14;
        padding: 36px 28px;
        position: relative;
        transition: background 0.15s;
    }
    .vol2-card:hover { background: #11111e; }
    .vol2-card-num {
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.12em;
        color: #5660fe;
        margin-bottom: 16px;
    }
    .vol2-card-title {
        font-size: 1.35rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 12px;
        letter-spacing: -0.01em;
    }
    .vol2-card-text {
        font-size: 15px;
        color: rgba(255,255,255,0.6);
        line-height: 1.7;
        margin: 0;
    }

    /* ─── Form panel ─── */
    .vol2-form-wrap {
        max-width: 1100px;
        margin: 60px auto 100px;
        padding: 56px 24px 64px;
    }
    .vol2-form-heading {
        font-size: 2.2rem;
        font-weight: 900;
        color: #fff;
        margin: 0 0 12px;
        letter-spacing: -0.01em;
    }
    .vol2-form-sub {
        font-size: 16px;
        color: rgba(255,255,255,0.55);
        line-height: 1.7;
        margin: 0 0 48px;
        max-width: 640px;
    }
    .vol2-fieldset {
        border: none;
        padding: 0;
        margin: 0 0 56px;
    }
    .vol2-legend {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #5660fe;
        padding: 0;
        margin: 0 0 24px;
    }
    .vol2-input {
        width: 100%;
        background: transparent;
        border: 0;
        border-bottom: 1px solid rgba(255,255,255,0.2);
        color: #fff;
        padding: 14px 2px;
        font-size: 16px;
        font-family: inherit;
        transition: border-color 0.15s;
    }
    .vol2-input:focus { border-bottom-color: #5660fe; outline: none; }
    .vol2-input::placeholder { color: rgba(255,255,255,0.35); }
    .vol2-textarea {
        width: 100%;
        background: rgba(255,255,255,0.03);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 4px;
        color: #fff;
        padding: 18px 20px;
        font-size: 15px;
        font-family: inherit;
        resize: vertical;
        min-height: 160px;
        transition: border-color 0.15s, background 0.15s;
    }
    .vol2-textarea:focus { border-color: #5660fe; background: rgba(86,96,254,0.04); outline: none; }
    .vol2-textarea::placeholder { color: rgba(255,255,255,0.35); }
    .vol2-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .vol2-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 24px; }
    .vol2-row { margin-bottom: 24px; }

    .vol2-checkbox-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px 32px;
    }
    .vol2-checkbox {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 15px;
        color: rgba(255,255,255,0.85);
        cursor: pointer;
        padding: 8px 0;
    }
    .vol2-checkbox input[type="checkbox"] {
        width: 20px;
        height: 20px;
        accent-color: #5660fe;
        cursor: pointer;
        flex-shrink: 0;
    }
    .vol2-hint {
        font-size: 13px;
        color: rgba(255,255,255,0.4);
        margin-top: 16px;
        line-height: 1.5;
    }

    .vol2-submit {
        background: #5660fe;
        color: #fff;
        border: none;
        padding: 20px 48px;
        font-size: 14px;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        cursor: pointer;
        transition: background 0.15s;
    }
    .vol2-submit:hover { background: #4450ee; }

    .vol2-success {
        background: rgba(86,96,254,0.08);
        border-left: 4px solid #5660fe;
        padding: 24px 28px;
        margin: 0 auto 40px;
        max-width: 1100px;
        color: #fff;
        font-size: 17px;
        line-height: 1.6;
    }

    @@media (max-width: 900px) {
        .vol2-hero { grid-template-columns: 1fr; padding: 56px 24px 40px; }
        .vol2-hero-image { aspect-ratio: 16 / 10; }
        .vol2-hero-title { font-size: 3rem; }
        .vol2-hero-lede { font-size: 18px; }
        .vol2-card-grid { grid-template-columns: 1fr; }
        .vol2-form-wrap { padding: 40px 24px; }
        .vol2-grid-2, .vol2-grid-3, .vol2-checkbox-grid { grid-template-columns: 1fr; }
        .vol2-lead p { font-size: 22px; }
        .vol2-section-heading { font-size: 2rem; }
        .vol2-form-heading { font-size: 1.8rem; }
    }
</style>
@endsection

@section('body')
<div class="vol2-page">

    {{-- Success Message --}}
    @if(request('form_submitted'))
        <div class="vol2-success">
            <strong>Thank you.</strong> We've received your application and will be in touch soon.
        </div>
    @endif

    {{-- ─── Hero ─── --}}
    <section class="vol2-hero">
        <div>
            <div class="vol2-eyebrow"><a href="/get-involved">Get Involved</a> &nbsp;/&nbsp; Volunteer</div>
            <h1 class="vol2-hero-title">Be the difference.</h1>
            <p class="vol2-hero-lede">
                At NPPC, our volunteers are the heart and soul of our organization. By dedicating your time and skills, you help us make a significant impact in the lives of political prisoners across the United States.
            </p>
            <a href="#apply" class="vol2-hero-cta">Apply to Volunteer</a>
        </div>
        <div class="vol2-hero-image">
            @if(file_exists(public_path('images/site/volunteer-hero.jpg')))
                <img src="/images/site/volunteer-hero.jpg" alt="Volunteers at work">
            @endif
        </div>
    </section>

    {{-- ─── Lead pull section ─── --}}
    <section class="vol2-lead">
        <div class="vol2-lead-rule"></div>
        <p>Whether you're looking to gain new experiences, meet like-minded people, or give back to society's most vulnerable population, we have a variety of volunteer opportunities that can match your interests and availability.</p>
        <p>Every letter written, every database entry verified, every event staffed and dollar raised translates directly into support for incarcerated organizers and their families. Fill out the application below and a member of our volunteer team will follow up within two weeks.</p>
    </section>

    {{-- ─── Ways to volunteer ─── --}}
    <section class="vol2-section">
        <div class="vol2-section-eyebrow">Where You Can Help</div>
        <h2 class="vol2-section-heading">Ways to volunteer</h2>
        <p class="vol2-section-sub">
            Pick the work that fits you best — most volunteers can be active for as little as a few hours a month. You'll be matched with a coordinator who can answer questions and onboard you.
        </p>
        <div class="vol2-card-grid">
            <div class="vol2-card">
                <div class="vol2-card-num">01</div>
                <h3 class="vol2-card-title">Data Entry & Research</h3>
                <p class="vol2-card-text">Help maintain the prisoner database — verifying inmate numbers and case dates, transcribing court records, and chasing down sources for new profiles.</p>
            </div>
            <div class="vol2-card">
                <div class="vol2-card-num">02</div>
                <h3 class="vol2-card-title">Clerical & Letter-Writing</h3>
                <p class="vol2-card-text">Filing, copying, mailing, and running letter-writing nights at local bookstores, infoshops, and community spaces — concrete solidarity that prisoners feel directly.</p>
            </div>
            <div class="vol2-card">
                <div class="vol2-card-num">03</div>
                <h3 class="vol2-card-title">Event Planning & Staffing</h3>
                <p class="vol2-card-text">Coordinating speaker visits, conferences, vigils, and book-fair tables. Help us show up where people are listening.</p>
            </div>
            <div class="vol2-card">
                <div class="vol2-card-num">04</div>
                <h3 class="vol2-card-title">Fundraising</h3>
                <p class="vol2-card-text">Run a donation drive, host a benefit, secure matching gifts. Every dollar reduces our reliance on grants and lets us spend more on direct prisoner support.</p>
            </div>
            <div class="vol2-card">
                <div class="vol2-card-num">05</div>
                <h3 class="vol2-card-title">Writing & Communications</h3>
                <p class="vol2-card-text">Newsletter copy, social posts, profile narratives, press releases. We pay for original writing from formerly incarcerated authors.</p>
            </div>
            <div class="vol2-card">
                <div class="vol2-card-num">06</div>
                <h3 class="vol2-card-title">Specialized Skills</h3>
                <p class="vol2-card-text">Web development, photography, video, accounting, legal expertise — we welcome paralegals, attorneys, and law students for case-research and FOIA work.</p>
            </div>
        </div>
    </section>

    {{-- ─── Application form ─── --}}
    <div class="vol2-form-wrap" id="apply">
        <h2 class="vol2-form-heading">Volunteer application</h2>
        <p class="vol2-form-sub">
            Tell us a little about yourself and what you'd like to work on. Required fields are marked with an asterisk; everything else is optional.
        </p>

        <form method="POST" id="volunteer-form" action="/form/volunteer">
            @csrf

            {{-- Contact Information --}}
            <fieldset class="vol2-fieldset">
                <legend class="vol2-legend">Contact Information</legend>

                <div class="vol2-grid-2 vol2-row">
                    <input type="text" name="first_name" class="vol2-input" placeholder="First name *" required>
                    <input type="text" name="last_name" class="vol2-input" placeholder="Last name *" required>
                </div>

                <div class="vol2-row">
                    <input type="email" name="email" class="vol2-input" placeholder="Email address *" required>
                </div>

                <div class="vol2-grid-3 vol2-row">
                    <input type="text" name="city" class="vol2-input" placeholder="City">
                    <input type="text" name="state" class="vol2-input" placeholder="State">
                    <input type="text" name="zip_code" class="vol2-input" placeholder="ZIP code">
                </div>

                <div class="vol2-grid-2 vol2-row">
                    <input type="text" name="phone_number" class="vol2-input" placeholder="Phone number">
                    <input type="text" name="mobile_phone" class="vol2-input" placeholder="Mobile phone">
                </div>
            </fieldset>

            {{-- Skills & Interests --}}
            <fieldset class="vol2-fieldset">
                <legend class="vol2-legend">Areas of Interest</legend>

                <div class="vol2-checkbox-grid">
                    <label class="vol2-checkbox"><input type="checkbox" name="fields_of_interest[]" value="Data Entry"> Data entry</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="fields_of_interest[]" value="Clerical"> Clerical (filing, copying, mailing)</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="fields_of_interest[]" value="Event Planning"> Event planning &amp; staffing</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="fields_of_interest[]" value="Fundraising"> Fundraising</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="fields_of_interest[]" value="Research"> Research</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="fields_of_interest[]" value="Writing"> Writing</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="fields_of_interest[]" value="Other"> Other (please specify)</label>
                </div>
                <p class="vol2-hint">Check every area where you have experience and interest in volunteering.</p>

                <div class="vol2-row" style="margin-top: 24px;">
                    <input type="text" name="other_interests" class="vol2-input" placeholder="Other interests (if applicable)">
                </div>
            </fieldset>

            {{-- Skills --}}
            <fieldset class="vol2-fieldset">
                <legend class="vol2-legend">Skills</legend>

                <div class="vol2-checkbox-grid">
                    <label class="vol2-checkbox"><input type="checkbox" name="skills[]" value="Web Development"> Web development</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="skills[]" value="Photography"> Photography</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="skills[]" value="Video Production"> Video production</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="skills[]" value="Accounting"> Accounting</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="skills[]" value="Fundraising"> Fundraising</label>
                    <label class="vol2-checkbox"><input type="checkbox" name="skills[]" value="Legal Expertise"> Legal expertise</label>
                </div>
            </fieldset>

            {{-- Educational Background --}}
            <fieldset class="vol2-fieldset">
                <legend class="vol2-legend">Educational Background</legend>
                <textarea name="educational_background" class="vol2-textarea" placeholder="Where did you study? Degrees, certifications, training programs."></textarea>
            </fieldset>

            {{-- Work Experience --}}
            <fieldset class="vol2-fieldset">
                <legend class="vol2-legend">Work Experience</legend>
                <textarea name="work_experience" class="vol2-textarea" placeholder="Tell us about your professional and volunteer history."></textarea>
            </fieldset>

            {{-- Why Volunteer --}}
            <fieldset class="vol2-fieldset">
                <legend class="vol2-legend">Why do you want to volunteer at NPPC?</legend>
                <textarea name="message" class="vol2-textarea" placeholder="Briefly describe your interest in the work of the National Political Prisoner Coalition."></textarea>
            </fieldset>

            <div style="margin-top: 16px;">
                <button type="submit" class="vol2-submit">Submit application</button>
            </div>
        </form>
    </div>
</div>
@endsection
