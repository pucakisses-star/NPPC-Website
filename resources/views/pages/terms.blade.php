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
    .legal-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 40px 0; }
</style>
@endsection

@section('body')
<div class="legal-page">
    <h1 class="legal-title">Terms of Use</h1>
    <div class="legal-updated">Last updated: May 25, 2026</div>

    <div class="legal-body">
        <p>Welcome to the website of the National Political Prisoner Coalition ("NPPC," "we," "us," or "our"). By accessing or using our website at nationalpoliticalprisonercoalition.org (the "Site"), you agree to be bound by these Terms of Use. If you do not agree to these terms, please do not use the Site.</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By accessing, browsing, or using this Site, you acknowledge that you have read, understood, and agree to be bound by these Terms of Use and our <a href="/privacy">Privacy Policy</a>, which is incorporated herein by reference. We reserve the right to modify these terms at any time. Your continued use of the Site following any changes constitutes acceptance of those changes.</p>

        <h2>2. About NPPC</h2>
        <p>The National Political Prisoner Coalition is a 501(c)(3) tax-exempt nonprofit organization dedicated to advocating for the rights of political prisoners in the United States. Our Site provides educational resources, news, a searchable database of political prisoners, and tools for civic engagement including petitions, volunteer sign-ups, and donation processing. Our EIN and copy of our IRS determination letter are available on request via <a href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a> or through our most recent <a href="/annual-report">Annual Report</a>.</p>

        <h2>3. Use of the Site</h2>
        <p>You agree to use the Site only for lawful purposes and in a manner that does not infringe the rights of, restrict, or inhibit anyone else's use and enjoyment of the Site. Prohibited conduct includes, but is not limited to:</p>
        <ul>
            <li>Using the Site in any way that violates any applicable federal, state, local, or international law or regulation</li>
            <li>Attempting to gain unauthorized access to any portion of the Site, other accounts, computer systems, or networks connected to the Site</li>
            <li>Using any automated means, including robots, crawlers, or scrapers, to access the Site for any purpose without our express written permission</li>
            <li>Introducing any viruses, trojan horses, worms, or other material that is malicious or technologically harmful</li>
            <li>Impersonating or attempting to impersonate NPPC, an NPPC employee, another user, or any other person or entity</li>
        </ul>

        <h2>4. Intellectual Property</h2>
        <p>The content on this Site — including but not limited to text, graphics, images, photographs, illustrations, data compilations, page layout, underlying code, and software — is the property of NPPC or its content suppliers and is protected by United States and international copyright, trademark, and other intellectual property laws.</p>
        <p>You may view, download, and print content from the Site for your personal, non-commercial use, provided you do not modify the content and you retain all copyright, trademark, and other proprietary notices. Any other use of materials on this Site — including reproduction, modification, distribution, republication, or display — requires the prior written consent of NPPC.</p>

        <h2>5. User-Submitted Content</h2>
        <p>Certain features of the Site, including petition signatures, volunteer applications, contact forms, and email sign-ups, allow you to submit information. By submitting content through the Site, you represent and warrant that:</p>
        <ul>
            <li>The information you provide is accurate, current, and complete</li>
            <li>You have the right to submit such information</li>
            <li>Your submission does not violate any law or the rights of any third party</li>
        </ul>
        <p>We reserve the right to remove or decline to publish any user-submitted content at our sole discretion.</p>

        <h2>6. Donations</h2>
        <p>Donations made through this Site are processed by Stripe, a third-party payment processor. By making a donation, you agree to Stripe's terms of service in addition to these Terms of Use. All donations are voluntary. NPPC does not sell goods or services in exchange for donations unless explicitly stated. Recurring donations may be cancelled at any time by contacting us at <a href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a>.</p>
        <p><strong>Tax-deductibility.</strong> NPPC is a 501(c)(3) tax-exempt nonprofit organization. Donations are tax-deductible in the United States to the fullest extent permitted by law. You will receive an email donation receipt at the time of your gift; for cumulative annual donations of $250 or more we send a year-end acknowledgment letter for your tax records. No goods or services are provided in exchange for donations unless explicitly stated at the time of solicitation.</p>
        <p><strong>Refunds.</strong> Donations are non-refundable except in cases of processing error, duplicate transactions, or unauthorized use. Refund requests should be sent to <a href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a> within 30 days of the transaction.</p>

        <h2>7. Prisoner Database</h2>
        <p>The prisoner database on this Site is provided for educational and advocacy purposes. While we strive for accuracy, we do not guarantee that all information is complete, current, or error-free. The inclusion of any individual in the database does not constitute a legal determination of their status. If you believe any information is inaccurate, please contact us so we can review and correct it.</p>

        <h2>8. Third-Party Links</h2>
        <p>The Site may contain links to third-party websites, services, or resources. These links are provided for your convenience only. We do not endorse, control, or assume responsibility for the content, privacy policies, or practices of any third-party websites. Your use of third-party websites is at your own risk and subject to the terms and conditions of those websites.</p>

        <h2>9. Disclaimer of Warranties</h2>
        <p>THE SITE AND ALL CONTENT, MATERIALS, AND SERVICES PROVIDED ON OR THROUGH THE SITE ARE PROVIDED "AS IS" AND "AS AVAILABLE" WITHOUT ANY WARRANTIES OF ANY KIND, WHETHER EXPRESS, IMPLIED, OR STATUTORY. TO THE FULLEST EXTENT PERMITTED BY LAW, NPPC DISCLAIMS ALL WARRANTIES, INCLUDING BUT NOT LIMITED TO IMPLIED WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.</p>
        <p>We do not warrant that the Site will be uninterrupted, secure, or error-free, that defects will be corrected, or that the Site or the servers that make it available are free of viruses or other harmful components.</p>

        <h2>10. Limitation of Liability</h2>
        <p>TO THE FULLEST EXTENT PERMITTED BY APPLICABLE LAW, IN NO EVENT SHALL NPPC, ITS OFFICERS, DIRECTORS, EMPLOYEES, VOLUNTEERS, OR AGENTS BE LIABLE FOR ANY INDIRECT, INCIDENTAL, SPECIAL, CONSEQUENTIAL, OR PUNITIVE DAMAGES, INCLUDING WITHOUT LIMITATION LOSS OF PROFITS, DATA, USE, OR GOODWILL, ARISING OUT OF OR IN CONNECTION WITH YOUR USE OF OR INABILITY TO USE THE SITE, WHETHER BASED ON WARRANTY, CONTRACT, TORT (INCLUDING NEGLIGENCE), OR ANY OTHER LEGAL THEORY.</p>

        <h2>11. Indemnification</h2>
        <p>You agree to indemnify, defend, and hold harmless NPPC and its officers, directors, employees, volunteers, and agents from and against any and all claims, damages, obligations, losses, liabilities, costs, and expenses arising from your use of the Site, your violation of these Terms of Use, or your violation of any rights of a third party.</p>

        <h2>12. Governing Law</h2>
        <p>These Terms of Use shall be governed by and construed in accordance with the laws of the United States, without regard to its conflict of law principles. Any legal action or proceeding arising under these terms shall be brought exclusively in the federal or state courts located in the United States, and you consent to personal jurisdiction and venue in such courts.</p>

        <h2>13. Severability</h2>
        <p>If any provision of these Terms of Use is held to be invalid or unenforceable, such provision shall be struck and the remaining provisions shall be enforced to the fullest extent under law.</p>

        <h2>14. Contact Information</h2>
        <p>If you have any questions about these Terms of Use, please contact us:</p>
        <p>
            <strong>National Political Prisoner Coalition</strong><br>
            Email: <a href="mailto:info@nationalpoliticalprisonercoalition.org">info@nationalpoliticalprisonercoalition.org</a><br>
            Website: <a href="/">nationalpoliticalprisonercoalition.org</a>
        </p>
    </div>
</div>
@endsection
