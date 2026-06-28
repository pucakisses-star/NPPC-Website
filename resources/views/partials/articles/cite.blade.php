@php
    $authorName = $article->author?->name ?? 'National Political Prisoner Coalition';
    $pubDate = $article->published_at;
    $title = $article->title;
    $url = url($article->url);
    $siteName = 'National Political Prisoner Coalition';
    $today = now();

    // Parse author name parts
    $nameParts = explode(' ', $authorName);
    $lastName = count($nameParts) > 1 ? end($nameParts) : $authorName;
    $firstInitial = substr($nameParts[0], 0, 1);
    $firstName = $nameParts[0];

    // APA: Author, A. (Year, Month Day). Title. Site Name. Retrieved Date, from URL
    $apa = $lastName . ', ' . $firstInitial . '. (' . ($pubDate ? $pubDate->format('Y, F d') : date('Y')) . '). ';
    $apa .= '<em>' . e($title) . '.</em> ' . $siteName . '. ';
    $apa .= 'Retrieved ' . $today->format('F d, Y') . ', from ' . $url;

    // Chicago: Author. "Title." Site Name. Date. URL.
    $chicago = $authorName . '. "' . e($title) . '." ';
    $chicago .= '<em>' . $siteName . '</em>. ';
    $chicago .= ($pubDate ? $pubDate->format('F j, Y') : '') . '. ';
    $chicago .= $url . '.';

    // MLA: Author. "Title." Site Name, Date, URL. Accessed Date.
    $mla = $authorName . '. "' . e($title) . '." ';
    $mla .= '<em>' . $siteName . '</em>, ';
    $mla .= ($pubDate ? $pubDate->format('j M. Y') : '') . ', ';
    $mla .= $url . '. ';
    $mla .= 'Accessed ' . $today->format('j M. Y') . '.';
@endphp

<div class="cite-section" style="margin-top: 48px; padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.1);">
    {{-- Citation tooltip --}}
    <div id="cite-tooltip" style="display:none; background:#222; color:#fff; padding:16px 20px; border-radius:6px; font-size:13px; line-height:1.6; max-width:500px; margin-bottom:12px; position:relative;">
        <div id="cite-text"></div>
        <button onclick="copyCitation()" style="margin-top:8px; background:rgba(255,255,255,0.1); border:none; color:#fff; padding:4px 12px; font-size:11px; cursor:pointer; border-radius:3px;">Copy to clipboard</button>
    </div>

    {{-- Buttons --}}
    <div style="display:flex; gap:8px;">
        <button onclick="showCitation('apa')" style="background:#87ceeb; color:#000; border:none; padding:10px 20px; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
            APA
        </button>
        <button onclick="showCitation('chicago')" style="background:#87ceeb; color:#000; border:none; padding:10px 20px; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
            Chicago
        </button>
        <button onclick="showCitation('mla')" style="background:#87ceeb; color:#000; border:none; padding:10px 20px; font-size:14px; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:6px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 24 24"><path d="M16 1H4c-1.1 0-2 .9-2 2v14h2V3h12V1zm3 4H8c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h11c1.1 0 2-.9 2-2V7c0-1.1-.9-2-2-2zm0 16H8V7h11v14z"/></svg>
            MLA
        </button>
    </div>
</div>

<script>
var citations = {
    apa: '{!! addslashes($apa) !!}',
    chicago: '{!! addslashes($chicago) !!}',
    mla: '{!! addslashes($mla) !!}'
};
var citePlain = {
    apa: '{!! addslashes(strip_tags($apa)) !!}',
    chicago: '{!! addslashes(strip_tags($chicago)) !!}',
    mla: '{!! addslashes(strip_tags($mla)) !!}'
};
var currentFormat = '';

function showCitation(format) {
    var tooltip = document.getElementById('cite-tooltip');
    var text = document.getElementById('cite-text');
    if (currentFormat === format && tooltip.style.display !== 'none') {
        tooltip.style.display = 'none';
        currentFormat = '';
        return;
    }
    text.innerHTML = citations[format];
    tooltip.style.display = 'block';
    currentFormat = format;
}

function copyCitation() {
    if (currentFormat && citePlain[currentFormat]) {
        navigator.clipboard.writeText(citePlain[currentFormat]).then(function() {
            var btn = event.target;
            btn.textContent = 'Copied!';
            setTimeout(function() { btn.textContent = 'Copy to clipboard'; }, 2000);
        });
    }
}
</script>
