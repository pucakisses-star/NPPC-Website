@php $quotes = App\Models\Quote::whereNotNull('author_image')->where('author_image', '!=', '')->inRandomOrder()->get(); @endphp
@if($quotes->isNotEmpty())
<section style="position:relative; overflow:hidden; padding:0 0 100px;">
    <div style="max-width:1100px; margin:0 auto; padding:0 24px; position:relative;">

        @foreach($quotes as $i => $quote)
            <div class="quote-slide" style="position:{{ $i === 0 ? 'relative' : 'absolute' }}; top:0; left:0; right:0; opacity:{{ $i === 0 ? '1' : '0' }}; transition:opacity 0.8s ease; padding:0 24px;">
                <div style="display:flex; align-items:flex-end; gap:24px;">

                    {{-- Author image — fixed height so portraits visually match; width is allowed to vary so portraits aren't cropped --}}
                    @if($quote->author_image)
                        <div style="flex:0 0 auto; height:400px;">
                            <img src="/storage/{{ $quote->author_image }}" alt="{{ $quote->author_name }}" style="height:100%; width:auto; max-width:none; display:block; filter:grayscale(100%);">
                        </div>
                    @endif

                    {{-- Quote content --}}
                    <div style="flex:1; min-width:0; padding-bottom:16px;">
                        <div style="display:flex; align-items:flex-start; gap:12px;">
                            {{-- Quote marks --}}
                            <svg width="56" height="56" viewBox="0 0 24 24" fill="#5660fe" style="flex-shrink:0; margin-top:2px;">
                                <path d="M6 17h3l2-4V7H5v6h3zm8 0h3l2-4V7h-6v6h3z"/>
                            </svg>
                            <div>
                                <p style="font-size:1.85rem; font-weight:800; color:#fff; line-height:1.35; margin:0 0 24px;">{{ $quote->text }}</p>
                                <cite style="font-size:15px; color:rgba(255,255,255,0.45); font-style:normal; font-weight:500;">- {{ $quote->author_name }}</cite>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var slides = document.querySelectorAll('.quote-slide');
    if (slides.length <= 1) return;

    var current = 0;

    setInterval(function () {
        var next = (current + 1) % slides.length;
        slides[current].style.position = 'absolute';
        slides[current].style.opacity = '0';
        slides[next].style.position = 'relative';
        slides[next].style.opacity = '1';
        current = next;
    }, 8000);
});
</script>
@endif
