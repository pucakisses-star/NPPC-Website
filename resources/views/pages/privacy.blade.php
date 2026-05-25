@extends('app')

@section('head')
<style>
    .legal-page { max-width: 800px; margin: 0 auto; padding: 48px 24px 80px; }
    .legal-title { font-size: 3rem; font-weight: 900; color: #fff; margin-bottom: 8px; }
    .legal-updated { font-size: 14px; color: rgba(255,255,255,0.4); margin-bottom: 40px; }
    .legal-body h2 { font-size: 1.4rem; font-weight: 800; color: #fff; margin: 2em 0 0.75em; }
    .legal-body p { font-size: 15px; color: rgba(255,255,255,0.7); line-height: 1.8; margin-bottom: 1.25em; }
    .legal-body ul { margin: 0.75em 0 1.25em 1.5em; }
    .legal-body li { font-size: 15px; color: rgba(255,255,255,0.7); line-height: 1.8; margin-bottom: 0.5em; }
    .legal-body a { color: #5660fe; text-decoration: underline; }
    .legal-body strong { color: #fff; }
</style>
@endsection

@section('body')
<div class="legal-page">
    <h1 class="legal-title">Privacy Policy</h1>
    <div class="legal-updated">Last updated: May 25, 2026</div>

    <div class="legal-body">
        <p>The National Political Prisoner Coalition ("NPPC," "we," "us," or "our") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website at nationalpoliticalprisonercoalition.org (the "Site"). Please read this policy carefully. By using the Site, you consent to the practices described in this policy.</p>

        <h2>1. Information We Collect</h2>
        <p><strong>Information you provide directly:</strong></p>
        <ul>
            <li><strong>Contact forms:</strong> Name, email address, and message content when you submit our contact form</li>
            <li><strong>Volunteer applications:</strong> Name, email, phone number, state, fields of interest, skills, educational background, work experience, and any additional message you provide</li>
            <li><strong>Petition signatures:</strong> Name, email, city, state, zip code, phone number, and any custom message</li>
            <li><strong>Email sign-ups:</strong> Email address when you subscribe to stay informed</li>
            <li><strong>Donations:</strong> Payment information is collected and processed by Stripe, our third-party payment processor. We do not store your credit card number, bank account number, or other financial account information on our servers</li>
        </ul>

        <p><strong>Information collected automatically:</strong></p>
        <ul>
            <li>Browser type and version</li>
            <li>Operating system</li>
            <li>Referring website</li>
            <li>Pages visited and time spent on those pages</li>
            <li>Date and time of visit</li>
            <li>IP address</li>
        </ul>

        <h2>2. How We Use Your Information</h2>
        <p>We use the information we collect to:</p>
        <ul>
            <li>Respond to your inquiries and contact requests</li>
            <li>Process volunteer applications and coordinate volunteer activities</li>
            <li>Deliver petitions to designated recipients on your behalf</li>
            <li>Send updates and newsletters to email subscribers</li>
            <li>Process and acknowledge donations</li>
            <li>Improve our website, programs, and services</li>
            <li>Comply with legal obligations</li>
            <li>Protect against fraudulent, unauthorized, or illegal activity</li>
        </ul>

        <h2>3. How We Share Your Information</h2>
        <p>We do not sell, trade, or rent your personal information to third parties. We may share your information in the following limited circumstances:</p>
        <ul>
            <li><strong>Service providers:</strong> We share information with trusted third-party service providers who assist us in operating our website and conducting our activities, including Stripe for payment processing. These providers are contractually obligated to keep your information confidential</li>
            <li><strong>Petition recipients:</strong> When you sign a petition, your name, city, and state may be shared with the designated petition recipients (e.g., elected officials) as part of the petition delivery</li>
            <li><strong>Legal requirements:</strong> We may disclose your information if required to do so by law or in response to valid legal process</li>
            <li><strong>Protection of rights:</strong> We may disclose information to protect the rights, property, or safety of NPPC, our users, or the public</li>
        </ul>

        <h2>4. Data Security</h2>
        <p>We implement reasonable administrative, technical, and physical security measures to protect your personal information. However, no method of transmission over the Internet or electronic storage is 100% secure. While we strive to protect your information, we cannot guarantee its absolute security.</p>

        <h2>5. Data Retention</h2>
        <p>We retain your personal information for as long as necessary to fulfill the purposes for which it was collected, comply with our legal obligations, resolve disputes, and enforce our agreements. Contact form submissions, volunteer applications, and petition signatures are retained indefinitely for record-keeping and advocacy purposes unless you request deletion.</p>

        <h2>6. Your Rights</h2>
        <p>Depending on your location, you may have certain rights regarding your personal information, including:</p>
        <ul>
            <li><strong>Access:</strong> The right to request a copy of the personal information we hold about you</li>
            <li><strong>Correction:</strong> The right to request correction of inaccurate personal information</li>
            <li><strong>Deletion:</strong> The right to request deletion of your personal information, subject to certain legal exceptions</li>
            <li><strong>Opt-out:</strong> The right to opt out of receiving marketing communications from us at any time</li>
        </ul>
        <p>To exercise any of these rights, please contact us using the information provided below.</p>

        <h2>7. Email Communications</h2>
        <p>If you subscribe to our email list, we may send you updates, newsletters, and information about our work. You may unsubscribe at any time by contacting us at <a href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a>. Please note that even if you unsubscribe from marketing communications, we may still send you transactional messages related to your donations or other activities.</p>

        <h2>8. Cookies and Tracking Technologies</h2>
        <p>Our Site uses cookies and similar tracking technologies to enhance your browsing experience. Cookies are small text files stored on your device. We use:</p>
        <ul>
            <li><strong>Essential cookies:</strong> Required for the Site to function properly, including session management and security</li>
            <li><strong>Analytics cookies:</strong> Help us understand how visitors interact with the Site so we can improve it</li>
        </ul>
        <p>You can control cookies through your browser settings. Disabling cookies may affect the functionality of certain features on the Site.</p>

        <h2>9. Third-Party Services</h2>
        <p>Our Site integrates with third-party services including:</p>
        <ul>
            <li><strong>Stripe:</strong> For secure donation processing (<a href="https://stripe.com/privacy" target="_blank">Stripe Privacy Policy</a>)</li>
            <li><strong>Google reCAPTCHA:</strong> For form spam protection (<a href="https://policies.google.com/privacy" target="_blank">Google Privacy Policy</a>)</li>
        </ul>
        <p>These services have their own privacy policies, and we encourage you to review them.</p>

        <h2>10. Children's Privacy</h2>
        <p>Our Site is not directed to children under the age of 13, and we do not knowingly collect personal information from children under 13. If we become aware that we have collected personal information from a child under 13, we will take steps to delete such information promptly.</p>

        <h2>11. Your California Privacy Rights (CCPA / CPRA)</h2>
        <p>If you are a California resident, the California Consumer Privacy Act of 2018 ("CCPA") and the California Privacy Rights Act of 2020 ("CPRA") provide you with additional rights regarding the personal information we collect about you, including the right to know what categories of personal information we collect, the right to delete the personal information we have collected, the right to opt out of any sale or sharing of your personal information for cross-context behavioral advertising, and the right not to be discriminated against for exercising these rights.</p>
        <p><strong>We do not sell or share your personal information for cross-context behavioral advertising</strong>, and we have not done so in the preceding twelve months. To exercise any of your California privacy rights, contact us at the email address below. We will verify your identity before processing your request.</p>

        <h2>12. EU/UK Residents (GDPR / UK GDPR)</h2>
        <p>If you access the Site from the European Economic Area, the United Kingdom, or Switzerland, you have rights under the General Data Protection Regulation (GDPR) and the UK GDPR, including the right to access, rectification, erasure, restriction, data portability, and to object to processing of your personal data. Our lawful bases for processing are: (a) your consent (for newsletter subscriptions and analytics cookies), (b) legitimate interests (for processing contact-form, volunteer, and petition submissions in pursuit of NPPC's nonprofit mission), and (c) legal obligations (for donor records and financial reporting). You may withdraw consent at any time. EU/UK residents have the right to lodge a complaint with their local data-protection authority.</p>

        <h2>13. Do Not Track Signals</h2>
        <p>Some browsers transmit "Do Not Track" (DNT) signals. We currently do not respond to DNT signals because no consistent industry standard exists. We will revise this position if a uniform standard is adopted.</p>

        <h2>14. Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. When we do, we will revise the "Last updated" date at the top of this page. We encourage you to review this policy periodically to stay informed about how we are protecting your information.</p>

        <h2>15. Contact Us</h2>
        <p>If you have questions or concerns about this Privacy Policy or our data practices, please contact us:</p>
        <p>
            <strong>National Political Prisoner Coalition</strong><br>
            Email: <a href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a><br>
            Website: <a href="/">nationalpoliticalprisonercoalition.org</a>
        </p>
    </div>
</div>
@endsection
