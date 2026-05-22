@extends('app')

@section('head')
<style>
    .ct-page { color: rgba(255,255,255,0.85); }

    /* Hero */
    .ct-hero { max-width: 1100px; margin: 0 auto; padding: 80px 24px 48px; }
    .ct-eyebrow {
        font-size: 13px;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #5660fe;
        margin-bottom: 18px;
    }
    .ct-hero-title {
        font-size: 5rem;
        font-weight: 900;
        color: #fff;
        line-height: 1;
        letter-spacing: -0.02em;
        margin: 0 0 28px;
    }
    .ct-hero-lede {
        font-size: 22px;
        color: rgba(255,255,255,0.78);
        line-height: 1.55;
        max-width: 720px;
        margin: 0 0 16px;
    }

    /* Card grid — different ways to reach us */
    .ct-section { max-width: 1100px; margin: 0 auto; padding: 56px 24px; }
    .ct-section-eyebrow {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #5660fe;
        margin-bottom: 12px;
    }
    .ct-section-heading {
        font-size: 2.4rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.1;
        margin: 0 0 16px;
        letter-spacing: -0.02em;
    }
    .ct-section-sub {
        font-size: 17px;
        color: rgba(255,255,255,0.6);
        line-height: 1.7;
        max-width: 720px;
        margin: 0 0 40px;
    }
    .ct-card-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 1px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.1);
    }
    .ct-card {
        background: #0a0a14;
        padding: 32px 28px;
        transition: background 0.15s;
    }
    .ct-card:hover { background: #11111e; }
    .ct-card-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #fff;
        margin: 0 0 8px;
        letter-spacing: -0.01em;
    }
    .ct-card-text {
        font-size: 14px;
        color: rgba(255,255,255,0.6);
        line-height: 1.6;
        margin: 0 0 14px;
    }
    .ct-card-link {
        font-size: 13px;
        font-weight: 700;
        color: #5660fe;
        letter-spacing: 0.04em;
        text-decoration: none;
        text-transform: uppercase;
    }
    .ct-card-link:hover { text-decoration: underline; }

    /* Form */
    .ct-form-wrap { max-width: 1100px; margin: 0 auto; padding: 24px 24px 100px; }
    .ct-form-grid {
        display: grid;
        grid-template-columns: 0.9fr 1.1fr;
        gap: 80px;
        align-items: start;
    }
    .ct-form-side h2 {
        font-size: 2.2rem;
        font-weight: 900;
        color: #fff;
        line-height: 1.1;
        margin: 0 0 16px;
        letter-spacing: -0.01em;
    }
    .ct-form-side p {
        font-size: 16px;
        color: rgba(255,255,255,0.6);
        line-height: 1.7;
        margin: 0 0 24px;
    }
    .ct-meta-block { margin-top: 32px; }
    .ct-meta-label {
        font-size: 11px;
        font-weight: 800;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.5);
        margin-bottom: 6px;
    }
    .ct-meta-value {
        font-size: 15px;
        color: #fff;
        line-height: 1.6;
        margin: 0 0 18px;
    }
    .ct-meta-value a { color: #5660fe; text-decoration: none; }
    .ct-meta-value a:hover { text-decoration: underline; }

    .ct-input {
        width: 100%;
        background: transparent;
        border: 1px solid rgba(255,255,255,0.25);
        color: #fff;
        padding: 22px 20px;
        font-size: 16px;
        font-family: inherit;
        transition: border-color 0.15s, background 0.15s;
    }
    .ct-input:focus { border-color: #5660fe; background: rgba(86,96,254,0.04); outline: none; }
    .ct-input::placeholder { color: rgba(255,255,255,0.5); }
    .ct-textarea {
        width: 100%;
        background: transparent;
        border: 1px solid rgba(255,255,255,0.25);
        color: #fff;
        padding: 20px;
        font-size: 16px;
        font-family: inherit;
        resize: vertical;
        min-height: 200px;
        transition: border-color 0.15s, background 0.15s;
    }
    .ct-textarea:focus { border-color: #5660fe; background: rgba(86,96,254,0.04); outline: none; }
    .ct-textarea::placeholder { color: rgba(255,255,255,0.5); }
    .ct-row { margin-bottom: 20px; }
    .ct-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

    .ct-submit {
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
    .ct-submit:hover { background: #4450ee; }

    .ct-success {
        background: rgba(86,96,254,0.08);
        border-left: 4px solid #5660fe;
        padding: 24px 28px;
        margin: 0 auto 32px;
        max-width: 1100px;
        color: #fff;
        font-size: 17px;
        line-height: 1.6;
    }
    .ct-error {
        background: rgba(239,68,68,0.08);
        border-left: 4px solid #ef4444;
        padding: 20px 24px;
        margin: 0 auto 24px;
        color: #fff;
        font-size: 15px;
        line-height: 1.6;
    }
    .ct-error p { margin: 0 0 6px; }

    .ct-fieldset-eyebrow {
        font-size: 12px;
        font-weight: 800;
        letter-spacing: 0.16em;
        text-transform: uppercase;
        color: #5660fe;
        margin: 0 0 18px;
    }

    @@media (max-width: 900px) {
        .ct-hero { padding: 56px 24px 32px; }
        .ct-hero-title { font-size: 3rem; }
        .ct-hero-lede { font-size: 18px; }
        .ct-card-grid { grid-template-columns: 1fr; }
        .ct-form-grid { grid-template-columns: 1fr; gap: 40px; }
        .ct-grid-2 { grid-template-columns: 1fr; }
        .ct-section-heading { font-size: 2rem; }
        .ct-form-side h2 { font-size: 1.8rem; }
    }
</style>
@endsection

@section('body')
<div class="ct-page">

    @if(request('form_submitted'))
        <div class="ct-success">
            <strong>Thank you.</strong> We've received your message and will get back to you shortly.
        </div>
    @endif

    {{-- Hero --}}
    <section class="ct-hero">
        <div class="ct-eyebrow">Get in Touch</div>
        <h1 class="ct-hero-title">Contact us.</h1>
        <p class="ct-hero-lede">
            Whether you have a question about our work, want to submit a case, correct a profile, request media comment, or simply want to say hello — we read every message and respond.
        </p>
    </section>

    {{-- Form (General Inquiry) --}}
    <div class="ct-form-wrap">
        <div class="ct-form-grid">
            <div class="ct-form-side">
                <div class="ct-fieldset-eyebrow">General Inquiry</div>
                <h2>Send us a message.</h2>
                <p>
                    Use this form for anything that doesn't fit one of the channels below. We read every message and a real person will write back.
                </p>

                <div class="ct-meta-block">
                    <div class="ct-meta-label">Mailing Address</div>
                    <p class="ct-meta-value">
                        National Political Prisoner Coalition<br>
                        P.O. Box pending<br>
                        United States
                    </p>
                </div>

                <div class="ct-meta-block">
                    <div class="ct-meta-label">General Email</div>
                    <p class="ct-meta-value">
                        <a href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a>
                    </p>
                </div>

                <div class="ct-meta-block">
                    <div class="ct-meta-label">Response Time</div>
                    <p class="ct-meta-value">Within two weeks for most inquiries; sooner for time-sensitive press and case submissions.</p>
                </div>
            </div>

            <div class="ct-form-fields">
                @if($errors->any())
                    <div class="ct-error">
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" id="contact-form" action="/form/contact">
                    @csrf

                    <div class="ct-grid-2 ct-row">
                        <input type="text" name="name" class="ct-input" placeholder="Your name *" required>
                        <input type="email" name="email" class="ct-input" placeholder="Your email *" required>
                    </div>

                    <div class="ct-row">
                        <input type="text" name="subject" class="ct-input" placeholder="Subject (optional)">
                    </div>

                    <div class="ct-row">
                        <textarea name="message" class="ct-textarea" placeholder="Your message *" required></textarea>
                    </div>

                    <div style="margin-top: 12px;">
                        <button type="submit" class="ct-submit g-recaptcha"
                                data-sitekey="6LdREZkqAAAAADv7Ei5dS_SZ1oVaz6A5FE7nacrw"
                                data-callback='onContactSubmit'
                                data-action='submit'>
                            Send message
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Different ways to reach us --}}
    <section class="ct-section">
        <div class="ct-section-eyebrow">Different Ways to Reach Us</div>
        <h2 class="ct-section-heading">Find the right channel</h2>
        <p class="ct-section-sub">
            Prefer to skip the form? Reach out to one of our specialty mailboxes if your message fits a particular category. We aim to respond within two weeks.
        </p>

        <div class="ct-card-grid">
            <div class="ct-card">
                <h3 class="ct-card-title">Submit a case</h3>
                <p class="ct-card-text">Know a political prisoner missing from our database? Send their name, location, charges, and any sources you have.</p>
                <a class="ct-card-link" href="#contact-form">Use the form &rarr;</a>
            </div>
            <div class="ct-card">
                <h3 class="ct-card-title">Profile corrections</h3>
                <p class="ct-card-text">Spotted an error on a prisoner's profile? Tell us the field, the correct information, and a verifiable source.</p>
                <a class="ct-card-link" href="#contact-form">Use the form &rarr;</a>
            </div>
            <div class="ct-card">
                <h3 class="ct-card-title">Press &amp; media</h3>
                <p class="ct-card-text">Reporters and producers — we'll connect you with a researcher or staff member who can comment on the record.</p>
                <a class="ct-card-link" href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a>
            </div>
            <div class="ct-card">
                <h3 class="ct-card-title">Volunteer with us</h3>
                <p class="ct-card-text">Letter-writing, research, event-staffing, fundraising, legal work, web/dev — every role has a coordinator.</p>
                <a class="ct-card-link" href="/volunteer">Apply to volunteer &rarr;</a>
            </div>
            <div class="ct-card">
                <h3 class="ct-card-title">Partnerships</h3>
                <p class="ct-card-text">Bookstores, faith communities, universities, infoshops — we run speaker visits and letter-writing nights.</p>
                <a class="ct-card-link" href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a>
            </div>
            <div class="ct-card">
                <h3 class="ct-card-title">Donations &amp; gifts</h3>
                <p class="ct-card-text">Stock, DAFs, crypto, planned giving, and tax-deductible monthly support — we'll help you choose the right vehicle.</p>
                <a class="ct-card-link" href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a>
            </div>
        </div>
    </section>
</div>

<script>
    function onContactSubmit(token) {
        document.getElementById("contact-form").submit();
    }
</script>
@endsection
