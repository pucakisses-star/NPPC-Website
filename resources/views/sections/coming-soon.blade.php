{{-- Reusable "coming soon / under construction" empty-state placeholder.
     Optional include data: $message (the sub-line). Inline-styled on purpose so
     it renders consistently regardless of the (purged) Tailwind build or the
     host page's own CSS. --}}
<div style="max-width:720px; margin:0 auto; padding:96px 24px; text-align:center;">
    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="rgba(255,255,255,0.35)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" style="display:block; margin:0 auto 24px;">
        <circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>
    </svg>
    <p style="font-size:12px; font-weight:700; letter-spacing:0.18em; text-transform:uppercase; color:rgba(255,255,255,0.4); margin:0 0 12px;">Under construction</p>
    <h2 style="font-size:clamp(1.8rem, 4vw, 2.4rem); font-weight:700; color:#fff; margin:0 0 16px; line-height:1.2;">Coming soon</h2>
    <p style="font-size:1.05rem; line-height:1.6; color:rgba(255,255,255,0.55); max-width:34rem; margin:0 auto;">{{ $message ?? 'This page is being updated. Please check back soon.' }}</p>
</div>
