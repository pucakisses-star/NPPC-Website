{{-- Scribble text decoration span. Wrap a single word in a heading:
     @include('partials.scribble', ['word' => 'Free'])
     Requires @include('partials.scribble-assets') once in the page head. --}}
<span class="scr">{{ $word }}<svg viewBox="0 0 300 24" preserveAspectRatio="none" aria-hidden="true"><path style="--len:340" d="M6,15 C60,7 120,19 180,10 C232,4 268,16 294,9"/><path class="p2" style="--len:330" d="M10,19 C70,13 150,21 210,14 C250,10 275,18 290,15"/></svg></span>@php /* no trailing whitespace */ @endphp
