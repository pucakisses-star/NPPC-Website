@php use App\Models\SiteSetting; @endphp
<div id="app-footer-v2">
    <footer class="f2">
        <div class="f2-inner">
            <div class="f2-top">
                <div class="f2-brand">
                    <h2 class="f2-title">National Political Prisoner Coalition</h2>
                    <p class="f2-desc">An independent coalition dedicated to documenting, supporting, and advocating for U.S. political prisoners — from the late nineteenth century to the present. Our archive, news, and case profiles serve to inform, organize, and act.</p>
                    <nav class="f2-social" aria-label="Social media">
                        @if ($twitterUrl = SiteSetting::get('twitter_url'))
                            <a href="{{ $twitterUrl }}" class="f2-soc" rel="noopener" target="_blank" aria-label="X / Twitter">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                            </a>
                        @endif
                        @if ($facebookUrl = SiteSetting::get('facebook_url'))
                            <a href="{{ $facebookUrl }}" class="f2-soc" rel="noopener" target="_blank" aria-label="Facebook">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.437H7.078v-3.49h3.047V9.412c0-3.027 1.792-4.7 4.533-4.7 1.312 0 2.686.235 2.686.235v2.97h-1.513c-1.491 0-1.956.93-1.956 1.886v2.27h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073"/></svg>
                            </a>
                        @endif
                        @if ($youtubeUrl = SiteSetting::get('youtube_url'))
                            <a href="{{ $youtubeUrl }}" class="f2-soc" rel="noopener" target="_blank" aria-label="YouTube">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814M9.545 15.568V8.432L15.818 12z"/></svg>
                            </a>
                        @endif
                        @if ($instagramUrl = SiteSetting::get('instagram_url'))
                            <a href="{{ $instagramUrl }}" class="f2-soc" rel="noopener" target="_blank" aria-label="Instagram">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849s-.012 3.584-.07 4.849c-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.849.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919C8.416 2.175 8.796 2.163 12 2.163m0-2.163C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12s.014 3.668.072 4.948c.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24s3.668-.014 4.948-.072c4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948s-.014-3.667-.072-4.947c-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0m0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324M12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8m6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881"/></svg>
                            </a>
                        @endif
                        @if ($tiktokUrl = SiteSetting::get('tiktok_url'))
                            <a href="{{ $tiktokUrl }}" class="f2-soc" rel="noopener" target="_blank" aria-label="TikTok">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5.8 20.1a6.34 6.34 0 0 0 10.86-4.43V9.01a8.16 8.16 0 0 0 4.77 1.52V7.06a4.85 4.85 0 0 1-1.84-.37z"/></svg>
                            </a>
                        @endif
                    </nav>
                </div>

                <div class="f2-signup-wrap">
                    <div class="f2-signup">
                        <div class="f2-signup-eyebrow">Dispatch</div>
                        <div class="f2-signup-headline">Stay informed about the fight for political prisoners.</div>
                        <form class="f2-signup-form" action="/sign-up" method="POST">
                            @csrf
                            <input type="email" name="email" placeholder="Email Address" aria-label="Email address" required>
                            <button type="submit">Submit</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="f2-bottom">
                <div class="f2-bottom-left">
                    <p>Independent, non-partisan, donor-supported. Your contributions fund legal aid, mutual support, and original research.</p>
                </div>
                <div class="f2-bottom-center">©{{ date('Y') }} National Political Prisoner Coalition</div>
                <ul class="f2-bottom-links">
                    <li><a href="/about">About</a></li>
                    <li><a href="/terms">Terms of Use</a></li>
                    <li><a href="/privacy">Privacy Policy</a></li>
                    <li><a href="/get-involved">Get Involved</a></li>
                    <li><a href="/donate">Donate</a></li>
                </ul>
            </div>
        </div>
    </footer>
</div>

<style>
    #app-footer-v2 { background: #0a0a1f; color: #fff; }
    #app-footer-v2 .f2 { font-family: inherit; }
    #app-footer-v2 .f2-inner { max-width: 1500px; margin: 0 auto; padding: 80px 32px 40px; }

    #app-footer-v2 .f2-top { display: grid; grid-template-columns: 1fr 1fr; gap: 64px; margin-bottom: 64px; align-items: start; }

    #app-footer-v2 .f2-title { font-size: 2.5rem; font-weight: 900; color: #fff; margin: 0 0 24px; line-height: 1.05; max-width: 520px; }
    #app-footer-v2 .f2-desc { font-size: 16px; line-height: 1.6; color: rgba(255,255,255,0.8); margin: 0 0 32px; max-width: 540px; }

    #app-footer-v2 .f2-social { display: flex; gap: 18px; flex-wrap: wrap; align-items: center; }
    #app-footer-v2 .f2-soc { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; color: #fff; text-decoration: none; transition: opacity 0.15s; }
    #app-footer-v2 .f2-soc:hover { opacity: 0.7; }
    #app-footer-v2 .f2-soc svg { width: 24px; height: 24px; fill: currentColor; display: block; }

    /* Bordered signup frame */
    #app-footer-v2 .f2-signup-wrap { display: flex; }
    #app-footer-v2 .f2-signup { border: 1.5px solid rgba(120, 140, 255, 0.4); padding: 36px 32px 32px; width: 100%; max-width: 620px; margin-left: auto; }
    #app-footer-v2 .f2-signup-eyebrow { font-size: 1.5rem; font-weight: 900; color: rgba(180, 190, 255, 0.55); line-height: 1; margin-bottom: 10px; }
    #app-footer-v2 .f2-signup-headline { font-size: 1.875rem; font-weight: 900; color: #fff; line-height: 1.15; margin-bottom: 24px; }
    #app-footer-v2 .f2-signup-form { display: flex; }
    #app-footer-v2 .f2-signup-form input { flex: 1; min-width: 0; background: transparent; border: 1px solid rgba(120, 140, 255, 0.45); color: #fff; padding: 16px 18px; font-size: 16px; outline: none; border-radius: 0; }
    #app-footer-v2 .f2-signup-form input::placeholder { color: rgba(255, 255, 255, 0.55); }
    #app-footer-v2 .f2-signup-form input:focus { border-color: #fff; }
    #app-footer-v2 .f2-signup-form button { background: #f25c54; color: #fff; border: none; padding: 0 32px; font-size: 13px; font-weight: 900; letter-spacing: 0.1em; text-transform: uppercase; cursor: pointer; transition: background 0.15s; }
    #app-footer-v2 .f2-signup-form button:hover { background: #d44a42; }

    /* Bottom bar */
    #app-footer-v2 .f2-bottom { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 32px; padding-top: 28px; border-top: 1px solid rgba(255, 255, 255, 0.1); align-items: start; font-size: 13px; color: rgba(255, 255, 255, 0.65); }
    #app-footer-v2 .f2-bottom p { margin: 0; line-height: 1.5; font-size: 13px; letter-spacing: 0; }
    #app-footer-v2 .f2-bottom-center { text-align: center; font-size: 13px; }
    #app-footer-v2 .f2-bottom-links { list-style: none; margin: 0; padding: 0; text-align: right; }
    #app-footer-v2 .f2-bottom-links li { margin-bottom: 6px; }
    #app-footer-v2 .f2-bottom-links a { color: rgba(255, 255, 255, 0.65); text-decoration: none; transition: color 0.15s; }
    #app-footer-v2 .f2-bottom-links a:hover { color: #fff; }

    @media (max-width: 900px) {
        #app-footer-v2 .f2-inner { padding: 48px 24px 28px; }
        #app-footer-v2 .f2-top { grid-template-columns: 1fr; gap: 40px; margin-bottom: 40px; }
        #app-footer-v2 .f2-title { font-size: 2rem; }
        #app-footer-v2 .f2-signup { padding: 28px 22px 24px; max-width: none; }
        #app-footer-v2 .f2-signup-headline { font-size: 1.5rem; }
        #app-footer-v2 .f2-bottom { grid-template-columns: 1fr; gap: 16px; text-align: center; }
        #app-footer-v2 .f2-bottom-center, #app-footer-v2 .f2-bottom-links { text-align: center; }
    }
</style>
