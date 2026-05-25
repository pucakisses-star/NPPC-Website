@php use App\Models\Faq; @endphp
@php $faqs = Faq::getFaqsByType($type); @endphp

@if($faqs->isNotEmpty())
<section style="background:#000; padding:48px 0; margin-top:40px;">
    <div style="max-width:900px; margin:0 auto; padding:0 24px;">
        <h2 style="font-size:2rem; font-weight:900; color:#fff; margin-bottom:32px;">Frequently Asked Questions</h2>

        @foreach($faqs as $faq)
            <div class="faq-item" style="border-bottom:1px solid rgba(255,255,255,0.12);">
                <button class="faq-toggle" onclick="toggleFaq(this)" aria-expanded="false" aria-controls="faq-{{ $loop->index }}" style="width:100%; display:flex; justify-content:space-between; align-items:center; padding:20px 0; background:none; border:none; cursor:pointer; text-align:left;">
                    <span style="font-size:17px; font-weight:500; color:rgba(255,255,255,0.85);">{{ $faq->question }}</span>
                    <svg class="faq-chevron" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" stroke="rgba(255,255,255,0.5)" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0; margin-left:16px; transition:transform 0.2s;" aria-hidden="true">
                        <path d="M6 9l6 6 6-6"/>
                    </svg>
                </button>
                <div class="faq-answer" id="faq-{{ $loop->index }}" role="region" aria-live="polite" style="max-height:0; overflow:hidden; transition:max-height 0.3s ease;">
                    <div style="padding:0 0 20px; font-size:15px; color:rgba(255,255,255,0.55); line-height:1.7;">
                        {!! nl2br(e($faq->answer)) !!}
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<script>
function toggleFaq(btn) {
    var answer = btn.nextElementSibling;
    var chevron = btn.querySelector('.faq-chevron');
    var isOpen = answer.style.maxHeight && answer.style.maxHeight !== '0px';

    // Close all
    document.querySelectorAll('.faq-answer').forEach(function(a) { a.style.maxHeight = '0px'; });
    document.querySelectorAll('.faq-chevron').forEach(function(c) { c.style.transform = 'rotate(0)'; });
    document.querySelectorAll('.faq-toggle span').forEach(function(s) { s.style.color = 'rgba(255,255,255,0.85)'; });

    // Reset all aria-expanded
    document.querySelectorAll('.faq-toggle').forEach(function(b) { b.setAttribute('aria-expanded', 'false'); });

    if (!isOpen) {
        answer.style.maxHeight = answer.scrollHeight + 'px';
        chevron.style.transform = 'rotate(180deg)';
        btn.querySelector('span').style.color = '#fff';
        btn.setAttribute('aria-expanded', 'true');
        btn.parentElement.style.borderBottomColor = '#5660fe';
    } else {
        btn.parentElement.style.borderBottomColor = 'rgba(255,255,255,0.12)';
    }
}
</script>
@endif
