@php use App\Models\SiteSetting; @endphp
{{-- Alternative footer — the legacy "STAY INFORMED" footer. Styled by the
     site's legacy (external) CSS, scoped under #app-footer. A page opts into
     this footer with @section('footer-style', 'alt'); otherwise the default
     footer (layout.footer) is used. --}}
<div id="app-footer">
    <footer class="site-footer -frameless">
        <div class="site-footer-inner">
            <div class="site-footer-top">
                <form class="site-footer-signup" action="/sign-up" method="POST">@csrf<label for="site-footer-signup-input-legacy">Stay
                        informed</label>
                    <div class="site-footer-signup-inputs"><input type="email" id="site-footer-signup-input-legacy" class="site-footer-signup-input" name="email" value="" placeholder="Enter your email" required><button type="submit" class="site-footer-signup-submit btn -go"><span class="sr-text">Submit</span></button></div>
                </form>
                <nav class="site-footer-social" aria-label="Social Media Links">
                    <ul>
                        @if($twitterUrl = SiteSetting::get('twitter_url'))
                        <li>
                            <a href="{{ $twitterUrl }}" class="icon-twitter" rel="noopener" target="_blank">
                                <span class="sr-text">Twitter</span>
                            </a>
                        </li>
                        @endif
                        @if($facebookUrl = SiteSetting::get('facebook_url'))
                        <li>
                            <a href="{{ $facebookUrl }}" class="icon-facebook" rel="noopener" target="_blank">
                                <span class="sr-text">Facebook</span>
                            </a>
                        </li>
                        @endif
                        @if($instagramUrl = SiteSetting::get('instagram_url'))
                        <li>
                            <a href="{{ $instagramUrl }}" class="icon-instagram" rel="noopener" target="_blank">
                                <span class="sr-text">Instagram</span>
                            </a>
                        </li>
                        @endif
                        @if($youtubeUrl = SiteSetting::get('youtube_url'))
                        <li>
                            <a href="{{ $youtubeUrl }}" class="icon-youtube" rel="noopener" target="_blank">
                                <span class="sr-text">YouTube</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </nav>
            </div>
            <div class="site-footer-bottom">
                <nav class="site-footer-nav" aria-label="Footer Navigation">
                    <ul>
                        <li><a href="/about" title="About">About</a></li>
                        <li><a href="/privacy" title="Privacy">Privacy</a></li>
                        <li><a href="/terms" title="Terms">Terms</a></li>
                    </ul>
                </nav>
                <div class="site-footer-copyright">
                    <p>© {{ date('Y') }} National Political Prisoner Coalition</p>
                </div>
            </div>
            <svg class="frame-texture" viewBox="0 0 483.45 32.11" aria-hidden="true">
                <use xlink:href="#frame-texture-path"></use>
            </svg>
        </div>
    </footer>
</div>
