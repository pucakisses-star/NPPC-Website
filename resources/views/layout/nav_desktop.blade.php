@php use App\Views\ViewSupport; @endphp
@php
    $menuItems = ViewSupport::getMenuItems();
@endphp

<div class="hidden md:block" id="nav-spacing"></div>

{{-- Search Overlay --}}
<div id="search-overlay" style="display:none; position:fixed; top:0; left:0; right:0; z-index:100001; background:#fff; padding:24px 40px; box-shadow:0 4px 30px rgba(0,0,0,0.3);">
    <div style="max-width:900px; margin:0 auto; display:flex; align-items:center; gap:16px;">
        <form action="/search" method="GET" role="search" style="flex:1; display:flex; align-items:center; border:2px solid #ddd; border-radius:40px; padding:8px 20px;">
            <label for="site-search" class="sr-only">Search this site</label>
            <input type="text" name="q" id="site-search" placeholder="Search this site" style="flex:1; border:none; outline:none; font-size:18px; color:#333; background:transparent;">
            <button type="submit" aria-label="Submit search" style="background:#5660fe; border:none; width:40px; height:40px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/></svg>
            </button>
        </form>
        <button onclick="document.getElementById('search-overlay').style.display='none'" aria-label="Close search" style="background:none; border:none; cursor:pointer; width:40px; height:40px; display:flex; align-items:center; justify-content:center;">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" fill="#333" viewBox="0 0 24 24"><path d="M18.3 5.71a1 1 0 0 0-1.42 0L12 10.59 7.12 5.71a1 1 0 0 0-1.42 1.42L10.59 12l-4.89 4.88a1 1 0 1 0 1.42 1.42L12 13.41l4.88 4.89a1 1 0 0 0 1.42-1.42L13.41 12l4.89-4.88a1 1 0 0 0 0-1.41z"/></svg>
        </button>
    </div>
</div>

{{-- Hamburger Slide-out Panel --}}
<div id="hamburger-panel" style="display:none; position:fixed; top:0; right:0; bottom:0; width:380px; max-width:90vw; background:#fff; z-index:100001; box-shadow:-4px 0 30px rgba(0,0,0,0.3); overflow-y:auto;">
    <div style="display:flex; justify-content:flex-end; padding:20px 24px;">
        <button onclick="document.getElementById('hamburger-panel').style.display='none'; document.getElementById('hamburger-backdrop').style.display='none';" aria-label="Close menu" style="background:none; border:none; cursor:pointer;">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#333" viewBox="0 0 24 24"><path d="M18.3 5.71a1 1 0 0 0-1.42 0L12 10.59 7.12 5.71a1 1 0 0 0-1.42 1.42L10.59 12l-4.89 4.88a1 1 0 1 0 1.42 1.42L12 13.41l4.88 4.89a1 1 0 0 0 1.42-1.42L13.41 12l4.89-4.88a1 1 0 0 0 0-1.41z"/></svg>
        </button>
    </div>
    <nav style="padding:0 40px 40px;">
        @foreach($menuItems as $item)
            <a href="{{$item->href}}" style="display:block; color:#222; font-size:20px; font-weight:700; padding:14px 0; border-bottom:1px solid #eee; text-decoration:none;">{{$item->title}}</a>
        @endforeach
        <div style="border-top:2px solid #ddd; margin-top:16px; padding-top:16px;">
            <a href="/donate" style="display:block; color:#222; font-size:20px; font-weight:700; padding:14px 0; text-decoration:none;">Donate</a>
            <a href="/contact" style="display:block; color:#222; font-size:20px; font-weight:700; padding:14px 0; text-decoration:none;">Contact</a>
        </div>
    </nav>
</div>
<div id="hamburger-backdrop" onclick="document.getElementById('hamburger-panel').style.display='none'; this.style.display='none';" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:100000;"></div>

{{-- Desktop Navigation --}}
<section class="hidden md:block" id="desktop-nav">
    {{-- Top utility bar --}}
    <div style="background:#000; display:flex; justify-content:space-between; align-items:center; padding:0 24px; height:36px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">
        <div style="display:flex; gap:20px;">
            <a href="/" style="color:#fff; text-decoration:none;">NPPC</a>
            <a href="/donate" style="color:#fff; text-decoration:none;">Donate</a>
        </div>
        <div>
            <a href="/contact" style="background:#5660fe; color:#fff; text-decoration:none; padding:8px 24px; display:inline-block; height:36px; line-height:20px;">Contact Us</a>
        </div>
    </div>

    {{-- Main navigation bar --}}
    <header id="main-nav" style="background:rgba(0,0,0,0.85); backdrop-filter:blur(10px); position:relative; z-index:10000;">
        <div style="max-width:1280px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; padding:0 24px; height:72px;">
            {{-- Logo --}}
            <a href="/" style="flex-shrink:0;">
                <img src="/logo.svg" alt="National Political Prisoner Coalition" style="height:50px;">
            </a>

            {{-- Nav links --}}
            <nav style="display:flex; align-items:center; gap:0;" id="mega-nav">
                @foreach($menuItems as $item)
                    <div class="mega-nav-item" data-has-children="{{ $item->children ? '1' : '0' }}" style="position:relative;">
                        <a href="{{$item->href}}"
                           class="mega-nav-link"
                           @if($item->children) data-mega-dropdown="{{$item->slug}}" @endif
                           style="display:flex; align-items:center; padding:24px 20px; color:#fff; text-decoration:none; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; position:relative; transition:color 0.2s;">
                            {{$item->title}}
                        </a>
                        {{-- Hover underline for items WITHOUT children --}}
                        @if(!$item->children)
                            <div class="mega-nav-underline" style="position:absolute; bottom:0; left:20px; right:20px; height:3px; background:#5660fe; transform:scaleX(0); transition:transform 0.2s;"></div>
                        @else
                            {{-- Active indicator for items WITH children --}}
                            <div class="mega-nav-indicator" style="position:absolute; bottom:0; left:50%; transform:translateX(-50%) scaleX(0); width:40px; height:3px; background:#5660fe; transition:transform 0.2s;"></div>
                        @endif
                    </div>
                @endforeach
            </nav>

            {{-- Donate + Search + Hamburger --}}
            <div style="display:flex; align-items:center; gap:12px; flex-shrink:0;">
                <a href="/donate" style="background:#5660fe; color:#fff; text-decoration:none; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:12px 28px; border-radius:4px; display:flex; align-items:center; gap:8px; transition:background 0.2s;" onmouseenter="this.style.background='#4850e6'" onmouseleave="this.style.background='#5660fe'">
                    DONATE <span style="font-size:18px;">&rarr;</span>
                </a>
                <button onclick="document.getElementById('search-overlay').style.display='block'; document.getElementById('search-overlay').querySelector('input').focus();" aria-label="Search" style="background:#222; border:none; width:42px; height:42px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="16.5" y1="16.5" x2="21" y2="21"/></svg>
                </button>
                <button onclick="document.getElementById('hamburger-panel').style.display='block'; document.getElementById('hamburger-backdrop').style.display='block';" aria-label="Open menu" style="background:#222; border:none; width:42px; height:42px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#fff" viewBox="0 0 24 24"><path d="M3 6h18v2H3V6zm0 5h18v2H3v-2zm0 5h18v2H3v-2z"/></svg>
                </button>
            </div>
        </div>

        {{-- Mega dropdown panels --}}
        @foreach($menuItems as $item)
            @if($item->children)
                <div class="mega-dropdown-panel" data-mega-panel="{{$item->slug}}" style="display:none; position:absolute; left:50%; transform:translateX(-50%); top:100%; z-index:9999; width:600px; max-width:90vw;">
                    <div style="background:#fff; border-top:3px solid #5660fe; box-shadow:0 8px 30px rgba(0,0,0,0.15); border-radius:0 0 8px 8px;">
                        <div style="padding:24px 32px; display:grid; grid-template-columns:repeat(2,1fr); gap:0;">
                            @foreach($item->children as $child)
                                <a href="{{$child->href}}" style="display:block; padding:14px 16px; color:#222; text-decoration:none; font-size:16px; font-weight:600; border-bottom:1px solid #eee; transition:background 0.15s;">
                                    {{$child->title}}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </header>
</section>

<style>
    .mega-nav-item:hover .mega-nav-underline { transform: scaleX(1) !important; }
    .mega-nav-item:hover .mega-nav-link { color: #ccc; }
    .mega-nav-item.mega-active .mega-nav-indicator { transform: translateX(-50%) scaleX(1) !important; }
    .mega-dropdown-panel a:hover { background: rgba(0,0,0,0.05); }
    #main-nav { position: sticky; top: 0; }
    #nav-spacing { height: 108px; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var navItems = document.querySelectorAll('.mega-nav-item[data-has-children="1"]');
    var panels = document.querySelectorAll('.mega-dropdown-panel');
    var closeTimer = null;

    function closeAll() {
        panels.forEach(function(p) { p.style.display = 'none'; });
        navItems.forEach(function(n) { n.classList.remove('mega-active'); });
    }

    navItems.forEach(function(item) {
        var link = item.querySelector('[data-mega-dropdown]');
        if (!link) return;
        var slug = link.getAttribute('data-mega-dropdown');
        var panel = document.querySelector('[data-mega-panel="' + slug + '"]');
        if (!panel) return;

        function openPanel() {
            if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
            closeAll();
            panel.style.display = 'block';
            item.classList.add('mega-active');
            link.setAttribute('aria-expanded', 'true');
        }

        function scheduleClose() {
            closeTimer = setTimeout(function() {
                closeAll();
                link.setAttribute('aria-expanded', 'false');
            }, 200);
        }

        item.addEventListener('mouseenter', openPanel);
        item.addEventListener('mouseleave', scheduleClose);
        panel.addEventListener('mouseenter', function() {
            if (closeTimer) { clearTimeout(closeTimer); closeTimer = null; }
        });
        panel.addEventListener('mouseleave', scheduleClose);

        // Keyboard: open on focus, close on blur away
        link.addEventListener('focus', openPanel);
        link.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openPanel();
                var firstLink = panel.querySelector('a');
                if (firstLink) firstLink.focus();
            }
            if (e.key === 'Escape') {
                closeAll();
                link.setAttribute('aria-expanded', 'false');
                link.focus();
            }
        });
        panel.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAll();
                link.setAttribute('aria-expanded', 'false');
                link.focus();
            }
        });
        panel.addEventListener('focusout', function(e) {
            if (!panel.contains(e.relatedTarget) && !item.contains(e.relatedTarget)) {
                scheduleClose();
            }
        });

        // Set ARIA attributes
        link.setAttribute('aria-haspopup', 'true');
        link.setAttribute('aria-expanded', 'false');

        // Prevent click on parent link if it has children
        link.addEventListener('click', function(e) {
            e.preventDefault();
        });
    });
});
</script>
